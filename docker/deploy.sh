#!/bin/bash
#
# Установка и обновление MarketBW на сервере с Traefik (idpro1313/webserver).
# Запускать на Linux: из каталога docker/ или из любого места: /opt/MarketBW/docker/deploy.sh install
#
# Использование:
#   ./deploy.sh install              # первый запуск (проверка сети web)
#   ./deploy.sh update               # git pull + build + up
#   ./deploy.sh update --no-cache    # то же, сборка без кэша
#   ./deploy.sh rebuild              # build --no-cache + up (без git)
#   ./deploy.sh restart|stop|logs|status|clean

set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
REPO_ROOT="$(cd "$SCRIPT_DIR/.." && pwd)"
cd "$SCRIPT_DIR"

export DOCKER_BUILDKIT=1
export COMPOSE_DOCKER_CLI_BUILD=1

# Не используем source .env — в TRAEFIK_RULE бывают обратные кавычки Host(`domain`),
# bash интерпретирует их как command substitution. Docker Compose читает .env сам.
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

log_info() { echo -e "${GREEN}[INFO]${NC} $1"; }
log_warn() { echo -e "${YELLOW}[WARN]${NC} $1"; }
log_error() { echo -e "${RED}[ERROR]${NC} $1"; }

set_compose_cmd() {
  if docker compose version &> /dev/null; then
    COMPOSE_CMD=(docker compose)
  elif command -v docker-compose &> /dev/null; then
    COMPOSE_CMD=(docker-compose)
  else
    log_error "Docker Compose не найден. Установите плагин «docker compose»."
    exit 1
  fi
}

# Безопасное чтение KEY=value (без source .env — в других строках могут быть Host(`...`))
compose_env_get() {
  local key="$1"
  local def="${2:-}"
  [ ! -f .env ] && echo "$def" && return
  local line=""
  if grep -q -E "^[[:space:]]*${key}=" .env 2>/dev/null; then
    line="$(grep -E "^[[:space:]]*${key}=" .env | tail -n1)"
  fi
  [ -z "$line" ] && echo "$def" && return
  local val="${line#*=}"
  val="${val%%#*}"
  val="$(printf '%s' "$val" | sed 's/^[[:space:]]*//;s/[[:space:]]*$//')"
  val="${val#\"}"
  val="${val%\"}"
  val="${val#\'}"
  val="${val%\'}"
  [ -z "$val" ] && echo "$def" || printf '%s' "$val"
}

compose() {
  local proj
  proj="$(compose_env_get COMPOSE_PROJECT_NAME marketbw-stack)"
  "${COMPOSE_CMD[@]}" -p "$proj" -f "$SCRIPT_DIR/docker-compose.yml" "$@"
}

check_docker() {
  if ! command -v docker &> /dev/null; then
    log_error "Docker не установлен."
    exit 1
  fi
  set_compose_cmd
  log_info "Compose: ${COMPOSE_CMD[*]}"
}

# Сеть, к которой подключён Traefik (как в https://github.com/idpro1313/webserver )
check_traefik_network() {
  local net
  net="$(compose_env_get TRAEFIK_NETWORK web)"
  if ! docker network inspect "$net" &>/dev/null; then
    log_error "Docker-сеть «$net» не найдена. Traefik из webserver обычно использует сеть web."
    log_error "Создайте: docker network create web"
    log_error "Или задайте TRAEFIK_NETWORK в docker/.env под вашу сеть."
    exit 1
  fi
  log_info "Сеть «$net» найдена."
}

require_env_file() {
  if [ ! -f ".env" ]; then
    log_error "Нет файла docker/.env"
    log_info "Скопируйте: cp $SCRIPT_DIR/env.example $SCRIPT_DIR/.env и задайте TRAEFIK_RULE, SITE_CONTAINER_NAME, TRAEFIK_ROUTER."
    exit 1
  fi
}

require_repo_root() {
  if [ ! -f "$REPO_ROOT/composer.json" ]; then
    log_error "Не найден $REPO_ROOT/composer.json — запускайте скрипт из репозитория MarketBW (каталог docker/ внутри проекта)."
    exit 1
  fi
}

install() {
  log_info "Установка MarketBW (Traefik / webserver)..."
  require_repo_root
  require_env_file
  check_traefik_network

  log_info "Сборка образа..."
  compose build

  log_info "Запуск контейнера..."
  compose up -d

  log_info "Готово. DNS (A) на IP сервера; HTTPS через Traefik (resolver le, entrypoint websecure по умолчанию)."
}

update() {
  local no_cache=""
  if [ "${2:-}" = "--no-cache" ] || [ "${1:-}" = "--no-cache" ]; then
    no_cache="--no-cache"
  fi

  log_info "Обновление MarketBW..."
  require_env_file

  log_info "Остановка контейнера..."
  compose down

  if [ -d "$REPO_ROOT/.git" ]; then
    # С auto-update.sh и cron: BRANCH=main|master|… (или GIT_BRANCH)
    local branch="${GIT_BRANCH:-${BRANCH:-main}}"
    log_info "git pull origin $branch..."
    if git -C "$REPO_ROOT" pull origin "$branch"; then
      :
    elif [ "$branch" != main ] && git -C "$REPO_ROOT" pull origin main; then
      log_warn "Ветка $branch недоступна, подтянут main."
    elif git -C "$REPO_ROOT" pull origin master; then
      log_warn "Подтянут master."
    else
      log_warn "git pull не удался (сеть или конфликт)."
    fi
  else
    log_warn "Нет .git в $REPO_ROOT — пропускаю git pull."
  fi

  log_info "Сборка образа${no_cache:+ (без кэша)}..."
  if [ -n "$no_cache" ]; then
    compose build --no-cache
  else
    compose build
  fi

  log_info "Запуск..."
  compose up -d

  log_info "Обновление завершено."
}

rebuild() {
  log_info "Пересборка без git pull..."
  require_env_file
  compose build --no-cache
  compose up -d
  log_info "Готово."
}

restart() {
  require_env_file
  log_info "Перезапуск..."
  compose restart
  log_info "Готово."
}

stop() {
  require_env_file
  compose down
  log_info "Остановлено."
}

logs() {
  require_env_file
  compose logs -f
}

status() {
  require_env_file
  compose ps
}

clean() {
  require_env_file
  local proj
  proj="$(compose_env_get COMPOSE_PROJECT_NAME marketbw-stack)"
  log_warn "Удалить контейнеры и образы проекта $proj? Сеть Traefik не трогаем. (y/n)"
  read -r response
  if [[ "$response" =~ ^([yY][eE][sS]|[yY])$ ]]; then
    compose down -v --rmi all
    log_info "Очистка выполнена."
  else
    log_info "Отменено."
  fi
}

usage() {
  echo "Использование: $0 {install|update|update --no-cache|rebuild|restart|stop|logs|status|clean}"
  echo ""
  echo "  install          — первая установка (проверка сети Traefik)"
  echo "  update           — git pull + build + up"
  echo "  update --no-cache — как update, с docker compose build --no-cache"
  echo "  rebuild          — build --no-cache + up без git"
  echo "  restart|stop|logs|status|clean"
  echo ""
  echo "Корень репозитория: $REPO_ROOT"
  echo "Документация хостинга: https://github.com/idpro1313/webserver"
}

main() {
  check_docker
  case "${1:-}" in
    install) install ;;
    update) update "$@" ;;
    rebuild) rebuild ;;
    restart) restart ;;
    stop) stop ;;
    logs) logs ;;
    status) status ;;
    clean) clean ;;
    *) usage; exit 1 ;;
  esac
}

main "$@"
