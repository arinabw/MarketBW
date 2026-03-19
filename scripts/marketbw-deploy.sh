#!/usr/bin/env bash
# Обертка для вызова из корня репозитория (удобно на сервере рядом с /opt/webserver).
# Пример: /opt/MarketBW/scripts/marketbw-deploy.sh install

set -euo pipefail
ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
exec "$ROOT/docker/deploy.sh" "$@"
