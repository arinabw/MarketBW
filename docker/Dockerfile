# Сборка: Node.js
FROM node:20-alpine AS base
RUN apk update && apk upgrade --no-cache

# Зависимости (better-sqlite3 — нативный модуль, нужны build tools)
FROM base AS deps
RUN apk add --no-cache libc6-compat python3 make g++
WORKDIR /app
COPY package.json ./
RUN npm install

FROM base AS builder
WORKDIR /app
COPY --from=deps /app/node_modules ./node_modules
COPY . .
ENV NODE_ENV=production
RUN npm run build && npm run build:server

# Production node_modules (без devDependencies)
FROM base AS prod-deps
RUN apk add --no-cache python3 make g++
WORKDIR /app
COPY package.json ./
RUN npm install --omit=dev && apk del python3 make g++

# Production: Node.js API + nginx
FROM node:20-alpine AS runner
WORKDIR /app

RUN apk add --no-cache nginx curl

COPY --from=prod-deps /app/node_modules ./node_modules
COPY server.js ./
COPY --from=builder /app/dist ./dist
COPY --from=builder /app/server-build ./server-build

RUN mkdir -p /app/data

COPY docker/nginx.conf /etc/nginx/conf.d/default.conf
COPY docker/start.sh /start.sh
RUN chmod +x /start.sh

EXPOSE 80
CMD ["/start.sh"]
