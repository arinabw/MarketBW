# Правила проекта MarketBW

## Общая информация

**Проект:** MarketBW - интернет-магазин украшений из бисера ручной работы
**Стек технологий:** Vue 3, Vite, TypeScript, Tailwind CSS, Pinia, Vue Router
**Язык проекта:** Русский (все сообщения, комментарии и документация на русском)
**Базовая директория:** `c:/Users/arish/OneDrive/Документы/MarketBW`

## Структура компонентов UI

### Правило 1: Отдельные файлы для компонентов
Каждый компонент UI находится в отдельном файле в `src/components/ui/`:

- `AppCard.vue` - основной компонент карточки
- `AppCardContent.vue` - содержимое карточки
- `AppCardHeader.vue` - заголовок карточки
- `AppCardTitle.vue` - заголовок (h3)
- `AppCardFooter.vue` - футер карточки
- `AppButton.vue` - кнопка

### Правило 2: Правильные импорты компонентов
**НЕПРАВИЛЬНО:**
```typescript
import AppCard, { AppCardContent, AppCardHeader, AppCardTitle } from '@/components/ui/AppCard.vue'
```

**ПРАВИЛЬНО:**
```typescript
import AppCard from '@/components/ui/AppCard.vue'
import AppCardContent from '@/components/ui/AppCardContent.vue'
import AppCardHeader from '@/components/ui/AppCardHeader.vue'
import AppCardTitle from '@/components/ui/AppCardTitle.vue'
```

**ВАЖНО:** Никогда не импортируйте подкомпоненты из `AppCard.vue` - они не экспортируются оттуда!

## Docker-конфигурация

### Правило 3: Обновление пакетов Alpine
В [`docker/Dockerfile`](docker/Dockerfile) всегда должно быть обновление пакетов Alpine:

```dockerfile
# Базовый образ Node.js
FROM node:25-alpine AS base

# Обновление пакетов Alpine
RUN apk update && apk upgrade --no-cache
```

Это обеспечивает безопасность и актуальность зависимостей.

## Структура проекта

```
src/
├── components/
│   ├── layout/
│   │   ├── AppHeader.vue
│   │   └── AppFooter.vue
│   └── ui/
│       ├── AppButton.vue
│       ├── AppCard.vue
│       ├── AppCardContent.vue
│       ├── AppCardHeader.vue
│       ├── AppCardTitle.vue
│       └── AppCardFooter.vue
├── views/
│   ├── HomeView.vue
│   ├── CatalogView.vue
│   ├── ProductView.vue
│   ├── ContactView.vue
│   └── FAQView.vue
├── router/
│   └── index.ts
├── stores/
│   └── useMenuStore.ts
├── lib/
│   ├── data.ts
│   ├── env.ts
│   └── utils.ts
└── styles/
    └── main.css
```

## Правила работы с Git

### Правило 4: Формат коммитов
Используйте понятные сообщения на русском языке:
- `fix: описание исправления`
- `feat: описание новой функции`
- `chore: описание технических изменений`
- `docs: описание изменений в документации`

### Правило 5: Проверка перед коммитом
Перед коммитом всегда:
1. Проверьте, что все импорты компонентов UI корректны
2. Убедитесь, что Dockerfile содержит обновление пакетов Alpine
3. Протестируйте сборку проекта (`npm run build`)

## Иконки и библиотеки

### Правило 6: Иконки из lucide-vue-next
Используйте иконки из библиотеки `lucide-vue-next`:

```typescript
import { Phone, Mail, MapPin, Instagram, Send, MessageCircle } from 'lucide-vue-next'
```

Для социальных сетей:
- Instagram: `Instagram`
- Telegram: `Send` (вместо `Telegram`)
- VK: `MessageCircle` (вместо `Vk`)

## Стиль кода

### Правило 7: TypeScript
Все компоненты должны использовать TypeScript с `<script setup lang="ts">`

### Правило 8: Tailwind CSS
Используйте utility-классы Tailwind CSS для стилизации. Кастомные классы определены в `tailwind.config.js`

### Правило 9: Компоненты Vue
- Используйте Composition API с `<script setup>`
- Определяйте интерфейсы для props
- Используйте `computed` для вычисляемых свойств
- Используйте `ref` для реактивных данных

## Переменные окружения

### Правило 10: Конфигурация env
Переменные окружения определены в `src/lib/env.ts`:
- `env.contactPhone`
- `env.contactEmail`
- `env.instagram`
- `env.telegram`
- `env.vk`

## Маршрутизация

### Правило 11: Vue Router
Маршруты определены в `src/router/index.ts`:
- `/` - HomeView
- `/catalog` - CatalogView
- `/product/:id` - ProductView
- `/contact` - ContactView
- `/faq` - FAQView

## Данные

### Правило 12: Данные проекта
Данные о продуктах, категориях и отзывах находятся в `src/lib/data.ts`:
- `products` - массив продуктов
- `categories` - массив категорий
- `reviews` - массив отзывов
- `faqs` - массив FAQ

## Утилиты

### Правило 13: Вспомогательные функции
Утилиты находятся в `src/lib/utils.ts`:
- `cn()` - объединение классов с clsx и tailwind-merge
- `formatPrice()` - форматирование цены

## Частые ошибки

### Ошибка 1: Некорректный импорт компонентов AppCard
**Симптом:** Ошибка сборки `"AppCardContent" is not exported by "src/components/ui/AppCard.vue"`
**Решение:** Импортируйте каждый компонент из своего файла (см. Правило 2)

### Ошибка 2: Отсутствие обновления пакетов Alpine
**Симптом:** Устаревшие пакеты безопасности в Docker-образе
**Решение:** Добавьте `RUN apk update && apk upgrade --no-cache` в Dockerfile (см. Правило 3)

## Проверка качества

Перед завершением любой задачи:
1. ✅ Все импорты компонентов UI корректны
2. ✅ Dockerfile содержит обновление пакетов Alpine
3. ✅ Код соответствует TypeScript
4. ✅ Используются правильные иконки из lucide-vue-next
5. ✅ Сообщения на русском языке
6. ✅ Формат коммита соответствует стандартам

---

**Дата создания:** 2026-03-18
**Последнее обновление:** 2026-03-18
