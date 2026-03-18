# План миграции с Next.js на Vite + Vue 3

## Обзор проекта

**Текущий стек:** Next.js 15.1.0 + React 19.0.0 + TypeScript + Tailwind CSS
**Целевой стек:** Vite + Vue 3 + TypeScript + Tailwind CSS

## Анализ текущего проекта

### Структура Next.js проекта
```
MarketBW/
├── app/
│   ├── globals.css          # Глобальные стили
│   ├── layout.tsx           # Корневой layout с шрифтами
│   └── page.tsx             # Главная страница
├── components/
│   ├── layout/
│   │   ├── header.tsx       # Шапка с навигацией
│   │   └── footer.tsx       # Подвал с контактами
│   └── ui/
│       ├── button.tsx       # Компонент кнопки (CVA)
│       └── card.tsx         # Компонент карточки
├── lib/
│   ├── data.ts              # Моковые данные (продукты, категории, отзывы)
│   ├── env.ts               # Переменные окружения
│   └── utils.ts             # Утилиты (cn, formatPrice, slugify)
├── public/images/           # Статические изображения
└── docker/                  # Docker конфигурации
```

### Ключевые компоненты и их функциональность

#### 1. Главная страница (app/page.tsx)
- Hero секция с градиентным фоном
- Секция категорий (4 категории)
- Секция популярных продуктов
- Секция отзывов клиентов
- CTA секция для заказа индивидуальных изделий

#### 2. Header (components/layout/header.tsx)
- Логотип с градиентом
- Десктопная навигация
- Контактная информация (телефон, email)
- Мобильное меню (бургер)
- Sticky позиционирование с blur эффектом

#### 3. Footer (components/layout/footer.tsx)
- Информация о компании
- Навигация
- Контакты
- Социальные сети (Instagram, Telegram, VK)
- Копирайт

#### 4. UI компоненты
- **Button**: Использует CVA (class-variance-authority) для вариантов
- **Card**: Компонуемая карточка с подкомпонентами

#### 5. Утилиты
- `cn()`: Объединение классов с clsx + tailwind-merge
- `formatPrice()`: Форматирование цены в рублях
- `slugify()`: Создание slug из текста
- `truncateText()`: Обрезка текста
- `generateId()`: Генерация уникального ID
- `debounce()`: Дебаунсинг функций

## Целевая архитектура Vite + Vue 3

### Структура проекта
```
MarketBW/
├── src/
│   ├── assets/
│   │   └── images/          # Изображения
│   ├── components/
│   │   ├── layout/
│   │   │   ├── AppHeader.vue
│   │   │   └── AppFooter.vue
│   │   └── ui/
│   │       ├── AppButton.vue
│   │       └── AppCard.vue
│   ├── composables/
│   │   └── useNavigation.ts # Композабл для навигации
│   ├── lib/
│   │   ├── data.ts          # Моковые данные (без изменений)
│   │   ├── env.ts           # Переменные окружения (адаптация)
│   │   └── utils.ts         # Утилиты (без изменений)
│   ├── router/
│   │   └── index.ts         # Vue Router конфигурация
│   ├── stores/
│   │   └── useMenuStore.ts  # Pinia store для мобильного меню
│   ├── styles/
│   │   └── main.css         # Глобальные стили
│   ├── views/
│   │   ├── HomeView.vue     # Главная страница
│   │   ├── CatalogView.vue  # Каталог
│   │   ├── ProductView.vue  # Страница продукта
│   │   ├── ContactView.vue  # Контакты
│   │   └── FAQView.vue      # FAQ
│   ├── App.vue              # Корневой компонент
│   └── main.ts              # Точка входа
├── public/                  # Статические файлы
├── index.html               # HTML шаблон
├── vite.config.ts           # Vite конфигурация
├── tsconfig.json            # TypeScript конфигурация
├── tailwind.config.js       # Tailwind конфигурация
└── package.json             # Зависимости
```

## Необходимые библиотеки для Vue

### Основные зависимости
```json
{
  "dependencies": {
    "vue": "^3.4.0",
    "vue-router": "^4.2.0",
    "pinia": "^2.1.0",
    "clsx": "^2.1.1",
    "tailwind-merge": "^2.5.0",
    "lucide-vue-next": "^0.500.0"
  }
}
```

### Dev зависимости
```json
{
  "devDependencies": {
    "@vitejs/plugin-vue": "^5.0.0",
    "vite": "^5.0.0",
    "typescript": "^5.3.0",
    "vue-tsc": "^1.8.0",
    "tailwindcss": "^3.4.0",
    "postcss": "^8.4.0",
    "autoprefixer": "^10.4.0",
    "@types/node": "^20.10.0"
  }
}
```

## План миграции компонентов

### Этап 1: Базовая инфраструктура

#### 1.1 Инициализация Vite проекта
- Создать новый Vite проект с Vue 3 + TypeScript
- Настроить Vite конфигурацию
- Настроить TypeScript для Vue

#### 1.2 Настройка Tailwind CSS
- Перенести tailwind.config.js (адаптировать для Vue)
- Перенести postcss.config.js (без изменений)
- Перенести globals.css в src/styles/main.css

#### 1.3 Настройка Vue Router
- Создать router/index.ts
- Определить маршруты:
  - `/` - HomeView
  - `/catalog` - CatalogView
  - `/catalog?category=:id` - CatalogView с фильтром
  - `/product/:id` - ProductView
  - `/contact` - ContactView
  - `/faq` - FAQView

#### 1.4 Настройка Pinia
- Создать stores/useMenuStore.ts для управления мобильным меню
- Создать stores/useCartStore.ts (если будет корзина)

### Этап 2: Миграция UI компонентов

#### 2.1 AppButton.vue
**Изменения:**
- Заменить React forwardRef на Vue defineProps
- Заменить CVA на Vue class bindings или vue-cva
- Заменить Slot из Radix UI на Vue slots
- Использовать `defineEmits` для событий

**Ключевые моменты:**
```vue
<script setup lang="ts">
import { cva, type VariantProps } from 'class-variance-authority'
import { cn } from '@/lib/utils'

const buttonVariants = cva(/* ... */)

interface Props extends VariantProps<typeof buttonVariants> {
  asChild?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  asChild: false
})
</script>
```

#### 2.2 AppCard.vue
**Изменения:**
- Заменить React forwardRef на Vue defineProps
- Создать подкомпоненты: CardHeader, CardTitle, CardDescription, CardContent, CardFooter
- Использовать Vue slots для компонуемости

### Этап 3: Миграция Layout компонентов

#### 3.1 AppHeader.vue
**Изменения:**
- Заменить `useState` на Pinia store для мобильного меню
- Заменить `next/link` на `router-link` из Vue Router
- Заменить иконки Lucide React на lucide-vue-next
- Использовать `defineProps` и `defineEmits`

**Ключевые моменты:**
```vue
<script setup lang="ts">
import { useMenuStore } from '@/stores/useMenuStore'
import { RouterLink } from 'vue-router'
import { Menu, X, Phone, Mail } from 'lucide-vue-next'

const menuStore = useMenuStore()
</script>
```

#### 3.2 AppFooter.vue
**Изменения:**
- Заменить `next/link` на `router-link`
- Заменить иконки Lucide React на lucide-vue-next
- Использовать `computed` для динамических данных

### Этап 4: Миграция Views

#### 4.1 HomeView.vue
**Изменения:**
- Перенести всю логику из app/page.tsx
- Заменить `next/link` на `router-link`
- Заменить иконки Lucide React на lucide-vue-next
- Использовать `ref` и `computed` вместо хуков React
- Заменить JSX на Vue template

**Ключевые моменты:**
```vue
<script setup lang="ts">
import { ref, computed } from 'vue'
import { RouterLink } from 'vue-router'
import { ArrowRight, Star, Heart } from 'lucide-vue-next'
import { getFeaturedProducts, categories, reviews } from '@/lib/data'
import { formatPrice } from '@/lib/utils'

const featuredProducts = computed(() => getFeaturedProducts())
const featuredReviews = computed(() => reviews.slice(0, 3))
</script>
```

#### 4.2 CatalogView.vue
**Новый компонент:**
- Отображение всех продуктов
- Фильтрация по категории
- Поиск (опционально)

#### 4.3 ProductView.vue
**Новый компонент:**
- Детальная страница продукта
- Галерея изображений
- Информация о продукте
- Кнопка заказа

#### 4.4 ContactView.vue
**Новый компонент:**
- Форма обратной связи
- Контактная информация

#### 4.5 FAQView.vue
**Новый компонент:**
- Аккордеон с вопросами и ответами
- Фильтрация по категориям

### Этап 5: Миграция утилит и данных

#### 5.1 lib/utils.ts
**Без изменений** - все утилиты совместимы с Vue

#### 5.2 lib/data.ts
**Без изменений** - моковые данные не зависят от фреймворка

#### 5.3 lib/env.ts
**Адаптация:**
- Заменить `process.env.NEXT_PUBLIC_*` на `import.meta.env.VITE_*`
- Обновить имена переменных окружения

```typescript
export const env = {
  siteName: import.meta.env.VITE_SITE_NAME || 'MarketBW',
  siteUrl: import.meta.env.VITE_SITE_URL || 'https://your-domain.com',
  contactEmail: import.meta.env.VITE_CONTACT_EMAIL || 'your-email@example.com',
  contactPhone: import.meta.env.VITE_CONTACT_PHONE || '+7 (999) 123-45-67',
  instagram: import.meta.env.VITE_INSTAGRAM || '#',
  telegram: import.meta.env.VITE_TELEGRAM || '#',
  vk: import.meta.env.VITE_VK || '#',
}
```

### Этап 6: Настройка шрифтов

#### 6.1 Вариант 1: Google Fonts через CSS
- Оставить импорт в main.css
- Настроить CSS переменные для шрифтов

#### 6.2 Вариант 2: Vue Google Fonts
- Установить `vue3-google-fonts`
- Настроить в main.ts

### Этап 7: Обновление конфигураций

#### 7.1 Dockerfile
**Изменения:**
- Заменить `npm run build` на `npm run build` (команда та же)
- Обновить команду запуска: `npm run preview` вместо `npm start`

```dockerfile
# Базовый образ Node.js
FROM node:20-alpine AS base

# Установка зависимостей для сборки
FROM base AS deps
RUN apk add --no-cache libc6-compat
WORKDIR /app

# Копирование файлов зависимостей
COPY package.json ./
RUN npm install

# Сборка приложения
FROM base AS builder
WORKDIR /app
COPY --from=deps /app/node_modules ./node_modules
COPY . .

# Установка переменных окружения для сборки
ENV NODE_ENV=production

# Сборка Vite приложения
RUN npm run build

# Финальный образ для запуска
FROM base AS runner
WORKDIR /app

ENV NODE_ENV=production

COPY --from=builder /app/dist ./dist
COPY --from=builder /app/node_modules/.vite ./node_modules/.vite
COPY --from=builder /app/package.json ./package.json

EXPOSE 4173

CMD ["npm", "run", "preview", "--", "--host", "0.0.0.0", "--port", "4173"]
```

#### 7.2 package.json
**Изменения:**
- Обновить скрипты:
  - `dev`: `vite`
  - `build`: `vue-tsc && vite build`
  - `preview`: `vite preview`
  - `lint`: `eslint . --ext .vue,.js,.jsx,.cjs,.mjs,.ts,.tsx,.cts,.mts --fix --ignore-path .gitignore`

#### 7.3 tsconfig.json
**Изменения:**
- Обновить для Vue:
  - Добавить `"jsx": "preserve"` (если нужно)
  - Обновить `include` для `.vue` файлов
  - Добавить Vue plugin

```json
{
  "compilerOptions": {
    "target": "ES2020",
    "useDefineForClassFields": true,
    "module": "ESNext",
    "lib": ["ES2020", "DOM", "DOM.Iterable"],
    "skipLibCheck": true,
    "moduleResolution": "bundler",
    "allowImportingTsExtensions": true,
    "resolveJsonModule": true,
    "isolatedModules": true,
    "noEmit": true,
    "jsx": "preserve",
    "strict": true,
    "noUnusedLocals": true,
    "noUnusedParameters": true,
    "noFallthroughCasesInSwitch": true,
    "baseUrl": ".",
    "paths": {
      "@/*": ["./src/*"]
    }
  },
  "include": ["src/**/*.ts", "src/**/*.d.ts", "src/**/*.tsx", "src/**/*.vue"],
  "references": [{ "path": "./tsconfig.node.json" }]
}
```

#### 7.4 vite.config.ts
**Новый файл:**
```typescript
import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import path from 'path'

export default defineConfig({
  plugins: [vue()],
  resolve: {
    alias: {
      '@': path.resolve(__dirname, './src'),
    },
  },
  server: {
    port: 3000,
    host: true,
  },
  build: {
    outDir: 'dist',
    sourcemap: true,
  },
})
```

#### 7.5 index.html
**Новый файл:**
```html
<!DOCTYPE html>
<html lang="ru">
  <head>
    <meta charset="UTF-8" />
    <link rel="icon" type="image/svg+xml" href="/vite.svg" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>MarketBW - Украшения из бисера ручной работы</title>
    <meta name="description" content="Уникальные украшения из бисера ручной работы. Браслеты, колье, серьги и броши, созданные с любовью и вниманием к деталям." />
  </head>
  <body>
    <div id="app"></div>
    <script type="module" src="/src/main.ts"></script>
  </body>
</html>
```

### Этап 8: Перенос статики

#### 8.1 Изображения
- Перенести все изображения из `public/images/` в `public/images/` (без изменений)
- Обновить пути в компонентах (если нужно)

### Этап 9: Тестирование

#### 9.1 Локальное тестирование
- Запустить `npm run dev`
- Проверить все страницы
- Проверить навигацию
- Проверить адаптивность
- Проверить стили

#### 9.2 Сборка
- Запустить `npm run build`
- Проверить отсутствие ошибок
- Запустить `npm run preview`
- Проверить собранное приложение

#### 9.3 Docker тестирование
- Собрать Docker образ
- Запустить контейнер
- Проверить работу приложения

## Ключевые различия между React и Vue

### 1. Компоненты
**React:**
```tsx
function Component({ prop }) {
  const [state, setState] = useState(initial)
  return <div>{state}</div>
}
```

**Vue:**
```vue
<script setup lang="ts">
import { ref } from 'vue'

const props = defineProps<{ prop: string }>()
const state = ref(initial)
</script>

<template>
  <div>{{ state }}</div>
</template>
```

### 2. Состояние
**React:** `useState`, `useReducer`
**Vue:** `ref`, `reactive`, `computed`

### 3. Эффекты
**React:** `useEffect`
**Vue:** `onMounted`, `onUpdated`, `onUnmounted`, `watch`, `watchEffect`

### 4. Роутинг
**React:** `next/link`, `useRouter`
**Vue:** `RouterLink`, `useRouter` из Vue Router

### 5. Слоты
**React:** `children` prop
**Vue:** `<slot>`, именованные слоты

### 6. Директивы
**React:** Нет директив
**Vue:** `v-if`, `v-for`, `v-bind`, `v-on`, `v-model`

## Порядок выполнения миграции

### Фаза 1: Подготовка (1-2 часа)
1. Создать новый Vite проект
2. Настроить базовую конфигурацию
3. Настроить Tailwind CSS
4. Настроить Vue Router и Pinia

### Фаза 2: UI компоненты (2-3 часа)
1. Мигрировать AppButton.vue
2. Мигрировать AppCard.vue
3. Протестировать UI компоненты

### Фаза 3: Layout компоненты (2-3 часа)
1. Мигрировать AppHeader.vue
2. Мигрировать AppFooter.vue
3. Протестировать layout

### Фаза 4: Views (4-6 часов)
1. Мигрировать HomeView.vue
2. Создать CatalogView.vue
3. Создать ProductView.vue
4. Создать ContactView.vue
5. Создать FAQView.vue
6. Протестировать все страницы

### Фаза 5: Интеграция (1-2 часа)
1. Настроить App.vue
2. Настроить main.ts
3. Интегрировать все компоненты

### Фаза 6: Конфигурации (1-2 часа)
1. Обновить Dockerfile
2. Обновить package.json
3. Обновить tsconfig.json
4. Создать vite.config.ts
5. Создать index.html

### Фаза 7: Тестирование (2-3 часа)
1. Локальное тестирование
2. Сборка и preview
3. Docker тестирование
4. Исправление багов

### Фаза 8: Финализация (1 час)
1. Обновить документацию
2. Очистить старые файлы
3. Финальное тестирование

## Потенциальные проблемы и решения

### 1. Иконки Lucide
**Проблема:** Lucide React и lucide-vue-next имеют разные API
**Решение:** Использовать lucide-vue-next, адаптировать импорты

### 2. CVA (class-variance-authority)
**Проблема:** CVA создан для React
**Решение:** CVA работает с Vue, но нужно адаптировать использование

### 3. Шрифты Google
**Проблема:** Next.js оптимизирует шрифты автоматически
**Решение:** Использовать CSS импорт или vue3-google-fonts

### 4. SEO
**Проблема:** Vite - SPA, хуже для SEO чем Next.js SSR
**Решение:** Использовать meta теги в index.html, рассмотреть SSR с Nuxt.js в будущем

### 5. Переменные окружения
**Проблема:** Разный синтаксис для env переменных
**Решение:** Обновить lib/env.ts для использования import.meta.env

### 6. Типизация
**Проблема:** Vue требует специальной типизации для .vue файлов
**Решение:** Создать env.d.ts с декларациями для .vue файлов

## Преимущества миграции на Vite + Vue

1. **Быстрее сборка:** Vite значительно быстрее Next.js для разработки
2. **Проще конфигурация:** Меньше магии и больше прозрачности
3. **Меньше зависимостей:** Убираем Next.js специфичные пакеты
4. **Легче отладка:** Четкое разделение concerns
5. **Лучший DX:** Быстрый HMR, понятная структура

## Недостатки миграции

1. **Потеря SSR:** Vite - SPA, нет серверного рендеринга
2. **SEO:** Хуже SEO чем Next.js
3. **Изучение Vue:** Требуется изучение Vue 3 если нет опыта
4. **Меньше экосистемы:** Меньше готовых решений чем в React/Next.js

## Рекомендации

1. **Сохранить Next.js версию:** Создать ветку с Next.js для отката
2. **Постепенная миграция:** Мигрировать по одному компоненту за раз
3. **Тестирование на каждом этапе:** Не переходить к следующему этапу без тестирования
4. **Документация:** Документировать все изменения и решения
5. **CI/CD:** Обновить CI/CD пайплайны для нового стека

## Заключение

Миграция с Next.js на Vite + Vue 3 - это значительное изменение архитектуры. Основные усилия потребуются для:
- Адаптации компонентов React на Vue
- Настройки Vue Router вместо Next.js routing
- Обновления конфигураций (Docker, build, etc.)
- Тестирования всех функциональностей

Ожидаемое время миграции: 12-18 часов для опытного разработчика.
