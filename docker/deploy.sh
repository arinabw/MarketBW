#!/bin/bash

# Скрипт установки и обновления сайта MarketBW
# Использование: ./deploy.sh [install|update|restart|stop|logs]

set -e

# Цвета для вывода
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Функция для вывода сообщений
log_info() {
    echo -e "${GREEN}[INFO]${NC} $1"
}

log_warn() {
    echo -e "${YELLOW}[WARN]${NC} $1"
}

log_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Определение команды Docker Compose (v2: "docker compose", v1: "docker-compose")
set_compose_cmd() {
    if docker compose version &> /dev/null; then
        COMPOSE_CMD="docker compose"
    elif command -v docker-compose &> /dev/null; then
        COMPOSE_CMD="docker-compose"
    else
        log_error "Docker Compose не найден. Установите Docker Compose (или плагин 'docker compose') перед запуском скрипта."
        exit 1
    fi
}

# Проверка наличия Docker
check_docker() {
    if ! command -v docker &> /dev/null; then
        log_error "Docker не установлен. Установите Docker перед запуском скрипта."
        exit 1
    fi

    set_compose_cmd
    log_info "Docker и Docker Compose установлены ($COMPOSE_CMD)"
}

# Установка сайта
install() {
    log_info "Начинаю установку сайта MarketBW..."

    # Проверка наличия необходимых файлов
    if [ ! -f "../package.json" ]; then
        log_error "Файл package.json не найден. Убедитесь, что вы находитесь в папке docker и проект настроен правильно."
        exit 1
    fi

    # Сборка Docker образа (включая установку зависимостей и сборку)
    log_info "Сборка Docker образа..."
    $COMPOSE_CMD -p marketbw-stack build

    # Запуск контейнера
    log_info "Запуск контейнера..."
    $COMPOSE_CMD -p marketbw-stack up -d

    log_info "✅ Сайт успешно установлен и запущен!"
    log_info "Сайт доступен по адресу: http://localhost:3000"
}

# Обновление сайта
update() {
    log_info "Начинаю обновление сайта MarketBW..."

    # Остановка контейнера
    log_info "Остановка контейнера..."
    $COMPOSE_CMD -p marketbw-stack down

    # Получение последних изменений (если используется git)
    if [ -d "../.git" ]; then
        log_info "Получение последних изменений из git..."
        cd ..
        git pull origin main || git pull origin master
        cd docker
    fi

    # Пересборка Docker образа (включая установку зависимостей и сборку)
    log_info "Пересборка Docker образа..."
    $COMPOSE_CMD -p marketbw-stack build

    # Запуск контейнера
    log_info "Запуск контейнера..."
    $COMPOSE_CMD -p marketbw-stack up -d

    log_info "✅ Сайт успешно обновлен и запущен!"
}

# Перезапуск сайта
restart() {
    log_info "Перезапуск сайта..."
    $COMPOSE_CMD -p marketbw-stack restart
    log_info "✅ Сайт успешно перезапущен!"
}

# Остановка сайта
stop() {
    log_info "Остановка сайта..."
    $COMPOSE_CMD -p marketbw-stack down
    log_info "✅ Сайт успешно остановлен!"
}

# Просмотр логов
logs() {
    log_info "Просмотр логов (Ctrl+C для выхода)..."
    $COMPOSE_CMD -p marketbw-stack logs -f
}

# Проверка статуса
status() {
    log_info "Статус контейнеров:"
    $COMPOSE_CMD -p marketbw-stack ps
}

# Очистка (удаление контейнеров и образов)
clean() {
    log_warn "Это действие удалит все контейнеры и образы. Продолжить? (y/n)"
    read -r response
    if [[ "$response" =~ ^([yY][eE][sS]|[yY])$ ]]; then
        log_info "Остановка и удаление контейнеров..."
        $COMPOSE_CMD -p marketbw-stack down -v --rmi all
        log_info "✅ Очистка завершена!"
    else
        log_info "Очистка отменена."
    fi
}

# Главное меню
main() {
    check_docker

    case "${1:-}" in
        install)
            install
            ;;
        update)
            update
            ;;
        restart)
            restart
            ;;
        stop)
            stop
            ;;
        logs)
            logs
            ;;
        status)
            status
            ;;
        clean)
            clean
            ;;
        *)
            echo "Использование: $0 {install|update|restart|stop|logs|status|clean}"
            echo ""
            echo "Команды:"
            echo "  install  - Первичная установка сайта"
            echo "  update   - Обновление сайта"
            echo "  restart  - Перезапуск сайта"
            echo "  stop     - Остановка сайта"
            echo "  logs     - Просмотр логов"
            echo "  status   - Проверка статуса"
            echo "  clean    - Полная очистка (удаление контейнеров и образов)"
            exit 1
            ;;
    esac
}

main "$@"