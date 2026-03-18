# Базовый образ Node.js
FROM node:25-alpine AS base

# Обновление пакетов Alpine
RUN apk update && apk upgrade --no-cache

# Установка зависимостей для сборки
FROM base AS deps
RUN apk add --no-cache libc6-compat
WORKDIR /app

# Копирование файлов зависимостей
COPY package.json ./
RUN npm install

# Сборка приложения
FROM base AS builder
WORKDIR /app
COPY --from=deps /app/node_modules ./node_modules
COPY . .

# Установка переменных окружения для сборки
ENV NODE_ENV=production

# Сборка Vite приложения
RUN npm run build

# Финальный образ для запуска
FROM base AS runner
WORKDIR /app

ENV NODE_ENV=production

COPY --from=builder /app/dist ./dist
COPY --from=builder /app/package.json ./package.json

EXPOSE 4173

CMD ["npm", "run", "preview", "--", "--host", "0.0.0.0", "--port", "4173"]
