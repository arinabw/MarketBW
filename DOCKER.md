# Запуск сайта MarketBW в Docker

Этот документ описывает, как запустить сайт MarketBW в Docker контейнере.

## Требования

- Docker (версия 20.10 или выше)
- Docker Compose (версия 2.0 или выше)

## Быстрый старт

### 1. Сборка и запуск

Перейдите в директорию `docker` и выполните:

```bash
cd docker
docker-compose up -d --build
```

Эта команда:
- Соберет Docker образ
- Запустит контейнер в фоновом режиме
- Сайт будет доступен по адресу `http://localhost:3000`

### 2. Просмотр логов

```bash
docker-compose logs -f
```

### 3. Остановка контейнера

```bash
docker-compose down
```

## Использование скрипта развертывания

Для удобства предоставлен скрипт `deploy.sh` с различными командами:

```bash
cd docker
chmod +x deploy.sh
./deploy.sh install    # Первичная установка
./deploy.sh update     # Обновление сайта
./deploy.sh restart    # Перезапуск
./deploy.sh stop       # Остановка
./deploy.sh logs       # Просмотр логов
./deploy.sh status     # Проверка статуса
./deploy.sh clean      # Полная очистка
```

## Настройка переменных окружения

Переменные окружения можно настроить двумя способами:

### Способ 1: Через docker-compose.yml

Отредактируйте файл `docker/docker-compose.yml`:

```yaml
environment:
  - NODE_ENV=production
  - NEXT_PUBLIC_SITE_NAME=MarketBW
  - NEXT_PUBLIC_SITE_URL=https://your-domain.com
  - NEXT_PUBLIC_CONTACT_EMAIL=your-email@example.com
  - NEXT_PUBLIC_CONTACT_PHONE=+7 (999) 123-45-67
  - NEXT_PUBLIC_INSTAGRAM=https://instagram.com/your-profile
  - NEXT_PUBLIC_TELEGRAM=https://t.me/your-profile
  - NEXT_PUBLIC_VK=https://vk.com/your-profile
```

### Способ 2: Через .env файл

Создайте файл `.env` в корне проекта:

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

## Структура Docker контейнера

Сайт использует многоступенчатую сборку (multi-stage build):

1. **deps** - Установка зависимостей
2. **builder** - Сборка Next.js приложения
3. **runner** - Финальный образ для запуска

Преимущества:
- Минимальный размер финального образа
- Безопасность (запуск от имени пользователя nextjs)
- Оптимизация для production

## Порты

По умолчанию сайт работает на порту `3000`. Вы можете изменить порт в `docker-compose.yml`:

```yaml
ports:
  - "8080:3000"  # Сайт будет доступен на порту 8080
```

## Обновление сайта

Для обновления сайта:

1. Получите последние изменения из git (если используете)
2. Пересоберите и перезапустите контейнер:

```bash
cd docker
docker-compose down
docker-compose up -d --build
```

Или используйте скрипт:

```bash
./deploy.sh update
```

## Решение проблем

### Контейнер не запускается

Проверьте логи:

```bash
docker-compose logs
```

### Ошибки сборки

Убедитесь, что все файлы проекта на месте и Docker имеет доступ к ним.

### Проблемы с портами

Если порт 3000 уже занят, измените его в `docker-compose.yml`.

## Production развертывание

Для production развертывания рекомендуется:

1. Использовать обратный прокси (nginx, traefik)
2. Настроить HTTPS (Let's Encrypt)
3. Использовать volumes для персистентности (если нужно)
4. Настроить мониторинг и логирование

## Полезные команды

```bash
# Просмотр запущенных контейнеров
docker ps

# Вход в контейнер
docker-compose exec web sh

# Перезапуск контейнера
docker-compose restart

# Удаление контейнеров и образов
docker-compose down -v --rmi all
```

## Поддержка

При возникновении проблем проверьте:
- Версии Docker и Docker Compose
- Доступность портов
- Логи контейнера
- Правильность переменных окружения
