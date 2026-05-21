# Business requirements — MarketBW

## Project

**MarketBW** — визитка мастера украшений из бисера: каталог, статьи (статические HTML), CMS, админка, SEO.

**Keywords:** бисер, handmade, каталог, статьи, CMS, PHP, Slim, Twig, SQLite.

## Actors

- **Visitor** — каталог, статьи, FAQ, контакты.
- **Admin** — товары, категории, CMS, отзывы, FAQ, заявки, логи.
- **SearchEngine** — sitemap, robots, JSON-LD.

## Use cases (основные)

| UC | Actor | Flow |
|----|-------|------|
| UC-CATALOG | Visitor | DF-CATALOG |
| UC-PRODUCT-VIEW | Visitor | DF-PRODUCT |
| UC-ARTICLES | Visitor | DF-ARTICLES |
| UC-CONTACT | Visitor | DF-CONTACT |
| UC-FAQ | Visitor | — |
| UC-ADMIN-AUTH | Admin | DF-AUTH |
| UC-ADMIN-PRODUCTS/CATEGORIES/CONTENT/… | Admin | DF-ADMIN-CRUD |
| UC-SEO | SearchEngine | DF-SEO |

## Out of scope

- Онлайн-оплата, корзина, регистрация пользователей.
- Редактирование статей через админку (только файлы в `content/articles/`).
- Публичный API.

## Constraints

- SQLite `data/marketbw.db`; Docker Nginx + PHP-FPM.
- CSS без сборки; `PUBLIC_SITE_URL` по умолчанию `https://marketbw.ru`.
- Поддержка `BASE_PATH` для подкаталога.

## Assumptions

- Один admin; трафик умеренный; SQLite достаточен.
