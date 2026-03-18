# Инструкция по установке и запуску сайта MarketBW

## 📋 Требования

- **Node.js** версии 18 или выше
- **Docker** и **Docker Compose** (для продакшн-развертывания)
- **Git** (для клонирования репозитория)

## 🚀 Быстрый старт

### 1. Клонирование репозитория

```bash
git clone https://github.com/arinabw/marketbw.git
cd marketbw
```

### 2. Локальная разработка

#### Установка зависимостей
```bash
npm install
```

#### Настройка переменных окружения
```bash
cp .env.example .env
```

Отредактируйте файл `.env` с вашими данными:
```env
NODE_ENV=development
VITE_SITE_NAME=MarketBW
VITE_SITE_URL=http://localhost:3000
VITE_CONTACT_EMAIL=your-email@example.com
VITE_CONTACT_PHONE=+7 (999) 123-45-67
VITE_INSTAGRAM=https://instagram.com/your-profile
VITE_TELEGRAM=https://t.me/your-profile
VITE_VK=https://vk.com/your-profile
```

#### Запуск в режиме разработки
```bash
npm run dev
```

Сайт будет доступен по адресу: `http://localhost:3000`

#### Сборка для продакшн
```bash
npm run build
npm start
```

### 3. Продакшн-развертывание с Docker

#### Переход в папку docker
```bash
cd docker
```

#### Первичная установка
```bash
chmod +x deploy.sh
./deploy.sh install
```

#### Обновление сайта
```bash
./deploy.sh update
```

#### Другие команды управления
```bash
./deploy.sh restart    # Перезапуск контейнера
./deploy.sh stop       # Остановка контейнера
./deploy.sh logs       # Просмотр логов
./deploy.sh status     # Проверка статуса
./deploy.sh clean      # Полная очистка (удаление контейнеров и образов)
```

## 📁 Структура проекта

```
marketbw/
├── src/                   # Исходный код
│   ├── views/            # Страницы Vue
│   │   ├── HomeView.vue  # Главная страница
│   │   ├── CatalogView.vue # Каталог изделий
│   │   ├── ProductView.vue # Детальная страница изделия
│   │   ├── ContactView.vue # Контакты
│   │   └── FAQView.vue   # FAQ
│   ├── components/       # Vue компоненты
│   │   ├── ui/            # Базовые UI компоненты
│   │   └── layout/        # Layout компоненты
│   ├── router/           # Vue Router
│   ├── stores/           # Pinia stores
│   ├── lib/              # Утилиты и данные
│   └── styles/           # Глобальные стили
├── public/               # Статические файлы и изображения
├── plans/                # Планирование проекта
├── docker/               # Docker конфигурация
│   ├── Dockerfile        # Docker конфигурация
│   ├── docker-compose.yml # Docker Compose конфигурация
│   └── deploy.sh         # Скрипт деплоя
├── vite.config.ts       # Конфигурация Vite
└── package.json          # Зависимости проекта
```

## 🖼️ Изображения

### Заглушки
В проекте созданы файлы-заглушки для изображений. Замените их на реальные фотографии:

- **Hero изображение**: `public/images/hero-beadwork.jpg`
- **Категории**: `public/images/categories/*.jpg`
- **Продукты**: `public/images/products/` (см. `public/images/products/README.md`)

### Требования к изображениям
- **Формат**: JPG или PNG
- **Размер**: 800x800px для основных фото, 400x400px для дополнительных
- **Качество**: Высокое, четкие фотографии на светлом фоне
- **Стиль**: Романтичный, нежный, с хорошим освещением

## 🎨 Кастомизация

### Цветовая палитра
Цвета определены в `tailwind.config.js`:
- Основной фон: `#FFF5F5` (Очень светлый розовый)
- Акценты: `#FFE4E1` (Мисти роз), `#E6E6FA` (Лавандовый)
- Текст: `#4A4A4A` (Тёмно-серый)
- Кнопки: `#C9A9A6` (Пыльная роза)

### Шрифты
- **Заголовки**: Playfair Display
- **Основной текст**: Lato

## 🔧 Разработка

### Добавление новых продуктов
Отредактируйте `lib/data.ts` для добавления новых изделий:

```typescript
{
  id: 'unique-id',
  name: 'Название изделия',
  description: 'Описание изделия',
  price: 2500,
  category: 'bracelet' | 'necklace' | 'earrings' | 'brooch',
  images: ['/images/products/image-1.jpg', '/images/products/image-2.jpg'],
  materials: ['Материал 1', 'Материал 2'],
  size: 'Размер',
  technique: 'Техника плетения',
  inStock: true,
  featured: false,
  createdAt: new Date()
}
```

### Добавление новых страниц
1. Создайте папку в `app/`
2. Добавьте файл `page.tsx`
3. Обновите навигацию в `components/layout/header.tsx`

## 🐛 Поиск ошибок

### Проверка типов
```bash
npm run lint
```

### Сборка и проверка
```bash
npm run build
```

## 📝 Лицензия

MIT License

## 🤞 Поддержка

При возникновении проблем:
1. Проверьте версию Node.js (`node --version`)
2. Очистите кэш npm (`npm cache clean --force`)
3. Удалите `node_modules` и переустановите зависимости
4. Проверьте правильность переменных окружения

---

**Примечание**: TypeScript ошибки в компонентах связаны с отсутствием установленных зависимостей. После выполнения `npm install` все ошибки должны исчезнуть.