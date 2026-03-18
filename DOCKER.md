# Запуск сайта MarketBW в Docker

Этот документ описывает, как запустить сайт MarketBW в Docker контейнере.

## Требования

- Docker (версия 20.10 или выше)
- Docker Compose (версия 2.0 или выше)

## Быстрый старт

### 1. Сборка и запуск

Перейдите в директорию `docker` и выполните:

```bash
cd docker
docker compose up -d --build
```

(На старых системах может быть команда `docker-compose` вместо `docker compose`.)

Эта команда:
- Соберет Docker образ
- Запустит контейнер в фоновом режиме
- Сайт будет доступен по адресу `http://localhost:3000`

### 2. Просмотр логов

```bash
docker compose logs -f
```

(Выполнять из папки `docker`.)

### 3. Остановка контейнера

```bash
docker compose down
```

## Использование скрипта развертывания

Для удобства предоставлен скрипт `deploy.sh` с различными командами:

```bash
cd docker
chmod +x deploy.sh
./deploy.sh install    # Первичная установка
./deploy.sh update     # Обновление сайта
./deploy.sh restart    # Перезапуск
./deploy.sh stop       # Остановка
./deploy.sh logs       # Просмотр логов
./deploy.sh status     # Проверка статуса
./deploy.sh clean      # Полная очистка
```

## Настройка сайта

Конфигурация сайта находится в файле `src/lib/env.ts`. Для изменения параметров сайта (название, URL, контакты, социальные сети) отредактируйте этот файл.

## Структура Docker контейнера

Сайт использует многоступенчатую сборку (multi-stage build):

1. **deps** — установка зависимостей;
2. **builder** — сборка Vite-приложения (`npm run build`);
3. **runner** — production-образ на **nginx**: раздача статики из `dist/`, без Node.js.

Преимущества:
- минимальный и безопасный образ (только nginx + статика);
- типичный production-подход для SPA.

## Порты

По умолчанию сайт работает на порту `3000`. Вы можете изменить порт в `docker-compose.yml`:

```yaml
ports:
  - "8080:3000"  # Сайт будет доступен на порту 8080
```

## Обновление сайта

Для обновления сайта:

1. Получите последние изменения из git (если используете)
2. Пересоберите и перезапустите контейнер:

```bash
cd docker
docker compose down
docker compose up -d --build
```

Или используйте скрипт:

```bash
./deploy.sh update
```

## Решение проблем

### 502 Bad Gateway (Caddy проксирует на MarketBW)

Ошибка значит: Caddy получил запрос, но не дождался ответа от контейнера MarketBW. Проверьте по шагам.

1. **Контейнер запущен и слушает порт**
   На сервере:
   ```bash
   docker ps
   ```
   Должен быть контейнер `marketbw-app` (или сервис `marketbw`) в статусе `Up`. Проверьте, что из контейнера отвечает nginx:
   ```bash
   docker exec marketbw-app wget -qO- http://127.0.0.1:3000/ | head -5
   ```
   Должен вернуться HTML. Если нет — перезапустите: `docker compose restart` (из папки проекта MarketBW).

2. **Caddy в Docker и `reverse_proxy marketbw:3000`**
   Имя `marketbw` должно резолвиться из контейнера Caddy. Контейнеры должны быть в **одной Docker-сети**:
   ```bash
   docker network ls
   docker network inspect <сеть_где_caddy>
   ```
   В списке контейнеров сети должен быть контейнер MarketBW. Если его нет — добавьте в `docker-compose.yml` MarketBW внешнюю сеть Caddy (см. блок «Если Caddy в Docker» выше) и выполните:
   ```bash
   docker compose up -d
   ```
   После этого перезапустите Caddy.

3. **Caddy на хосте и `reverse_proxy 127.0.0.1:3000`**
   Порт 3000 должен быть проброшен на хост. В `docker-compose.yml` должно быть:
   ```yaml
   ports:
     - "3000:3000"
   ```
   Проверка с сервера:
   ```bash
   curl -s -o /dev/null -w "%{http_code}" http://127.0.0.1:3000/
   ```
   Ожидается `200`. Если нет — контейнер не слушает на хосте или упал, смотрите п. 1.

4. **Логи Caddy**
   При 502 в логах Caddy часто видна причина (connection refused, timeout, wrong host):
   ```bash
   docker logs <контейнер_caddy> 2>&1 | tail -50
   ```

### Контейнер не запускается

Проверьте логи:

```bash
docker compose logs
```

### Ошибки сборки

Убедитесь, что все файлы проекта на месте и Docker имеет доступ к ним.

### Проблемы с портами

Если порт 3000 уже занят, измените его в `docker-compose.yml`.

## Домен marketbw.ru и порт 80 занят

**Важно:** в DNS нельзя «указать порт». DNS только связывает домен с IP (запись A: `marketbw.ru` → IP сервера). Порт 80/443 браузер подставляет сам при открытии `http://marketbw.ru` или `https://marketbw.ru`.

Если на сервере порт 80 уже занят (другой сайт или сервис), есть два варианта.

### Вариант 1: Обратный прокси (рекомендуется)

На сервере на порту 80 уже слушает Caddy (или nginx). Добавьте виртуальный хост для `marketbw.ru`.

**Caddy** — в Caddyfile рядом с остальными сайтами:

```caddy
marketbw.ru www.marketbw.ru {
	reverse_proxy marketbw:3000
	encode gzip zstd
}
```

Контейнер MarketBW должен быть в той же Docker-сети, что и Caddy, чтобы имя `marketbw` резолвилось. Если Caddy запущен на хосте (не в Docker), укажите `127.0.0.1:3000` вместо `marketbw:3000`.

**Если Caddy в Docker:** подключите сервис MarketBW к сети Caddy. В `docker-compose.yml` проекта MarketBW добавьте внешнюю сеть и привязку к ней:

```yaml
services:
  marketbw:
    # ... остальное без изменений ...
    networks:
      - marketbw-stack-net
      - caddy_net   # имя сети, где крутится Caddy (уточните через docker network ls)

networks:
  marketbw-stack-net:
    driver: bridge
    name: marketbw-stack-net
  caddy_net:
    external: true
```

Имя сети (`caddy_net`) посмотрите командой `docker network ls` в том проекте, где запущен Caddy.

**Nginx** (если вдруг используете):

```nginx
server {
    listen 80;
    server_name marketbw.ru www.marketbw.ru;
    location / {
        proxy_pass http://127.0.0.1:3000;
        proxy_http_version 1.1;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
    }
}
```

В DNS у домена должна быть A-запись на IP этого сервера. Тогда пользователи открывают **http://marketbw.ru** (без порта).

### Вариант 2: Открыть сайт по порту в URL

Контейнер маппится на порт 3000, в DNS — только A-запись на сервер. Сайт доступен по адресу **http://marketbw.ru:3000**. Минус: некрасиво, порт виден в ссылках и часто блокируется файрволами.

---

## Production развертывание

Для production развертывания рекомендуется:

1. Использовать обратный прокси на порту 80/443 (как выше для marketbw.ru)
2. Настроить HTTPS (Let's Encrypt) в этом же nginx
3. Использовать volumes для персистентности (если нужно)
4. Настроить мониторинг и логирование

## Полезные команды

```bash
# Просмотр запущенных контейнеров
docker ps

# Вход в контейнер
docker compose exec marketbw sh
# или (если используется docker-compose v1): docker-compose exec marketbw sh

# Перезапуск контейнера
docker compose restart

# Удаление контейнеров и образов
docker compose down -v --rmi all
```

## Поддержка

При возникновении проблем проверьте:
- Версии Docker и Docker Compose
- Доступность портов
- Логи контейнера
- Правильность переменных окружения
