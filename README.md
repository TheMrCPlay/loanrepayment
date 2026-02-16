# 🏦 Loan Repayment - Проект управления платежами

Полнофункциональное приложение для управления и обработки платежей, построенное на **Symfony 6.4** с **PHP 8.1** и **PostgreSQL**.

## 📦 Технологический стек

- **PHP 8.1** - язык программирования
- **Symfony 6.4** - веб-фреймворк
- **PostgreSQL 16** - база данных
- **Doctrine ORM** - работа с БД
- **Docker & Docker Compose** - контейнеризация
- **Nginx** - веб-сервер
- **PHPUnit** - тестирование

## 🎯 Основные возможности

- ✅ **REST API** для загрузки одиночных платежей
- ✅ **Массовый импорт** платежей из CSV файлов
- ✅ **Обработка ошибок** и валидация данных
- ✅ **Защита от дублей** платежей
- ✅ **Отчеты** по платежам
- ✅ **Логирование** операций
- ✅ **Уведомления** через Messenger

## 📁 Структура проекта

```
src/
├── Application/          # Слой приложения
│   ├── Dto/             # Объекты передачи данных
│   ├── Notification/    # Уведомления и сообщения
│   └── Service/         # Бизнес-логика приложения
├── Command/             # Консольные команды
│   ├── ImportBatchPaymentFromCsvCommand.php
│   └── PaymentReportByDateCommand.php
├── Controller/          # REST контроллеры
│   └── PaymentController.php
└── Domain/              # Бизнес-логика (domain-driven)
    ├── Assignment/      # Логика назначений платежей
    ├── Entity/          # Доменные модели
    ├── Enum/            # Перечисления
    ├── Event/           # События
    ├── Exception/       # Пользовательские исключения
    └── Service/         # Доменные сервисы
```

## 🚀 Быстрый старт

### Опция 1: Docker (Рекомендуется)

```bash
# Клонировать и перейти в директорию проекта
cd /home/jans/Projects/loanrepayment

# Запустить контейнеры
docker-compose up -d

# Инициализировать БД
docker-compose exec php81 php bin/console doctrine:migrations:migrate

# Проверить, что всё работает
curl http://localhost/api/payment/
```

Подробная инструкция: смотри [DOCKER_SETUP.md](DOCKER_SETUP.md)

### Опция 2: Локальная установка

```bash
# Установить зависимости
composer install

# Настроить БД
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate

# Запустить сервер разработки
symfony server:start
```

## 📚 API Документация

### 1. Загрузить один платеж

**Endpoint:** `POST /api/payment/`

```bash
curl -X POST http://localhost/api/payment/ \
  -H "Content-Type: application/json" \
  -d '{
    "firstname": "Иван",
    "lastname": "Иванов",
    "paymentDate": "2024-02-16T15:30:00+00:00",
    "amount": "150.99",
    "description": "Платеж LN20240216",
    "refId": "a1b2c3d4-e5f6-47g8-h9i0-j1k2l3m4n5o6"
  }'
```

**Ответ (200 OK):**
```json
{
  "message": "Payment successfully registered",
  "refId": "a1b2c3d4-e5f6-47g8-h9i0-j1k2l3m4n5o6"
}
```

### 2. Импортировать CSV

**Команда:** `php bin/console import --file=<path>`

```bash
# Создать CSV файл
cat > payments.csv << 'EOF'
firstname,lastname,paymentDate,amount,description,refId
Иван,Иванов,2024-02-16T15:30:00+00:00,150.99,LN20240216,ref-001
Петр,Петров,2024-02-16T16:45:00+00:00,200.50,LN20240216,ref-002
EOF

# Запустить импорт
docker-compose exec php81 php bin/console import --file=/app/payments.csv
```

### 3. Отчет по платежам

**Команда:** `php bin/console payment:report:by-date`

```bash
docker-compose exec php81 php bin/console payment:report:by-date --date=2024-02-16
```

## 🧪 Тестирование

### Запустить unit тесты

```bash
docker-compose exec php81 php bin/phpunit
```

### Запустить интеграционные тесты

```bash
docker-compose exec php81 php bin/phpunit tests/
```

### Автоматизированное тестирование API

```bash
# Убедись, что контейнеры запущены
docker-compose up -d

# Запустить тесты
./test_api.sh
```

## 🔧 Полезные команды

```bash
# Просмотреть логи
docker-compose logs -f php81

# Войти в контейнер
docker-compose exec php81 bash

# Запустить миграции
docker-compose exec php81 php bin/console doctrine:migrations:migrate

# Создать новую миграцию
docker-compose exec php81 php bin/console make:migration

# Откатить миграцию
docker-compose exec php81 php bin/console doctrine:migrations:migrate prev

# Очистить кэш
docker-compose exec php81 php bin/console cache:clear

# Посмотреть маршруты
docker-compose exec php81 php bin/console debug:router

# Посмотреть сервисы
docker-compose exec php81 php bin/console debug:container
```

## 🗄️ Работа с базой данных

### Подключиться к PostgreSQL

```bash
docker-compose exec postgres psql -U app -d app
```

### Полезные SQL запросы

```sql
-- Показать все платежи
SELECT * FROM payment;

-- Показать платежи за дату
SELECT * FROM payment WHERE payment_date >= '2024-02-16' AND payment_date < '2024-02-17';

-- Посчитать количество платежей
SELECT COUNT(*) FROM payment;

-- Посчитать сумму платежей
SELECT SUM(amount) FROM payment;

-- Показать статистику по платежам
SELECT 
  COUNT(*) as total_payments,
  SUM(amount) as total_amount,
  AVG(amount) as average_amount,
  MIN(amount) as min_amount,
  MAX(amount) as max_amount
FROM payment;
```

## 📋 Переменные окружения

Основные переменные окружения (`.env`):

```env
APP_ENV=dev                    # Окружение (dev, test, prod)
APP_SECRET=f961c994f994ef220843174e58d900ba  # Секретный ключ
DATABASE_URL=postgresql://app:secret123@postgres:5432/app?serverVersion=16&charset=utf8
```

## 🐛 Решение проблем

### Проблема: Ошибка подключения к БД

```bash
# Проверить статус PostgreSQL
docker-compose ps postgres

# Перезагрузить БД
docker-compose restart postgres php81
```

### Проблема: Nginx 502 Bad Gateway

```bash
# Проверить логи PHP
docker-compose logs php81

# Перезагрузить PHP
docker-compose restart php81
```

### Проблема: Порт уже в использовании

```bash
# Изменить порт в docker-compose.yml
# или
# Найти процесс и завершить его
lsof -i :80
kill -9 <PID>
```

## 📞 Поддержка

- 📖 Подробная инструкция: [DOCKER_SETUP.md](DOCKER_SETUP.md)
- 🚀 Быстрый старт: [QUICKSTART.md](QUICKSTART.md)
- 🧪 Коллекция Postman: [postman_collection.json](postman_collection.json)

## 📝 Лицензия

Этот проект лицензирован под лицензией, указанной в файле LICENSE.

## 🤝 Контрибьютинг

Приветствуются pull requests! Для больших изменений сначала откройте issue.

---

**Версия:** 1.0.0  
**Последнее обновление:** 2024-02-16

