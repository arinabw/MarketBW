# Админ-панель

## Описание
Модуль администрирования каталога товаров с доступом по адресу `/admin` с паролем.

> Актуальное описание: **[ADMIN.md](../ADMIN.md)** в корне репозитория.

## Технологии
- **База данных**: SQLite
- **Фронтенд**: Vue 3 + TypeScript + Vite
- **Состояние**: Pinia
- **API**: FastAPI (Python)
- **Docker**: один контейнер (см. `docker/Dockerfile`)

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
    └── catalog-types.ts

backend/app/
├── main.py
└── database.py

docker/
├── Dockerfile
└── docker-compose.yml
```

## Установка и запуск

### 1. Установка зависимостей
```bash
cd frontend && npm install
cd ../backend && pip install -r requirements.txt
```

### 2. Запуск
```bash
# из docker/ — см. README
docker compose up -d --build
```

### 3. Доступ к админ-панели
- URL: `https://<ваш-домен>/admin` или локально через Vite + uvicorn (см. README)
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
При использовании Docker, папка `data` монтируется в контейнер.

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

## Безопасность

- Пароли хранятся в виде простого хеша (для демонстрации)
- В реальном проекте рекомендуется использовать bcrypt или Argon2
- Рекомендуется добавить защиту от CSRF
- Рекомендуется добавить rate limiting

## Документация

Подробная документация архитектуры находится в файле [`Plan.md`](Plan.md).

## Разработка

### Добавление новых компонентов
1. Создайте компонент в `src/components/admin/`
2. Добавьте логику в `src/stores/useAdminStore.ts`
3. Добавьте роут в `src/views/AdminView.vue`

### Добавление новых API эндпоинтов
1. Логика БД — в `backend/app/database.py`
2. Маршруты — в `backend/app/main.py`
3. Клиент админки — в `src/api/admin.ts`

## Тестирование

### Тестирование админ-панели
1. Запустите стек (см. README / `docker/deploy.sh`)
2. Откройте `/admin` на вашем домене или локально
3. Войдите с учетными данными: admin / admin123
4. Проверьте функционал категорий и товаров

### Тестирование API
```bash
# Получить все категории
curl http://localhost:3000/api/categories

# Создать категорию
curl -X POST http://localhost:3000/api/categories \
  -H "Content-Type: application/json" \
  -d '{"name":"Тест","description":"Описание","image":"/images/test.svg"}'

# Получить все товары
curl http://localhost:3000/api/products
```

## Деплой

### Docker
```bash
# В директории docker/admin
docker-compose up -d --build
```

### Логи
```bash
docker-compose logs -f admin-server
```

### Остановка
```bash
docker-compose down
```

## Поддержка

При возникновении проблем:
1. Проверьте логи контейнера: `docker-compose logs admin-server`
2. Проверьте, что папка `data` существует и имеет правильные права доступа
3. Проверьте, что порт 3000 свободен
