# MarketBW

Сайт украшений из бисера ручной работы.

## Стек

- **Vue 3** + JavaScript + Vite
- **Tailwind CSS** с кастомной палитрой (primary/accent/surface)
- **Lucide** иконки
- **Pinia** для состояния
- **Docker**: один контейнер — **FastAPI (uvicorn)** на порту **8000**, SQLite, сборка фронта в **Node** (multi-stage Dockerfile) за **Traefik** — как в [idpro1313/webserver](https://github.com/idpro1313/webserver)

## Структура

```
src/                # Vue + JS (корень репозитория — root для Vite в frontend/vite.config.js)
├── views/          # Страницы (Home, Catalog, Product, Contact, FAQ)
├── components/
├── router/
├── stores/
├── api/            # клиенты /api
├── lib/            # env.js, utils.js
└── styles/
frontend/           # package.json, vite.config.js, lockfile — npm install здесь
backend/app/        # FastAPI: main.py, database.py
docker/             # Dockerfile, compose, deploy.sh
data/               # SQLite на сервере (том ../data из compose)
```

## Разработка

**Фронт** (Vite проксирует `/api` → `http://127.0.0.1:8000`):

```bash
cd frontend && npm install
cd .. && npm run dev
```

**API** (второй терминал):

```bash
cd backend
pip install -r requirements.txt
uvicorn app.main:app --reload --host 127.0.0.1 --port 8000
```

Контакты и название сайта — в **`src/lib/env.js`**. Запросы к API — **`/api`** (см. `src/api/`). Для сервера Traefik — **`docker/.env`** из `docker/env.example`; в compose для сервиса указан порт **8000**.

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

### Обновление на сервере после изменений в GitHub

**`docker/.env` в репозиторий не коммитится** (в `.gitignore`) — на `git pull` он не влияет.

Достаточно из каталога с проектом:

```bash
cd /opt/webserver/sites/MarketBW/docker
./deploy.sh update
```

Скрипт сам сделает `git pull` из **корня репозитория** (`MarketBW/`), затем пересоберёт образ и поднимет контейнер. Ручной `git pull` не обязателен.

Если **`git pull` падает с ошибкой** — обычно на сервере меняли отслеживаемые файлы (не `.env`). Посмотреть:

```bash
cd /opt/webserver/sites/MarketBW
git status
```

Варианты:

- откатить случайные правки в tracked-файлах: `git checkout -- <файл>`;
- или временно убрать свои правки: `git stash -u` → `./docker/deploy.sh update` → при необходимости `git stash pop` (осторожно с конфликтами).

Если когда-то **`docker/.env` попал в коммит**, Git будет ругаться при pull — тогда: `git rm --cached docker/.env` (локально), закоммитить исправление в репо, на сервере после pull оставить свой `.env` на диске.

### Автообновление по cron

`auto-update.sh` сравнивает `HEAD` с `origin/$BRANCH` (по умолчанию **main**); при отличии вызывает **`deploy.sh update`**. Скрипт сам **создаёт каталог лога**, **пишет в файл** (`exec >> …`) и задаёт **`PATH`** — даже без редиректа в crontab строки появляются в **`/opt/webserver/log/marketbw-auto-update.log`** (если каталог недоступен — **`/tmp/marketbw-auto-update.log`**). Свой путь: переменная **`MARKETBW_AUTO_UPDATE_LOG`**.

**`chmod +x docker/auto-update.sh docker/deploy.sh`**

```bash
# PATH в crontab по-прежнему нужен (первая строка без *):
PATH=/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin:/snap/bin

* * * * * /opt/webserver/sites/MarketBW/docker/auto-update.sh

# ветка master
* * * * * BRANCH=master /opt/webserver/sites/MarketBW/docker/auto-update.sh
```

Редирект `>> …log 2>&1` в crontab необязателен. Если лога нет — смотрите **`/tmp/marketbw-auto-update.log`** и что cron идёт от пользователя с правом **`docker`**.

**Перемешивание лога с выводом Docker:** раньше использовался `exec deploy.sh`, из‑за этого снималась блокировка и cron каждую минуту дописывал строки в тот же файл во время сборки. Сейчас вызывается **`bash deploy.sh`** и один **`flock`** на весь запуск (`/tmp/marketbw-auto-update.run.lock`).

### Ускорение сборки Docker на сервере

- **`deploy.sh` включает BuildKit** — кэшируется npm между сборками фронта (`RUN --mount=type=cache,target=/root/.npm`).
- Слой **Python** кэшируется, пока не меняется `backend/requirements.txt`.
- Если есть **`frontend/package-lock.json`**, в Docker используется **`npm ci`**; иначе — **`npm install`**.

**Добавить lock локально:** `cd frontend && npm install` и закоммитить **`frontend/package-lock.json`**.

**Только Docker на машине:** `chmod +x scripts/generate-package-lock.sh && ./scripts/generate-package-lock.sh`

### Важно

- Порты **80/443** не пробрасываются в compose сайта — их слушает Traefik.
- Имя контейнера Traefik в webserver — **`traefik_proxy`**; конфликта портов с другим прокси быть не должно.
- У каждого сайта на одном хосте — **свои** `TRAEFIK_ROUTER` и `SITE_CONTAINER_NAME`.

## Конфигурация сайта

Контакты и параметры — `src/lib/env.js`.
