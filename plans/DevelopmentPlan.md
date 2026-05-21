$START_DEV_PLAN

**PURPOSE:** Визитка мастера (украшения из бисера): каталог, статические статьи, CMS (`site_content`), админка, SEO (sitemap, JSON-LD). **Версия:** см. `README.md` (3.13.x).

**Стек:** PHP 8.2+, Slim 4, Twig 3, SQLite, Nginx + PHP-FPM, Docker. CSS без сборки (`public/css/app.css`).

**Принципы:** минимальный стек; читаемые контракты M-*; без JS-фреймворков. **Риск:** блокировка SQLite при параллельной записи.

**Якоря в коде:** `MODULE_CONTRACT`, `START_BLOCK_*` / `END_BLOCK_*` (идентификаторы модулей).

**Для людей:** `docs/README.md` (поток запроса, БД, админ-маршруты).

---

## Модули

### Layer 0 — конфигурация и стили

| M-* | Путь | Назначение |
|-----|------|------------|
| M-SETTINGS | `config/settings.php` | Env, base_path, public_site_url |
| M-CONTENT-DEFAULTS | `app/SiteContentDefaults.php` | CMS keys, adminGroups, imageKeys |
| M-CSS | `public/css/app.css` | Стили, print, article-table |

### Layer 1 — данные и утилиты

| M-* | Путь | Назначение |
|-----|------|------------|
| M-DATABASE | `app/Database.php` | SQLite CRUD, init, site_content merge |
| M-SEED | `app/Seed.php` | Демо при пустой БД |
| M-ARTICLE-CONTENT | `app/ArticleContent.php` | `content/articles/` + `_topics.php` |
| M-ARTICLE-FILES | `content/articles/**/*.html` | 39 статей, 8 тем |
| M-SEO | `app/SeoHelper.php` | JSON-LD, canonical |
| M-PRODUCT-IMAGES | `app/ProductImages.php` | Пути фото, data-URL |
| M-SITE-UPLOAD | `app/SiteUpload.php` | CMS images → `public/images/site/` |
| M-HTTPS-DETECTOR | `app/HttpsDetector.php` | HTTPS за proxy |
| M-EXCEL-EXPORT | `app/DatabaseExcelExport.php` | Export .xls |
| M-AUDIT-LOG | `app/AuditLogMiddleware.php` | audit_log |

### Layer 2–3 — оркестрация

| M-* | Путь | Назначение |
|-----|------|------------|
| M-BOOTSTRAP | `app/bootstrap.php` | DI, Twig, CMS middleware, CSRF |
| M-ROUTES | `app/routes.php` | Все HTTP + `/admin/*` |
| M-ENTRY | `public/index.php` | Entry → bootstrap → run |
| M-TWIG-BASE | `templates/base.twig` | SEO head, `t()`, `path()` |
| M-TWIG-ADMIN | `templates/admin/layout.twig` | Админ-навигация |
| M-TWIG-ARTICLES | `templates/articles/*.twig` | Раздел статей |
| M-NGINX | `docker/nginx.conf` | gzip, security headers, cache |
| M-MANIFEST | `public/site.webmanifest` | PWA meta |

---

## Data flows

| ID | Описание |
|----|----------|
| DF-CATALOG | GET /catalog → DB → Twig |
| DF-PRODUCT | GET /product/{id} → Product JSON-LD |
| DF-ARTICLES | ArticleContent → BlogPosting JSON-LD |
| DF-CONTACT | POST /contact → CSRF → contact_messages |
| DF-AUTH | POST /admin/login → session |
| DF-ADMIN-CRUD | Admin POST → DB → redirect |
| DF-SEO | robots + sitemap + lastmod |
| DF-CMS | Middleware merge SiteContentDefaults + site_content |

---

## Phases (implemented)

0. Foundation — settings, DB, seed, content defaults  
1. Core — bootstrap, routes, templates, CSS  
2. Public — catalog, product, articles, SEO, contact, FAQ  
3. Admin — auth, CRUD, CMS, messages, logs, DB export  
4–6. GRACE markup, SEO polish, JSON-LD v2  

---

### 1. Draft Code Graph

См. **`plans/AppGraph.xml`**.

```xml
<DraftCodeGraph>
  <public_index_php FILE="public/index.php" TYPE="ENTRY_POINT">
    <CrossLinks><Link TARGET="app_bootstrap_php" TYPE="REQUIRES" /></CrossLinks>
  </public_index_php>
  <app_bootstrap_php FILE="app/bootstrap.php" TYPE="ORCHESTRATION">
    <CrossLinks>
      <Link TARGET="app_routes_php" TYPE="INCLUDES" />
      <Link TARGET="app_Database_php" TYPE="CALLS_INIT" />
    </CrossLinks>
  </app_bootstrap_php>
  <app_routes_php FILE="app/routes.php" TYPE="ROUTING">
    <CrossLinks>
      <Link TARGET="app_ArticleContent_php" TYPE="READS_ARTICLES" />
      <Link TARGET="app_SeoHelper_php" TYPE="BUILDS_JSONLD" />
    </CrossLinks>
  </app_routes_php>
</DraftCodeGraph>
```

---

### 2. Step-by-step Data Flow

1. `index.php` → `bootstrap.php`: settings, `Database::init()`, Twig, CMS middleware, audit.
2. `routes.php`: match URL → DB / ArticleContent → SeoHelper → Twig render.
3. Admin: guard session → CRUD forms with CSRF.
4. Статические статьи: только чтение файлов, не БД.

---

### 3. Acceptance Criteria

- [ ] Новый маршрут: `app/routes.php` + строка в `docs/README.md` + узел в AppGraph.
- [ ] Новый CMS-ключ: `SiteContentDefaults` + при необходимости миграция БД.
- [ ] SEO: sitemap включает новые публичные URL.
- [ ] `tests/test_guide.md` обновлён при новых V-M-*.

$END_DEV_PLAN
