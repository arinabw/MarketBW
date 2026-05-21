# Test guide — MarketBW (mode-qa)

## Политика

- Синтаксис: `php -l` на изменённых PHP-файлах.
- Smoke HTTP — только по явной просьбе пользователя или в Docker (`mode-qa` + `docker/README.md`).
- Не логировать пароли, CSRF, session ID.

## Phase gates

| Фаза | Проверка |
|------|----------|
| Phase-0 | `php -l config/settings.php app/Database.php app/Seed.php` |
| Phase-1 | Docker up; `GET /` → 200 |
| Phase-2 | Публичные URL + `sitemap.xml` |
| Phase-3 | Login `/admin/login`; guard; CRUD smoke |

## Critical flows (CF-*)

| CF | Сценарий |
|----|----------|
| CF-CATALOG | /catalog → /product/{id}; JSON-LD Product |
| CF-ARTICLES | /articles → тема → статья; BlogPosting |
| CF-AUTH | POST login; guard /admin/* |
| CF-CONTACT | POST /contact; contact_messages |
| CF-CMS | Middleware → `t('key')` из merged content |

## Модули → проверки (V-M-*)

| Module | Checks |
|--------|--------|
| M-SETTINGS | `php -l config/settings.php` |
| M-CONTENT-DEFAULTS | `php -l app/SiteContentDefaults.php` |
| M-DATABASE | `php -l app/Database.php`; init, authenticate, CRUD |
| M-SEED | `php -l app/Seed.php` |
| M-ARTICLE-CONTENT | `php -l app/ArticleContent.php`; 8 тем, 39 slug |
| M-SEO | `php -l app/SeoHelper.php`; JSON-LD types |
| M-BOOTSTRAP | `php -l app/bootstrap.php` |
| M-ROUTES | `php -l app/routes.php`; GET /, /catalog, /articles, /admin guard |
| M-ARTICLE-FILES | 39 HTML в `content/articles/`; `article-table` |

PHPUnit запланирован в legacy-плане; при появлении — дописать сюда.
