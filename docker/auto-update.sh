#!/bin/bash
# Автообновление по cron (как на сервере с webserver).
# Рекомендация: crontab -e
#   * * * * * /opt/MarketBW/docker/auto-update.sh >> /var/log/marketbw-auto-update.log 2>&1
#
# Ветка: переменная BRANCH (по умолчанию main).

set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
REPO_ROOT="$(cd "$SCRIPT_DIR/.." && pwd)"
BRANCH="${BRANCH:-main}"

cd "$REPO_ROOT"

git fetch origin 2>/dev/null || exit 0

LOCAL=$(git rev-parse HEAD 2>/dev/null)
REMOTE=$(git rev-parse "origin/$BRANCH" 2>/dev/null)

if [ -z "$REMOTE" ] || [ "$LOCAL" = "$REMOTE" ]; then
  exit 0
fi

exec "$SCRIPT_DIR/deploy.sh" update
