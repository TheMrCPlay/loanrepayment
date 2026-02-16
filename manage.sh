#!/bin/bash

# Loan Repayment Docker Management Script
# Вспомогательные команды для управления контейнерами

set -e

COMMAND=${1:-help}
PROJECT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"

# Цвета для вывода
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

echo_info() {
    echo -e "${BLUE}ℹ️  $1${NC}"
}

echo_success() {
    echo -e "${GREEN}✅ $1${NC}"
}

echo_warning() {
    echo -e "${YELLOW}⚠️  $1${NC}"
}

echo_error() {
    echo -e "${RED}❌ $1${NC}"
}

case $COMMAND in
    up)
        echo_info "Запуск контейнеров..."
        cd "$PROJECT_DIR"
        docker-compose up -d
        echo_success "Контейнеры запущены"
        docker-compose ps
        ;;

    down)
        echo_info "Остановка контейнеров..."
        cd "$PROJECT_DIR"
        docker-compose down
        echo_success "Контейнеры остановлены"
        ;;

    restart)
        echo_info "Перезагрузка контейнеров..."
        cd "$PROJECT_DIR"
        docker-compose restart
        echo_success "Контейнеры перезагружены"
        docker-compose ps
        ;;

    logs)
        SERVICE=${2:-php81}
        echo_info "Логи для сервиса: $SERVICE"
        cd "$PROJECT_DIR"
        docker-compose logs -f "$SERVICE"
        ;;

    bash)
        SERVICE=${2:-php81}
        echo_info "Подключение к контейнеру $SERVICE..."
        cd "$PROJECT_DIR"
        docker-compose exec "$SERVICE" bash
        ;;

    shell)
        echo_info "Подключение к PostgreSQL..."
        cd "$PROJECT_DIR"
        docker-compose exec postgres psql -U app -d app
        ;;

    migrate)
        echo_info "Запуск миграций..."
        cd "$PROJECT_DIR"
        docker-compose exec php81 php bin/console doctrine:migrations:migrate
        echo_success "Миграции завершены"
        ;;

    migrate-status)
        echo_info "Статус миграций..."
        cd "$PROJECT_DIR"
        docker-compose exec php81 php bin/console doctrine:migrations:status
        ;;

    import-csv)
        FILE=${2:-}
        if [ -z "$FILE" ]; then
            echo_error "Укажи путь до CSV файла"
            echo "Использование: ./manage.sh import-csv <path_to_file>"
            exit 1
        fi

        if [ ! -f "$FILE" ]; then
            echo_error "Файл не найден: $FILE"
            exit 1
        fi

        echo_info "Импорт из файла: $FILE"
        cd "$PROJECT_DIR"

        # Копируем файл в контейнер
        FILENAME=$(basename "$FILE")
        docker cp "$FILE" loanrepayment-php:/app/"$FILENAME"

        # Запускаем импорт
        docker-compose exec php81 php bin/console import --file=/app/"$FILENAME"
        echo_success "Импорт завершен"
        ;;

    build)
        echo_info "Построение образов..."
        cd "$PROJECT_DIR"
        docker-compose build --no-cache
        echo_success "Образы построены"
        ;;

    rebuild)
        echo_info "Пересоздание контейнеров..."
        cd "$PROJECT_DIR"
        docker-compose down -v
        docker-compose up -d --build
        echo_success "Контейнеры пересозданы"
        docker-compose ps
        ;;

    clean)
        echo_warning "Удаление всех данных (включая БД)..."
        read -p "Ты уверен? (y/n) " -n 1 -r
        echo
        if [[ $REPLY =~ ^[Yy]$ ]]; then
            cd "$PROJECT_DIR"
            docker-compose down -v
            echo_success "Все данные удалены"
        else
            echo_warning "Отмена"
        fi
        ;;

    console)
        COMMAND_NAME=${2:-}
        if [ -z "$COMMAND_NAME" ]; then
            echo_error "Укажи Symfony команду"
            echo "Использование: ./manage.sh console <command>"
            exit 1
        fi

        cd "$PROJECT_DIR"
        shift
        docker-compose exec php81 php bin/console "$@"
        ;;

    test-api)
        echo_info "Запуск тестов API..."
        cd "$PROJECT_DIR"
        ./test_api.sh
        ;;

    status)
        echo_info "Статус контейнеров:"
        cd "$PROJECT_DIR"
        docker-compose ps
        echo ""

        echo_info "Проверка подключения к API..."
        if curl -s -o /dev/null -w "%{http_code}" http://localhost/api/payment/ | grep -q "^[0-9]*$"; then
            echo_success "API доступен"
        else
            echo_error "API недоступен"
        fi
        ;;

    routes)
        echo_info "Показать все маршруты..."
        cd "$PROJECT_DIR"
        docker-compose exec php81 php bin/console debug:router
        ;;

    services)
        echo_info "Показать все сервисы..."
        cd "$PROJECT_DIR"
        docker-compose exec php81 php bin/console debug:container
        ;;

    cache-clear)
        echo_info "Очистка кэша..."
        cd "$PROJECT_DIR"
        docker-compose exec php81 php bin/console cache:clear
        echo_success "Кэш очищен"
        ;;

    help|*)
        cat << EOF
${BLUE}🐳 Loan Repayment Docker Management${NC}

${YELLOW}Использование:${NC}
  ./manage.sh <command> [options]

${YELLOW}Команды:${NC}
  up                     Запустить контейнеры
  down                   Остановить контейнеры
  restart                Перезагрузить контейнеры
  status                 Показать статус контейнеров

  logs [service]         Показать логи (php81, nginx, postgres)
  bash [service]         Подключиться к контейнеру bash
  shell                  Подключиться к PostgreSQL psql
  console <cmd>          Запустить Symfony команду

  migrate                Запустить миграции БД
  migrate-status         Показать статус миграций
  import-csv <file>      Импортировать CSV файл

  build                  Построить образы
  rebuild                Пересоздать контейнеры с новыми образами
  clean                  Удалить все контейнеры и тома (осторожно!)

  routes                 Показать все маршруты приложения
  services               Показать все сервисы
  cache-clear            Очистить кэш Symfony

  test-api               Запустить автоматизированные тесты API

  help                   Показать эту справку

${YELLOW}Примеры:${NC}
  ./manage.sh up
  ./manage.sh logs php81
  ./manage.sh bash php81
  ./manage.sh migrate
  ./manage.sh import-csv payments.csv
  ./manage.sh console debug:router
  ./manage.sh test-api

${YELLOW}Документация:${NC}
  - README.md           Общая информация о проекте
  - DOCKER_SETUP.md     Подробная инструкция по Docker
  - QUICKSTART.md       Быстрый старт

EOF
        ;;
esac

