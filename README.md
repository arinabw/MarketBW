# MarketBW - Сайт украшений из бисера

Современный сайт для продажи и демонстрации украшений из бисера ручной работы, созданный с использованием Next.js 14 и современных технологий.

## 🌟 Особенности

- **Современный дизайн** в романтичном стиле с пастельными тонами
- **Адаптивная вёрстка** для всех устройств
- **Оптимизированная производительность** с Next.js 14
- **SEO-дружественный** с Server-Side Rendering
- **Docker-контейнеризация** для лёгкого развёртывания

## 🛠️ Технологический стек

- **Frontend**: Next.js 14 (App Router), TypeScript
- **Стилизация**: Tailwind CSS с кастомной цветовой палитрой
- **UI компоненты**: shadcn/ui
- **Анимации**: Framer Motion
- **Иконки**: Lucide React
- **Формы**: React Hook Form + Zod
- **Контейнеризация**: Docker + Docker Compose

## 📁 Структура проекта

```
marketbw/
├── app/                    # Страницы Next.js
│   ├── layout.tsx         # Главный layout
│   ├── page.tsx           # Главная страница
│   ├── catalog/           # Каталог изделий
│   ├── product/           # Детальная страница изделия
│   ├── contact/           # Контакты
│   └── faq/               # FAQ
├── components/            # React компоненты
│   ├── ui/                # Базовые UI компоненты
│   ├── layout/            # Layout компоненты
│   ├── catalog/           # Компоненты каталога
│   └── product/           # Компоненты изделий
├── lib/                   # Утилиты и данные
├── public/                # Статические файлы
├── plans/                 # Планирование проекта
└── docker/                # Docker конфигурация
    ├── Dockerfile         # Docker конфигурация
    ├── docker-compose.yml # Docker Compose конфигурация
    └── deploy.sh          # Скрипт деплоя
```

## 🚀 Быстрый старт

### Требования

- Node.js 18+
- Docker и Docker Compose

### Клонирование репозитория

```bash
git clone https://github.com/arinabw/marketbw.git
cd marketbw
```

### Локальная разработка

1. **Установка зависимостей**
   ```bash
   npm install
   ```

2. **Настройка переменных окружения**
   ```bash
   cp .env.example .env
   # Отредактируйте .env файл с вашими данными
   ```

3. **Запуск в режиме разработки**
   ```bash
   npm run dev
   ```

   Сайт будет доступен по адресу `http://localhost:3000`

### Продакшн развёртывание с Docker

1. **Переход в папку docker**
   ```bash
   cd docker
   ```

2. **Первичная установка**
   ```bash
   chmod +x deploy.sh
   ./deploy.sh install
   ```

3. **Обновление сайта**
   ```bash
   ./deploy.sh update
   ```

4. **Другие команды**
   ```bash
   ./deploy.sh restart    # Перезапуск
   ./deploy.sh stop       # Остановка
   ./deploy.sh logs       # Просмотр логов
   ./deploy.sh status     # Проверка статуса
   ./deploy.sh clean      # Полная очистка
   ```

## 🎨 Дизайн-система

### Цветовая палитра

- **Основной фон**: `#FFF5F5` (Очень светлый розовый)
- **Акценты**: `#FFE4E1` (Мисти роз), `#E6E6FA` (Лавандовый)
- **Текст**: `#4A4A4A` (Тёмно-серый)
- **Кнопки**: `#C9A9A6` (Пыльная роза)

### Типографика

- **Заголовки**: Playfair Display (с засечками)
- **Основной текст**: Lato (без засечек)

## 📱 Страницы сайта

1. **Главная страница**
   - Hero секция с призывом к действию
   - Категории изделий
   - Популярные изделия
   - Отзывы клиентов

2. **Каталог**
   - Сетка изделий с фильтрацией
   - Сортировка по цене и популярности
   - Детальная информация о каждом изделии

3. **Страница изделия**
   - Галерея фотографий
   - Подробное описание
   - Характеристики и материалы
   - Форма заказа

4. **Контакты**
   - Форма обратной связи
   - Контактная информация
   - Ссылки на социальные сети

5. **FAQ**
   - Часто задаваемые вопросы
   - Разделение по категориям

## 🔧 Конфигурация

### Переменные окружения

```env
NODE_ENV=production
NEXT_PUBLIC_SITE_NAME=MarketBW
NEXT_PUBLIC_SITE_URL=https://your-domain.com
NEXT_PUBLIC_CONTACT_EMAIL=your-email@example.com
NEXT_PUBLIC_CONTACT_PHONE=+7 (999) 123-45-67
NEXT_PUBLIC_INSTAGRAM=https://instagram.com/your-profile
NEXT_PUBLIC_TELEGRAM=https://t.me/your-profile
NEXT_PUBLIC_VK=https://vk.com/your-profile
```

## 📝 Лицензия

MIT License

## 🤝 Поддержка

Для связи и поддержки:
- Email: `your-email@example.com`
- Telegram: `@your-profile`

---

Создано с ❤️ для MarketBW