#!/bin/bash
# Проверка обновлений в git и пересборка контейнера при изменениях.
# Запускать по crontab каждую минуту.

set -e

# Каталог репозитория (родитель папки docker)
SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"
REPO_DIR="$(dirname "$SCRIPT_DIR")"
BRANCH="${BRANCH:-main}"

cd "$REPO_DIR"

# Обновить ссылки с remote, без слияния
git fetch origin 2>/dev/null || exit 0

# Есть ли новые коммиты на origin?
LOCAL=$(git rev-parse HEAD 2>/dev/null)
REMOTE=$(git rev-parse "origin/$BRANCH" 2>/dev/null)

if [ -z "$REMOTE" ] || [ "$LOCAL" = "$REMOTE" ]; then
    exit 0
fi

# Есть изменения — подтягиваем и пересобираем
cd "$SCRIPT_DIR"
exec ./deploy.sh update
