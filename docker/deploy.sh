#!/bin/bash

# Скрипт установки и обновления сайта MarketBW
# Использование: ./deploy.sh [install|update|restart|stop|logs]

set -e

# Цвета для вывода
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Функция для вывода сообщений
log_info() {
    echo -e "${GREEN}[INFO]${NC} $1"
}

log_warn() {
    echo -e "${YELLOW}[WARN]${NC} $1"
}

log_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Проверка наличия Docker
check_docker() {
    if ! command -v docker &> /dev/null; then
        log_error "Docker не установлен. Установите Docker перед запуском скрипта."
        exit 1
    fi

    if ! command -v docker-compose &> /dev/null; then
        log_error "Docker Compose не установлен. Установите Docker Compose перед запуском скрипта."
        exit 1
    fi

    log_info "Docker и Docker Compose установлены"
}

# Установка сайта
install() {
    log_info "Начинаю установку сайта MarketBW..."

    # Проверка наличия необходимых файлов
    if [ ! -f "../package.json" ]; then
        log_error "Файл package.json не найден. Убедитесь, что вы находитесь в папке docker и проект настроен правильно."
        exit 1
    fi

    # Создание .env файла, если его нет
    if [ ! -f "../.env" ]; then
        log_warn "Файл .env не найден. Создаю базовый .env файл..."
        cat > ../.env << EOF
NODE_ENV=production
VITE_SITE_NAME=MarketBW
VITE_SITE_URL=https://your-domain.com
EOF
    fi

    # Сборка Docker образа (включая установку зависимостей и сборку)
    log_info "Сборка Docker образа..."
    docker-compose -p marketbw-stack build

    # Запуск контейнера
    log_info "Запуск контейнера..."
    docker-compose -p marketbw-stack up -d

    log_info "✅ Сайт успешно установлен и запущен!"
    log_info "Сайт доступен по адресу: http://localhost:3000"
}

# Обновление сайта
update() {
    log_info "Начинаю обновление сайта MarketBW..."

    # Остановка контейнера
    log_info "Остановка контейнера..."
    docker-compose -p marketbw-stack down

    # Получение последних изменений (если используется git)
    if [ -d "../.git" ]; then
        log_info "Получение последних изменений из git..."
        cd ..
        git pull origin main || git pull origin master
        cd docker
    fi

    # Пересборка Docker образа (включая установку зависимостей и сборку)
    log_info "Пересборка Docker образа..."
    docker-compose -p marketbw-stack build

    # Запуск контейнера
    log_info "Запуск контейнера..."
    docker-compose -p marketbw-stack up -d

    log_info "✅ Сайт успешно обновлен и запущен!"
}

# Перезапуск сайта
restart() {
    log_info "Перезапуск сайта..."
    docker-compose -p marketbw-stack restart
    log_info "✅ Сайт успешно перезапущен!"
}

# Остановка сайта
stop() {
    log_info "Остановка сайта..."
    docker-compose -p marketbw-stack down
    log_info "✅ Сайт успешно остановлен!"
}

# Просмотр логов
logs() {
    log_info "Просмотр логов (Ctrl+C для выхода)..."
    docker-compose -p marketbw-stack logs -f
}

# Проверка статуса
status() {
    log_info "Статус контейнеров:"
    docker-compose -p marketbw-stack ps
}

# Очистка (удаление контейнеров и образов)
clean() {
    log_warn "Это действие удалит все контейнеры и образы. Продолжить? (y/n)"
    read -r response
    if [[ "$response" =~ ^([yY][eE][sS]|[yY])$ ]]; then
        log_info "Остановка и удаление контейнеров..."
        docker-compose -p marketbw-stack down -v --rmi all
        log_info "✅ Очистка завершена!"
    else
        log_info "Очистка отменена."
    fi
}

# Главное меню
main() {
    check_docker

    case "${1:-}" in
        install)
            install
            ;;
        update)
            update
            ;;
        restart)
            restart
            ;;
        stop)
            stop
            ;;
        logs)
            logs
            ;;
        status)
            status
            ;;
        clean)
            clean
            ;;
        *)
            echo "Использование: $0 {install|update|restart|stop|logs|status|clean}"
            echo ""
            echo "Команды:"
            echo "  install  - Первичная установка сайта"
            echo "  update   - Обновление сайта"
            echo "  restart  - Перезапуск сайта"
            echo "  stop     - Остановка сайта"
            echo "  logs     - Просмотр логов"
            echo "  status   - Проверка статуса"
            echo "  clean    - Полная очистка (удаление контейнеров и образов)"
            exit 1
            ;;
    esac
}

main "$@"