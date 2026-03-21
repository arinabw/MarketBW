#!/bin/bash
# Автообновление MarketBW по cron при появлении новых коммитов на origin.
# Срабатывание: только если есть коммиты на origin/$BRANCH, которых нет в HEAD
# (git rev-list --count HEAD..origin/$BRANCH > 0). Иначе выход без deploy.
#
# ВАЖНО: нельзя «exec deploy.sh» — процесс заменяется и снимается flock, тогда cron
# каждую минуту пишет в тот же лог параллельно с docker build (лог перемешивается).
#
# Логи:
#   Каталог: MARKETBW_AUTO_UPDATE_LOG_DIR (по умолчанию /opt/webserver/log/marketbw-auto-update/)
#   или dirname от MARKETBW_AUTO_UPDATE_LOG (обратная совместимость со старым путём к файлу).
#   На каждое обновление (запуск deploy) — отдельный файл:
#     marketbw-update-YYYYMMDD-HHMMSS-PID.log
#   Сообщение «Пропуск» (занят lock) — дописывается в summary.log в том же каталоге.
# Блокировка всего цикла (включая deploy): MARKETBW_AUTO_UPDATE_RUN_LOCK или
#   /tmp/marketbw-auto-update.run.lock
#
# crontab:
#   PATH=/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin:/snap/bin
#   * * * * * /opt/webserver/sites/MarketBW/docker/auto-update.sh

if [ -n "${MARKETBW_AUTO_UPDATE_LOG:-}" ]; then
  LOG_DIR="$(dirname "$MARKETBW_AUTO_UPDATE_LOG")"
else
  LOG_DIR="${MARKETBW_AUTO_UPDATE_LOG_DIR:-/opt/webserver/log/marketbw-auto-update}"
fi
if ! mkdir -p "$LOG_DIR" 2>/dev/null; then
  LOG_DIR="/tmp/marketbw-auto-update-logs"
  mkdir -p "$LOG_DIR" 2>/dev/null || true
fi

SUMMARY_LOG="$LOG_DIR/summary.log"

RUN_LOCK="${MARKETBW_AUTO_UPDATE_RUN_LOCK:-/tmp/marketbw-auto-update.run.lock}"

# Единственный активный экземпляр на всё время (fetch → deploy/build).
# Сообщение «Пропуск» пишем под коротким flock на summary.log — без смешивания с docker.
if command -v flock >/dev/null 2>&1; then
  exec 9>"$RUN_LOCK"
  if ! flock -n 9; then
    _skip_ts="$(date -Iseconds)"
    flock -w 5 "$SUMMARY_LOG" bash -c "echo \"[${_skip_ts}] Пропуск: уже идёт другой экземпляр (deploy/build), выход.\" >> \"\$1\"" _ "$SUMMARY_LOG" || true
    exit 0
  fi
fi

export PATH="/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin${PATH:+:$PATH}"
[ -d /snap/bin ] && PATH="/snap/bin:$PATH"
export PATH

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

REMOTE_REF="origin/$BRANCH"
REMOTE=$(git rev-parse "$REMOTE_REF" 2>/dev/null)

if [ -z "$REMOTE" ]; then
  log_msg "Нет $REMOTE_REF после fetch, выход."
  exit 0
fi

# Только «отставание» от удалённой ветки: коммиты на origin, которых нет в HEAD.
# Сравнение HEAD == origin/… даёт ложные срабатывания при другой локальной ветке/merge-base.
BEHIND=$(git rev-list --count "HEAD..$REMOTE_REF" 2>/dev/null || echo 0)

if [ "${BEHIND:-0}" -eq 0 ]; then
  log_msg "Новых коммитов на $REMOTE_REF нет (behind=0, $(git rev-parse --short HEAD)), выход."
  exit 0
fi

UPDATE_LOG="$LOG_DIR/marketbw-update-$(date +%Y%m%d-%H%M%S)-$$.log"
exec >>"$UPDATE_LOG" 2>&1

log_msg "Начало обновления (отдельный лог: $UPDATE_LOG, user=$(id -un 2>/dev/null || echo unknown))"
log_msg "На $REMOTE_REF на $BEHIND коммит(ов) впереди локального HEAD, запуск deploy.sh update…"
# Не exec — иначе этот процесс исчезает и отпускает RUN_LOCK до завершения сборки.
bash "$SCRIPT_DIR/deploy.sh" update
log_msg "deploy.sh update завершён."
