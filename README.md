# MarketBW (marketbw.ru)

**Версия:** 3.6.0

Сайт-визитка рукоделия (украшения из бисера). **Текущий прод-стек** — лёгкий PHP + SQLite + Twig в Docker (Nginx + PHP-FPM за Traefik). Опциональный **журнал HTTP-событий** в БД (`/admin/logs`, ключи `audit.log_*` в разделе «Контент»).

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

### Админка: 502 на странице нового товара

Если **`/admin/products/new`** отвечает **502 Bad Gateway**, откройте **`/admin/products/create`** — это тот же экран и форма (дублирующий маршрут в приложении). Частая причина — правила WAF/ModSecurity на слове `new` в пути. Если 502 на **всех** страницах админки, смотрите логи **PHP-FPM** и **Nginx** на сервере (падение воркера, таймаут, нехватка памяти).

## SEO (Яндекс, Google)

- В `.env` задайте **`PUBLIC_SITE_URL`** — полный адрес без завершающего слэша (например `https://marketbw.ru` или `https://example.com/shop` при установке в подкаталоге). От этого зависят **canonical**, **Open Graph**, абсолютные URL в **`/sitemap.xml`**. Если переменная пуста, базовый URL берётся из заголовков запроса (`Host`, `X-Forwarded-*`).
- Карта сайта: **`/sitemap.xml`** (главная, каталог, категории, товары, о мастере, контакты, FAQ). **`/robots.txt`** — `Allow: /`, закрыта админка, указан Sitemap.
- Уникальные **meta description** на главной, каталоге, товаре (обрезка описания), о мастере, FAQ, контактах. Общий текст по умолчанию — ключ **`meta.description`** в разделе «Контент». На каждой странице — **JSON-LD** (WebSite, Organization; на карточке товара — **Product** + Offer).
- В кабинетах [Яндекс.Вебмастер](https://webmaster.yandex.ru/) и [Google Search Console](https://search.google.com/search-console) добавьте сайт и URL sitemap.

Если после **отправки формы** вас выкидывает на **логин**: (1) сессии пишутся в **`data/sessions`** на томе с БД — общие для реплик и переживают рестарт; (2) в **`docker-compose.yml`** по умолчанию **`SESSION_FORCE_SECURE=true`** — cookie с `Secure` при внешнем HTTPS; (3) заголовки **`X-Forwarded-Proto`**, **`Forwarded`**, **`X-Forwarded-SSL`** учитываются в коде и при необходимости пробрасываются из Nginx в PHP; (4) при **www** и **apex** задайте **`SESSION_COOKIE_DOMAIN=.ваш-домен.ru`**. Если ходите к контейнеру по **чистому HTTP** (без TLS), задайте **`SESSION_FORCE_SECURE=false`**. Редиректы учитывают **`BASE_PATH`**.

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
