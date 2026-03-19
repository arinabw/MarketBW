FROM node:20-alpine

WORKDIR /app

# Копируем package.json и устанавливаем зависимости
COPY package*.json ./
RUN npm ci --only=production

# Копируем приложение
COPY . .

# Создаем директорию для БД
RUN mkdir -p /app/data

# Экспортируем переменные окружения
ENV NODE_ENV=production
ENV PORT=3000

# Запускаем приложение
EXPOSE 3000
CMD ["node", "server.js"]
