#!/bin/bash
# Автообновление MarketBW по cron при появлении новых коммитов на origin.
#
# Лог пишется ВНУТРИ скрипта (mkdir каталога + exec >> файл). Так cron не молчит,
# если каталога для редиректа не было или редирект в crontab не сработал.
# Путь: MARKETBW_AUTO_UPDATE_LOG или по умолчанию /opt/webserver/log/marketbw-auto-update.log
# При невозможности создать каталог — /tmp/marketbw-auto-update.log
#
# crontab (PATH обязателен у cron):
#   PATH=/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin:/snap/bin
#   * * * * * /opt/webserver/sites/MarketBW/docker/auto-update.sh
#
# Редирект в crontab не обязателен; можно оставить для дубля: >> /opt/webserver/log/... 2>&1
#
# chmod +x docker/auto-update.sh docker/deploy.sh
# Ветка: BRANCH=master перед путём в crontab.

# --- лог до set -e: иначе при ранней ошибке нечего писать ---
LOG_FILE="${MARKETBW_AUTO_UPDATE_LOG:-/opt/webserver/log/marketbw-auto-update.log}"
LOG_DIR="$(dirname "$LOG_FILE")"
if ! mkdir -p "$LOG_DIR" 2>/dev/null; then
  LOG_FILE="/tmp/marketbw-auto-update.log"
fi
exec >>"$LOG_FILE" 2>&1

export PATH="/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin${PATH:+:$PATH}"
[ -d /snap/bin ] && PATH="/snap/bin:$PATH"
export PATH

echo "[$(date -Iseconds)] === auto-update старт (лог-файл: $LOG_FILE, user=$(id -un 2>/dev/null || echo unknown)) ==="

set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
REPO_ROOT="$(cd "$SCRIPT_DIR/.." && pwd)"
BRANCH="${BRANCH:-main}"
LOCK_FILE="${LOCK_FILE:-/tmp/marketbw-auto-update.lock}"

log_msg() {
  echo "[$(date -Iseconds)] $*"
}

export BRANCH

if command -v flock >/dev/null 2>&1; then
  exec 9>"$LOCK_FILE"
  if ! flock -n 9; then
    log_msg "Пропуск: уже идёт другой запуск (flock), выход."
    exit 0
  fi
fi

if [ ! -d "$REPO_ROOT/.git" ]; then
  log_msg "Нет каталога .git в $REPO_ROOT, выход."
  exit 0
fi

cd "$REPO_ROOT"

if ! git fetch origin 2>/dev/null; then
  log_msg "git fetch origin не удался, выход."
  exit 0
fi

LOCAL=$(git rev-parse HEAD 2>/dev/null)
REMOTE=$(git rev-parse "origin/$BRANCH" 2>/dev/null)

if [ -z "$REMOTE" ]; then
  log_msg "Нет origin/$BRANCH после fetch, выход."
  exit 0
fi

if [ "$LOCAL" = "$REMOTE" ]; then
  log_msg "Обновлений нет (ветка $BRANCH, $(git rev-parse --short HEAD)), выход."
  exit 0
fi

log_msg "Есть коммиты на origin/$BRANCH, запуск deploy.sh update…"
exec "$SCRIPT_DIR/deploy.sh" update
