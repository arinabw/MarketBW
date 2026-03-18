# MarketBW

Сайт украшений из бисера ручной работы.

## Стек

- **Vue 3** + TypeScript + Vite
- **Tailwind CSS** с кастомной палитрой (primary/accent/surface)
- **Lucide** иконки
- **Pinia** для состояния
- **Docker** + nginx для деплоя

## Структура

```
src/
├── views/          # Страницы (Home, Catalog, Product, Contact, FAQ)
├── components/
│   ├── ui/         # AppButton, AppCard, AppCardHeader, AppCardContent, AppCardTitle
│   └── layout/     # AppHeader, AppFooter
├── router/         # Vue Router
├── stores/         # Pinia (useMenuStore)
├── lib/            # data.ts, env.ts, utils.ts
└── styles/         # main.css (Tailwind + кастомные классы)
```

## Разработка

```bash
npm install
npm run dev
```

## Деплой (Docker)

```bash
cd docker
chmod +x deploy.sh
./deploy.sh install   # первый запуск
./deploy.sh update    # обновление
./deploy.sh logs      # логи
./deploy.sh status    # статус
```

## Конфигурация

Контакты и параметры сайта — `src/lib/env.ts`.
