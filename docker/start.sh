#!/bin/sh

# Запуск Node.js сервера в фоне
node server.js &

# Ожидание запуска сервера
sleep 3

# Запуск nginx
nginx -g "daemon off;"
