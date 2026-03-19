#!/usr/bin/env bash
# Автообновление MarketBW по cron при появлении новых коммитов на origin.
#
# Требования:
#   - клон с GitHub (есть .git), настроен remote origin;
#   - один раз выполнен install: есть docker/.env;
#   - Docker доступен пользователю cron (часто root — ок).
#
# crontab -e (каждую минуту проверка; сборка только если есть новые коммиты):
#   * * * * * /opt/webserver/sites/MarketBW/docker/auto-update.sh >> /var/log/marketbw-auto-update.log 2>&1
#
# Другая ветка (по умолчанию main):
#   * * * * * BRANCH=master /opt/.../docker/auto-update.sh >> /var/log/marketbw-auto-update.log 2>&1
#
# Параллельный запуск (пока идёт долгий build) блокируется flock.

set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
REPO_ROOT="$(cd "$SCRIPT_DIR/.." && pwd)"
BRANCH="${BRANCH:-main}"
LOCK_FILE="${LOCK_FILE:-/tmp/marketbw-auto-update.lock}"

export BRANCH
# deploy.sh update использует GIT_BRANCH или BRANCH для git pull

if command -v flock >/dev/null 2>&1; then
  exec 9>"$LOCK_FILE"
  flock -n 9 || exit 0
fi

if [ ! -d "$REPO_ROOT/.git" ]; then
  exit 0
fi

cd "$REPO_ROOT"

git fetch origin 2>/dev/null || exit 0

LOCAL=$(git rev-parse HEAD 2>/dev/null)
REMOTE=$(git rev-parse "origin/$BRANCH" 2>/dev/null)

if [ -z "$REMOTE" ] || [ "$LOCAL" = "$REMOTE" ]; then
  exit 0
fi

exec "$SCRIPT_DIR/deploy.sh" update
