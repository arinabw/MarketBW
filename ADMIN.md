# Админ-панель

## Описание
Модуль администрирования каталога товаров с доступом по адресу `/admin` с паролем. Запускается в общем контейнере с основным сайтом.

## Технологии
- **База данных**: SQLite
- **Фронтенд**: Vue 3 + TypeScript + Vite
- **Состояние**: Pinia
- **API**: FastAPI (Python) + uvicorn
- **Контейнеризация**: Docker — **один** контейнер (сборка фронта в Node, рантайм Python + раздача `dist`)

## Структура проекта

```
src/
├── api/
│   └── admin.ts          # API клиент для админ-панели
├── components/
│   └── admin/
│       ├── AdminLogin.vue     # Форма входа
│       ├── AdminDashboard.vue # Главная панель
│       ├── AdminCategories.vue # Управление категориями
│       └── AdminProducts.vue   # Управление товарами
├── views/
│   └── AdminView.vue          # Главный компонент админ-панели
├── stores/
│   └── useAdminStore.ts       # Pinia store для админ-данных
└── lib/
    └── catalog-types.ts      # Типы каталога (фронт)

backend/
└── app/
    ├── main.py               # FastAPI, маршруты /api, раздача SPA
    └── database.py           # SQLite (та же схема, что раньше в Node)

docker/
├── Dockerfile                # Multi-stage: Node → dist, затем Python-рантайм
└── docker-compose.yml        # Traefik, порт контейнера **8000**
```

## Установка и запуск

### 1. Установка зависимостей (локально)
```bash
cd frontend && npm install
cd ../backend && pip install -r requirements.txt
```

### 2. Запуск контейнера
```bash
cd docker
docker-compose up -d --build
```

### 3. Доступ к админ-панели
- URL: `https://marketbw.ru/admin` (или `http://localhost/admin`)
- Логин: `admin`
- Пароль: `admin123`

## Функционал

### Категории
- Просмотр всех категорий
- Создание новых категорий
- Редактирование существующих категорий
- Удаление категорий

### Товары
- Просмотр всех товаров
- Создание новых товаров
- Редактирование существующих товаров
- Удаление товаров
- Добавление/удаление изображений
- Добавление/удаление материалов

### Аутентификация
- Защита роутов
- Вход с логином и паролем
- Выход из системы

## База данных

### Структура таблиц
- `categories` - категории товаров
- `products` - товары
- `users` - пользователи админ-панели

### Расположение БД
Файл базы данных хранится в папке `data/` в корне проекта.
При использовании Docker, папка `data` монтируется в общий контейнер.

### Инициализация
При первом запуске создается админ-пользователь:
- Логин: `admin`
- Пароль: `admin123`

## API Endpoints

### Категории
- `GET /api/categories` - Получить все категории
- `POST /api/categories` - Создать категорию
- `PUT /api/categories/:id` - Обновить категорию
- `DELETE /api/categories/:id` - Удалить категорию

### Товары
- `GET /api/products` - Получить все товары
- `POST /api/products` - Создать товар
- `PUT /api/products/:id` - Обновить товар
- `DELETE /api/products/:id` - Удалить товар

### Аутентификация
- `POST /api/login` - Войти в систему
- `POST /api/logout` - Выйти из системы

### Контент сайта (публично)
- `GET /api/reviews` — все отзывы; `GET /api/reviews?product_id=<id>` — отзывы по товару
- `GET /api/faqs` — вопросы и ответы для страницы FAQ

## Деплой

### Docker
```bash
# В директории docker
docker-compose up -d --build
```

### Логи
```bash
docker-compose logs -f marketbw
```

### Остановка
```bash
docker-compose down
```

### Обновление
```bash
docker-compose pull
docker-compose up -d --build
```

## Поддержка

При возникновении проблем:
1. Проверьте логи контейнера: `docker-compose logs marketbw`
2. Проверьте, что папка `data` существует и имеет правильные права доступа
3. Проверьте, что контейнер запущен: `docker-compose ps`
4. Проверьте, что Traefik указывает на порт **8000** контейнера (`loadbalancer.server.port`)

## Архитектура

### Один контейнер
- **uvicorn** поднимает FastAPI на порту **8000**
- Маршруты **`/api/*`** — REST API и SQLite
- Статика Vue (**`dist`**) и SPA fallback (**`/admin`**) отдаются тем же процессом

### Маршрутизация
- `/` - основной сайт
- `/admin` - админ-панель
- `/api/*` - API запросы

### Безопасность
- Пароли хранятся в виде простого хеша (для демонстрации)
- В реальном проекте рекомендуется использовать bcrypt или Argon2
- Рекомендуется добавить защиту от CSRF
- Рекомендуется добавить rate limiting
