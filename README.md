# MarketBW

Сайт украшений из бисера ручной работы.

## Стек

- **Vue 3** + TypeScript + Vite
- **Tailwind CSS** с кастомной палитрой (primary/accent/surface)
- **Lucide** иконки
- **Pinia** для состояния
- **Docker** (nginx + Node API + SQLite) за **Traefik** — как в [idpro1313/webserver](https://github.com/idpro1313/webserver)

## Структура

```
src/
├── views/          # Страницы (Home, Catalog, Product, Contact, FAQ)
├── components/
│   ├── ui/         # AppButton, AppCard, ...
│   └── layout/     # AppHeader, AppFooter
├── router/         # Vue Router
├── stores/         # Pinia
├── lib/            # data.ts, env.ts, utils.ts, db.ts
└── styles/         # main.css (Tailwind)
docker/             # Dockerfile, compose, deploy.sh — деплой на сервер
data/               # на сервере: БД SQLite (том ../data из compose)
```

## Разработка

```bash
npm install
npm run dev
```

Контакты и название сайта — в **`src/lib/env.ts`**. Запросы админки к API идут на **`/api`** (см. `src/api/admin.ts`); отдельный `.env` в корне не нужен. Для сервера Traefik по-прежнему **`docker/.env`** из `docker/env.example`.

## Деплой на сервер (Traefik уже установлен)

Инфраструктура — репозиторий **[webserver](https://github.com/idpro1313/webserver)**: сеть Docker **`web`**, resolver **`le`**, HTTPS entrypoint обычно **`websecure`** (как в шаблоне `templates/node-site`).

### 1. Клонировать весь проект на сервер

Нужен **полный** репозиторий (не только папка `docker/`), например:

```bash
sudo git clone <url> /opt/MarketBW
cd /opt/MarketBW/docker
```

Контекст сборки в compose — родитель каталога `docker/`, том БД — **`/opt/MarketBW/data`**.

### 2. Сеть Traefik

Если ещё нет (на чистом Docker): `docker network create web`. Установка по webserver это делает автоматически.

### 3. Настроить `.env`

```bash
cp env.example .env
nano .env
```

Обязательно задайте уникальные на сервере **`SITE_CONTAINER_NAME`**, **`TRAEFIK_ROUTER`**, и правило **`TRAEFIK_RULE`**, например:

```env
TRAEFIK_RULE=Host(`mysite.ru`) || Host(`www.mysite.ru`)
```

(обратные кавычки вокруг доменов — как в [README webserver](https://github.com/idpro1313/webserver).)

При несовпадении имён entrypoint/resolver с вашим `reverse-proxy` поправьте **`TRAEFIK_HTTPS_ENTRYPOINT`** и **`TRAEFIK_CERT_RESOLVER`**.

### 4. Запуск и обновление

```bash
chmod +x deploy.sh auto-update.sh
# из корня репозитория можно так же:
# chmod +x scripts/marketbw-deploy.sh && ./scripts/marketbw-deploy.sh install

./deploy.sh install              # первый раз
./deploy.sh update               # git pull + сборка + up
./deploy.sh update --no-cache    # как в README node-site: полная пересборка
./deploy.sh rebuild              # без git: только build --no-cache + up
./deploy.sh logs|status|stop|restart
```

Скрипт **не делает `source .env`**, чтобы не ломаться на `Host(\`...\`)` в bash.

### Автообновление по cron

```bash
# пример: каждую минуту проверка ветки main
* * * * * BRANCH=main /opt/MarketBW/docker/auto-update.sh >> /var/log/marketbw-auto-update.log 2>&1
```

### Важно

- Порты **80/443** не пробрасываются в compose сайта — их слушает Traefik.
- Имя контейнера Traefik в webserver — **`traefik_proxy`**; конфликта портов с другим прокси быть не должно.
- У каждого сайта на одном хосте — **свои** `TRAEFIK_ROUTER` и `SITE_CONTAINER_NAME`.

## Конфигурация сайта

Контакты и параметры — `src/lib/env.ts`.
