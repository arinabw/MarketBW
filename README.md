# MarketBW (marketbw.ru)

**Версия:** 3.1.2

Сайт-визитка рукоделия (украшения из бисера). **Текущий прод-стек** — лёгкий PHP + SQLite + Twig в Docker (Nginx + PHP-FPM за Traefik).

## Стек (актуальный)

| Слой | Технология |
|------|------------|
| HTTP | **Nginx** → **PHP 8.3-FPM** (Alpine) |
| Приложение | **Slim 4** (маршруты), **Twig 3** |
| Данные | **SQLite** (`marketbw.db`) |
| Контейнер | Один образ, **порт 80** (Traefik → сервис) |

Память: образ без Node/Python; на VDS достаточно **<200 MB RAM** при типичной нагрузке визитки.

## Структура репозитория

```
app/              # PHP: bootstrap, routes, Database, Seed
config/           # settings.php (env + дефолты)
public/           # document root: index.php, css/, images/
templates/        # Twig (сайт + /admin)
docker/           # Dockerfile, nginx.conf, supervisord.conf, compose, env.example
data/             # SQLite (том на сервере: ../data → /var/www/data)
AGENTS.md         # карта кодовой базы для разработчиков и ИИ-агентов
.cursor/rules/    # правила Cursor (архитектура, PHP, Twig, релизы)
```

## Архитектура и навигация по коду

Чтобы быстрее ориентироваться в проекте (в том числе ИИ-агентам): смотрите **`AGENTS.md`** — поток запроса, таблицы БД, маршруты админки, где менять тексты и стили. В **`.cursor/rules/`** — сжатые правила для редактора.

## Локальная разработка

1. Установите зависимости PHP:

```bash
composer install
```

2. Запуск встроенного сервера PHP (из корня репозитория):

```bash
php -S localhost:8080 -t public
```

Откройте http://localhost:8080 — статика `/images` и `/css` отдаётся из `public/`.

Переменные (опционально): скопируйте значения из `config/settings.php` или задайте `DATA_DIR`, `IMAGES_DIR`, `CONTACT_EMAIL` и т.д. в окружении.

Если сайт открывается **не из корня домена** (например `https://example.com/shop/`), задайте в окружении **`BASE_PATH=shop`** (без слэшей) — иначе Slim не сопоставит маршруты и даст 404.

## Админка

- URL: **`/admin`** (и **`/admin/`**)
- Логин по умолчанию после первой инициализации БД: **`admin`** / **`admin123`** — **смените пароль** после деплоя (хэш хранится в SQLite).

## Деплой (Docker + Traefik)

Инфраструктура — как в [idpro1313/webserver](https://github.com/idpro1313/webserver): сеть Docker **`web`**, HTTPS entrypoint обычно **`websecure`**, resolver **`le`**.

```bash
cd /opt/MarketBW/docker
cp env.example .env
# Правьте TRAEFIK_RULE, домен, контакты (SITE_NAME, CONTACT_*)
docker network create web   # если ещё нет
docker compose up -d --build
```

### Тома на сервере

| Том в compose | Назначение |
|---------------|------------|
| `../data` → `/var/www/data` | Файл **`marketbw.db`** (SQLite) |
| `../public/images` → `/var/www/images` | Картинки каталога и загрузки из админки (URL вида `/images/...`) |

При первом запуске пустой папки `data/` создаётся БД и демо-данные (если таблицы пустые). Картинки демо копируются в образ в `/var/www/images`; при монтировании тома поверх `../public/images` используйте файлы из репозитория или свои.

Старт контейнера выполняет **`docker/docker-entrypoint.sh`**: `chown` на `/var/www/data` и `/var/www/images` под пользователя **www-data** (PHP-FPM), чтобы SQLite не был «readonly». Если на хосте том не даёт менять владельца, сделайте каталог `data` доступным на запись (например `chmod 775` / владелец UID контейнера).

### Traefik

В `docker-compose.yml` для сервиса указан порт **`80`** (`loadbalancer.server.port=80`).

## Версионирование релизов

При выпуске новой версии обновите везде одно и то же значение (семвер), затем коммит и push:

| Файл | Что править |
|------|-------------|
| `VERSION` | строка, например `3.1.1` |
| `composer.json` | поле `"version"` |
| `README.md` | строка **Версия:** в шапке |
| `docker/Dockerfile` | `LABEL org.opencontainers.image.version="…"` |

---

## Лицензия / контакты

Проект частный; контакты и соцсети настраиваются через переменные окружения или `config/settings.php`.
