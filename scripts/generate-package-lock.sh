#!/usr/bin/env bash
# Создать/обновить package-lock.json без локального Node (нужен только Docker).
# Запуск из корня репозитория: ./scripts/generate-package-lock.sh
# Lockfile лежит в frontend/ (Vite).

set -euo pipefail
ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
exec docker run --rm \
  -v "$ROOT:/app" \
  -w /app/frontend \
  node:20-alpine \
  sh -c "npm install --package-lock-only --no-audit --no-fund"
