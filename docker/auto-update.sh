#!/bin/bash
# Автообновление MarketBW по cron при появлении новых коммитов на origin.
#
# ВАЖНО: нельзя «exec deploy.sh» — процесс заменяется и снимается flock, тогда cron
# каждую минуту пишет в тот же лог параллельно с docker build (лог перемешивается).
#
# Лог: MARKETBW_AUTO_UPDATE_LOG или /opt/webserver/log/marketbw-auto-update.log
# Блокировка всего цикла (включая deploy): /tmp/marketbw-auto-update.run.lock
#
# crontab:
#   PATH=/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin:/snap/bin
#   * * * * * /opt/webserver/sites/MarketBW/docker/auto-update.sh

LOG_FILE="${MARKETBW_AUTO_UPDATE_LOG:-/opt/webserver/log/marketbw-auto-update.log}"
LOG_DIR="$(dirname "$LOG_FILE")"
if ! mkdir -p "$LOG_DIR" 2>/dev/null; then
  LOG_FILE="/tmp/marketbw-auto-update.log"
fi

RUN_LOCK="${MARKETBW_AUTO_UPDATE_RUN_LOCK:-/tmp/marketbw-auto-update.run.lock}"

# Единственный активный экземпляр на всё время (fetch → deploy/build).
# Сообщение «Пропуск» пишем под коротким flock на сам лог — без смешивания с docker.
if command -v flock >/dev/null 2>&1; then
  exec 9>"$RUN_LOCK"
  if ! flock -n 9; then
    _skip_ts="$(date -Iseconds)"
    flock -w 5 "$LOG_FILE" bash -c "echo \"[${_skip_ts}] Пропуск: уже идёт другой экземпляр (deploy/build), выход.\" >> \"\$1\"" _ "$LOG_FILE" || true
    exit 0
  fi
fi

exec >>"$LOG_FILE" 2>&1

export PATH="/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin${PATH:+:$PATH}"
[ -d /snap/bin ] && PATH="/snap/bin:$PATH"
export PATH

echo "[$(date -Iseconds)] === auto-update старт (лог: $LOG_FILE, user=$(id -un 2>/dev/null || echo unknown)) ==="

set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
REPO_ROOT="$(cd "$SCRIPT_DIR/.." && pwd)"
BRANCH="${BRANCH:-main}"

log_msg() {
  echo "[$(date -Iseconds)] $*"
}

export BRANCH

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
# Не exec — иначе этот процесс исчезает и отпускает RUN_LOCK до завершения сборки.
bash "$SCRIPT_DIR/deploy.sh" update
log_msg "deploy.sh update завершён."
