# syntax=docker/dockerfile:1
# BuildKit: DOCKER_BUILDKIT=1 — кэш npm между сборками (см. deploy.sh).

# Сборка: Node.js
FROM node:20-alpine AS base
# Без apk upgrade на каждый build — ускоряет слой; при необходимости обновляйте базовый образ node:20-alpine

# Зависимости (better-sqlite3 — один раз компилируется здесь)
FROM base AS deps
RUN apk add --no-cache libc6-compat python3 make g++
WORKDIR /app
COPY package*.json ./
# npm ci — быстрее и детерминированно, если в репозитории есть package-lock.json
RUN --mount=type=cache,target=/root/.npm \
    sh -c 'if [ -f package-lock.json ]; then npm ci --no-audit --no-fund; else npm install --no-audit --no-fund; fi'

FROM base AS builder
WORKDIR /app
COPY --from=deps /app/node_modules ./node_modules
COPY . .
ENV NODE_ENV=production
RUN npm run build && npm run build:server
# Второй npm install не делаем — убираем devDependencies из уже собранных node_modules
RUN npm prune --omit=dev

# Production: Node.js API + nginx
FROM node:20-alpine AS runner
WORKDIR /app

RUN apk add --no-cache nginx curl

COPY --from=builder /app/node_modules ./node_modules
COPY server.js ./
COPY --from=builder /app/dist ./dist
COPY --from=builder /app/server-build ./server-build

RUN mkdir -p /app/data

# Alpine nginx: conf.d включается вне http{} — server{} только в http.d
COPY docker/nginx.conf /etc/nginx/http.d/default.conf
COPY docker/start.sh /start.sh
RUN chmod +x /start.sh

EXPOSE 80
CMD ["/start.sh"]
