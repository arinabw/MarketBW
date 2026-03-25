# AGENTS.md — навигация по кодовой базе MarketBW

Документ для **ИИ-агентов и разработчиков**: где что лежит, как течёт запрос, где менять поведение.

> **GRACE-артефакты** (Graph-RAG Anchored Code Engineering): `docs/requirements.xml`, `docs/technology.xml`, `docs/development-plan.xml`, `docs/verification-plan.xml`, `docs/knowledge-graph.xml`. Они — каноническая карта модулей, контрактов, потоков данных и плана верификации.

## Назначение проекта

Визитка мастера (украшения из бисера): каталог товаров, **раздел статей**, страницы «О мастере», контакты, FAQ. Админка: товары, категории, **контент сайта** (тексты/картинки из БД), FAQ, отзывы, пароль.

**Стек:** PHP 8.2+, Slim 4, Twig 3, SQLite, Docker (Nginx + PHP-FPM). Фронт — CSS без сборки (`public/css/app.css`).

---

## Дерево репозитория (семантика папок)

| Путь | Роль |
|------|------|
| `public/` | Document root: `index.php`, статика `css/`, `images/`, `favicon.svg` |
| `public/index.php` | Единственная точка входа HTTP → `app/bootstrap.php` |
| `app/bootstrap.php` | DI-контейнер, Twig, middleware (сессия, CSRF, **контент из БД**), `Database::init()`, подключение `routes.php` |
| `app/routes.php` | Все маршруты: публичные + группа `/admin/*`; хелперы загрузки изображений внизу файла |
| `app/Database.php` | PDO SQLite: схема, товары, категории, отзывы, FAQ, пользователи, **site_content**, смена пароля |
| `app/Seed.php` | Демо-данные при пустой БД |
| `app/SiteContentDefaults.php` | Ключи и дефолты текстов CMS, группы для админки `adminGroups()`, `imageKeys()` |
| `app/ProductImages.php` | Нормализация путей к фото товаров, data-URL → файлы |
| `app/SiteUpload.php` | Загрузка картинок для раздела «Контент» → `public/images/site/` |
| `config/settings.php` | Env: `SITE_NAME`, контакты, соцсети, пути `DATA_DIR`, `IMAGES_DIR`, опционально **`BASE_PATH`**, **`PUBLIC_SITE_URL`** (канонический URL для SEO) |
| `app/ArticleContent.php` | Чтение статических статей из `content/articles/` (без БД) |
| `app/SeoHelper.php` | Абсолютный origin сайта, JSON-LD Organization / WebSite / Product / BreadcrumbList / BlogPosting |
| `content/articles/` | Статические HTML-файлы статей по темам + конфиг `_topics.php` |
| `data/` | `marketbw.db` (на проде часто volume) |
| `templates/` | Twig: `base.twig`, страницы, `admin/*`, `partials/*` |
| `docs/` | GRACE-артефакты: requirements, technology, development-plan, verification-plan, knowledge-graph (XML) |
| `docker/` | Dockerfile, nginx, compose, `deploy.sh`, `env.example` |

---

## Поток запроса (обязательно понимать порядок)

1. `public/index.php` вызывает `app/bootstrap.php` и `->run()`.
2. Регистрируются middleware (Slim): в конце файла добавлен слой, который на **каждый запрос** подмешивает в Twig глобал `content` (слияние `SiteContentDefaults` + таблица `site_content`) и перезаписывает глобалы `master_*`, `contact_*`, `social_*`.
3. Маршруты из `app/routes.php`: публичные URL и группа `/admin` с guard (кроме логина).
4. Рендер: Twig-шаблоны; тексты с сайта — **`t('ключ')`** (ключи из `SiteContentDefaults`).

**CSRF:** `$_SESSION['csrf']`, в формах `csrf_token()`, проверка в POST админки и `/contact`.

**SEO:** в `templates/base.twig` — canonical, `hreflang`, Open Graph, Twitter Card, JSON-LD; middleware в `bootstrap.php` выставляет `seo_canonical_url`, `seo_absolute_base`. На страницах каталога, товара, о мастере, контактов, FAQ в `head` выводится **BreadcrumbList** (данные из `app/routes.php` + `SeoHelper::buildBreadcrumbJsonLd`). Публичные маршруты `GET /robots.txt`, `GET /sitemap.xml`. Дефолт **`PUBLIC_SITE_URL`** — `https://marketbw.ru` в `config/settings.php`; переопределение через env (см. `docker/env.example`).

**URL с `BASE_PATH`:** в Twig функция **`path('/foo')`** (и обновлённые **`catalog_url`**, **`nav_is_active`**) учитывают префикс из `settings['base_path']`. Ссылки и формы в шаблонах используют `path(...)`, чтобы подкаталог (`/shop/...`) работал согласованно.

---

## База данных SQLite (таблицы)

| Таблица | Назначение |
|---------|------------|
| `categories` | Категории каталога + путь к картинке категории |
| `products` | Товары: JSON `images`, `materials`, цена, featured и т.д. |
| `reviews` | Отзывы: опционально `product_id` |
| `contact_messages` | Заявки с формы «Контакты»: имя, email, текст, статус (`new` / `done` / `archived`), IP, User-Agent |
| `faqs` | Вопрос–ответ FAQ |
| `users` | Админ (логин + hash пароля) |
| `site_content` | CMS: `content_key` → `value` (переопределение дефолтов из кода/env) |
| `audit_log` | Журнал запросов (если включено `audit.log_enabled`); пишет `AuditLogMiddleware` |

Инициализация схемы: `Database::init()` при старте приложения.

---

## Контент сайта (CMS)

- **Ключи** и строки по умолчанию: `app/SiteContentDefaults.php` (`defaults()`, `adminGroups()`, `imageKeys()`, `layoutBooleanKeys()` — чекбоксы видимости отдельных элементов; старые ключи `layout.show_home_*` / `layout.show_product_reviews` из БД при отсутствии новых записей подмешиваются через `inheritLegacyLayoutToggles()` в `Database::getMergedSiteContent`).
- **Хранение переопределений:** `site_content`.
- **Шаблоны:** не хардкодить длинные тексты — `{{ t('ключ') }}`; HTML-фрагменты — `|raw` где осознанно.
- **Админка:** `GET/POST /admin/content`, шаблон `templates/admin/content.twig`.

---

## Админка (маршруты)

| URL | Назначение |
|-----|------------|
| `/admin/login`, `/admin/logout` | Вход / выход |
| `/admin` | Дашборд |
| `/admin/content` | Редактирование текстов и hero-картинки |
| `/admin/products`, `/admin/products/create` (и `/products/new`), `.../edit` | Товары |
| `/admin/categories`, `...` | Категории |
| `/admin/faqs`, `/admin/faqs/new`, `.../edit` | FAQ |
| `/admin/reviews`, `...` | Отзывы |
| `/admin/contact-messages`, `.../{id}`, смена статуса и удаление POST | Заявки с сайта (`contact_messages`) |
| `/admin/logs` | Журнал HTTP-событий (таблица `audit_log`, включается ключами `audit.log_*` в CMS) |
| `/admin/database`, `POST /admin/database/export` | Просмотр таблиц SQLite и выгрузка в Excel (SpreadsheetML `.xls`) |
| `/admin/password` | Смена пароля |

Навигация админки: `templates/admin/layout.twig`.

---

## Статьи (статика, без БД)

Раздел «Статьи» (`/articles`) реализован **полностью статически** — без хранения в БД.

- **Конфиг тем и статей:** `content/articles/_topics.php` — PHP-массив: ключ = slug темы → `name`, `description`, `articles[]` (slug, title, excerpt).
- **HTML-файлы:** `content/articles/{topic_slug}/{article_slug}.html` — тело статьи в чистом HTML (без обёрток/шаблонов).
- **PHP-класс:** `app/ArticleContent.php` — читает конфиг и файлы; используется маршрутами в `routes.php`.
- **Twig-шаблоны:** `templates/articles/index.twig`, `topic.twig`, `article.twig`.
- **Маршруты:** `GET /articles`, `GET /articles/{topicSlug}`, `GET /articles/{topicSlug}/{articleSlug}`.
- **SEO:** BreadcrumbList + BlogPosting JSON-LD, canonical URL, sitemap.

---

## Соглашения по коду

- PHP: `declare(strict_types=1);`, namespace `App\` для классов в `app/`.
- Новые маршруты — в `app/routes.php` внутри нужной группы; публичные — на уровне `$app->get/post`.
- Загрузка файлов: товары → `ProductImages` + `handle_image_uploads`; категории → `handle_category_upload`; контент сайта → `SiteUpload`.
- Версия релиза: см. `.cursor/rules/release-version.mdc` и `README.md`.

---

## Где менять типичные задачи

| Задача | Место |
|--------|--------|
| Новая публичная страница | `app/routes.php` + `templates/*.twig`, при необходимости ключи в `SiteContentDefaults` |
| Новая статья | HTML в `content/articles/{topic}/`, запись в `content/articles/_topics.php` |
| Текст на существующей странице | Ключ в `SiteContentDefaults`, шаблон — `t('ключ')`, при необходимости поле в `adminGroups()` |
| Стили сайта | `public/css/app.css` |
| Env-переменные | `config/settings.php`, `docker/env.example` |
| Деплой | `docker/deploy.sh`, `docker/Dockerfile` |

---

## Зависимости

См. `composer.json`: Slim, slim/twig-view, Twig, php-di. Фронт без npm в репозитории.
