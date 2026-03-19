# syntax=docker/dockerfile:1
# BuildKit: DOCKER_BUILDKIT=1 — кэш npm между сборками (см. deploy.sh).
# В выводе docker compose build ищите [build-time] — отметки начала/конца этапов.

FROM node:20-alpine AS base

# Зависимости (better-sqlite3 — нативная компиляция; часто самый долгий шаг без кэша)
FROM base AS deps
RUN echo "[build-time] deps: apk toolchain START $(date -u +%Y-%m-%dT%H:%M:%SZ)" \
    && apk add --no-cache libc6-compat python3 make g++ \
    && echo "[build-time] deps: apk toolchain END   $(date -u +%Y-%m-%dT%H:%M:%SZ)"
WORKDIR /app
COPY package*.json ./
RUN --mount=type=cache,target=/root/.npm \
    sh -c '\
    echo "[build-time] deps: npm install/ci START $(date -u +%Y-%m-%dT%H:%M:%SZ)"; \
    if [ -f package-lock.json ]; then npm ci --no-audit --no-fund; else npm install --no-audit --no-fund; fi; \
    echo "[build-time] deps: npm install/ci END   $(date -u +%Y-%m-%dT%H:%M:%SZ)"'

FROM base AS builder
WORKDIR /app
COPY --from=deps /app/node_modules ./node_modules
COPY . .
ENV NODE_ENV=production
RUN echo "[build-time] builder: vite build START $(date -u +%Y-%m-%dT%H:%M:%SZ)" \
    && npm run build \
    && echo "[build-time] builder: vite build END   $(date -u +%Y-%m-%dT%H:%M:%SZ)"
RUN echo "[build-time] builder: tsc server START $(date -u +%Y-%m-%dT%H:%M:%SZ)" \
    && npm run build:server \
    && echo "[build-time] builder: tsc server END   $(date -u +%Y-%m-%dT%H:%M:%SZ)"
RUN echo "[build-time] builder: npm prune START $(date -u +%Y-%m-%dT%H:%M:%SZ)" \
    && npm prune --omit=dev \
    && echo "[build-time] builder: npm prune END   $(date -u +%Y-%m-%dT%H:%M:%SZ)"

FROM node:20-alpine AS runner
WORKDIR /app
RUN echo "[build-time] runner: apk nginx START $(date -u +%Y-%m-%dT%H:%M:%SZ)" \
    && apk add --no-cache nginx curl \
    && echo "[build-time] runner: apk nginx END   $(date -u +%Y-%m-%dT%H:%M:%SZ)"
COPY --from=builder /app/node_modules ./node_modules
COPY server.js ./
COPY --from=builder /app/dist ./dist
COPY --from=builder /app/server-build ./server-build
RUN mkdir -p /app/data
COPY docker/nginx.conf /etc/nginx/http.d/default.conf
COPY docker/start.sh /start.sh
RUN chmod +x /start.sh
EXPOSE 80
CMD ["/start.sh"]
