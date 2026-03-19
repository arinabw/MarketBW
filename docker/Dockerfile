# Сборка: Node.js
FROM node:20-alpine AS base
RUN apk update && apk upgrade --no-cache

FROM base AS deps
RUN apk add --no-cache libc6-compat
WORKDIR /app
COPY package.json ./
# Без package-lock.json используем npm install (для npm ci добавьте lock-файл в репозиторий)
RUN npm install

FROM base AS builder
WORKDIR /app
COPY --from=deps /app/node_modules ./node_modules
COPY . .
ENV NODE_ENV=production
RUN npm run build

# Production: Node.js сервер + nginx
FROM node:20-alpine AS runner
WORKDIR /app

# Устанавливаем зависимости для админ-сервера
RUN apk add --no-cache curl

# Копируем админ-сервер
COPY server.js ./

# Создаем директорию для БД
RUN mkdir -p /app/data

# Копируем статический сайт
COPY --from=builder /app/dist /app/dist

# Копируем конфигурацию nginx
COPY docker/nginx.conf /etc/nginx/conf.d/default.conf

# Копируем скрипт запуска
COPY docker/start.sh /start.sh
RUN chmod +x /start.sh

EXPOSE 80
CMD ["/start.sh"]
