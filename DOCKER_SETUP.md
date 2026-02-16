# Loan Repayment Project - Docker Setup & Testing Guide

## 📋 Структура проекта

- **PHP 8.1** - основной язык приложения
- **Symfony 6.4** - фреймворк
- **PostgreSQL 16** - база данных
- **Nginx** - веб-сервер
- **Docker Compose** - оркестрация контейнеров

---

## 🐳 Запуск проекта через Docker

### 1. Предварительные требования

Убедись, что установлены:
- Docker (https://docs.docker.com/get-docker/)
- Docker Compose (https://docs.docker.com/compose/install/)

### 2. Построение и запуск контейнеров

```bash
cd /home/jans/Projects/loanrepayment

# Построить образы и запустить контейнеры в фоне
docker-compose up -d

# Проверить статус контейнеров
docker-compose ps
```

**Ожидаемый результат:**
```
NAME                       STATUS
loanrepayment-postgres     Up (healthy)
loanrepayment-php          Up
loanrepayment-nginx        Up
```

### 3. Инициализация БД

```bash
# Войти в контейнер PHP
docker-compose exec php81 bash

# Внутри контейнера выполнить миграции
php bin/console doctrine:migrations:migrate

# Выйти из контейнера
exit
```

Или в одну строку:
```bash
docker-compose exec php81 php bin/console doctrine:migrations:migrate
```

### 4. Проверка здоровья приложения

```bash
# Проверить, что Nginx отвечает
curl -i http://localhost/

# Должна вернуться ошибка 404 (это нормально, так как нет маршрута "/")
```

---

## 🧪 Тестирование эндпоинтов

### Эндпоинт 1: Загрузка одного платежа (POST /api/payment/)

**URL:** `http://localhost/api/payment/`

**Метод:** `POST`

**Headers:**
```
Content-Type: application/json
```

**Body (JSON):**
```json
{
  "firstname": "Иван",
  "lastname": "Иванов",
  "paymentDate": "2024-02-16T15:30:00+00:00",
  "amount": "150.99",
  "description": "Платеж LN20240216",
  "refId": "a1b2c3d4-e5f6-47g8-h9i0-j1k2l3m4n5o6"
}
```

**Примеры тестирования:**

#### С помощью curl:
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

#### С помощью httpie:
```bash
http POST localhost/api/payment/ \
  firstname="Иван" \
  lastname="Иванов" \
  paymentDate="2024-02-16T15:30:00+00:00" \
  amount="150.99" \
  description="Платеж LN20240216" \
  refId="a1b2c3d4-e5f6-47g8-h9i0-j1k2l3m4n5o6"
```

#### С помощью Postman:
1. Создать новый запрос типа POST
2. URL: `http://localhost/api/payment/`
3. Tab "Body" → "raw" → выбрать "JSON"
4. Вставить JSON выше
5. Нажать "Send"

**Ожидаемые ответы:**

✅ **Успешный запрос (200 OK):**
```json
{
  "message": "Payment successfully registered",
  "refId": "a1b2c3d4-e5f6-47g8-h9i0-j1k2l3m4n5o6"
}
```

❌ **Дублирующийся платеж (409 Conflict):**
```json
{
  "message": "Duplicate payment detected"
}
```

❌ **Ошибка валидации (400 Bad Request):**
```json
{
  "message": "Invalid request data"
}
```

---

### Эндпоинт 2: Импорт платежей из CSV файла

**Команда:** `php bin/console import --file=<path_to_csv>`

#### Формат CSV файла:

```csv
firstname,lastname,paymentDate,amount,description,refId
Иван,Иванов,2024-02-16T15:30:00+00:00,150.99,LN20240216,a1b2c3d4-e5f6-47g8-h9i0-j1k2l3m4n5o6
Петр,Петров,2024-02-16T16:45:00+00:00,200.50,LN20240216,b2c3d4e5-f6g7-48h9-i0j1-k2l3m4n5o6p7
Сергей,Сергеев,2024-02-16T17:20:00+00:00,75.25,LN20240216,c3d4e5f6-g7h8-49i0-j1k2-l3m4n5o6p7q8
```

#### Пример использования:

**1. Создать CSV файл на хосте:**
```bash
# Создать файл payments.csv в проекте
cat > /home/jans/Projects/loanrepayment/payments.csv << 'EOF'
firstname,lastname,paymentDate,amount,description,refId
Иван,Иванов,2024-02-16T15:30:00+00:00,150.99,LN20240216,a1b2c3d4-e5f6-47g8-h9i0-j1k2l3m4n5o6
Петр,Петров,2024-02-16T16:45:00+00:00,200.50,LN20240216,b2c3d4e5-f6g7-48h9-i0j1-k2l3m4n5o6p7
EOF
```

**2. Запустить импорт через Docker:**
```bash
docker-compose exec php81 php bin/console import --file=/app/payments.csv
```

**3. Альтернативно - зайти в контейнер и запустить команду:**
```bash
# Войти в контейнер
docker-compose exec php81 bash

# Запустить импорт
php bin/console import --file=/app/payments.csv

# Выход
exit
```

**Ожидаемый вывод:**
```
Importing payments from CSV file: /app/payments.csv
Processing row 1: Иван Иванов - 150.99
✓ Successfully imported
Processing row 2: Петр Петров - 200.50
✓ Successfully imported
Processing row 3: Сергей Сергеев - 75.25
✗ Failed (Duplicate payment)

Import completed: 2 successful, 1 failed
```

---

## 🔧 Полезные Docker команды

### Просмотр логов контейнеров

```bash
# Логи PHP контейнера
docker-compose logs -f php81

# Логи Nginx
docker-compose logs -f nginx

# Логи PostgreSQL
docker-compose logs -f postgres

# Все логи
docker-compose logs -f
```

### Выполнение команд в контейнерах

```bash
# Запустить PHP команду
docker-compose exec php81 php bin/console <command>

# Запустить bash в контейнере
docker-compose exec php81 bash

# Подключиться к PostgreSQL
docker-compose exec postgres psql -U app -d app
```

### Управление контейнерами

```bash
# Остановить контейнеры
docker-compose stop

# Перезагрузить контейнеры
docker-compose restart

# Удалить контейнеры и тома
docker-compose down -v

# Пересобрать образ
docker-compose up -d --build
```

---

## 🗄️ Работа с базой данных

### Проверка БД через psql

```bash
# Подключиться к PostgreSQL
docker-compose exec postgres psql -U app -d app

# Внутри psql выполнить запросы
\dt  -- показать все таблицы
SELECT * FROM payment;  -- показать все платежи
\q   -- выход
```

### Просмотр логов миграций

```bash
docker-compose exec php81 php bin/console doctrine:migrations:status
```

### Откат миграции (если необходимо)

```bash
docker-compose exec php81 php bin/console doctrine:migrations:migrate prev
```

---

## 📋 Проверка конфигурации

### Проверить Symfony конфигурацию

```bash
# Внутри контейнера
docker-compose exec php81 php bin/console debug:config

# Проверить маршруты
docker-compose exec php81 php bin/console debug:router

# Проверить сервисы
docker-compose exec php81 php bin/console debug:container
```

---

## 🚨 Решение проблем

### Проблема: Контейнер PHP не запускается

```bash
# Проверить логи
docker-compose logs php81

# Пересобрать образ
docker-compose build --no-cache php81
docker-compose up -d php81
```

### Проблема: Ошибка подключения к БД

```bash
# Проверить статус PostgreSQL
docker-compose ps postgres

# Проверить переменные окружения
docker-compose exec php81 env | grep DATABASE

# Переделать контейнеры
docker-compose restart postgres php81
```

### Проблема: Nginx возвращает 502 Bad Gateway

```bash
# Проверить логи Nginx
docker-compose logs nginx

# Проверить работает ли PHP-FPM
docker-compose logs php81

# Перезагрузить контейнеры
docker-compose restart php81 nginx
```

### Проблема: Порты уже в использовании

```bash
# Найти процессы на портах
lsof -i :80
lsof -i :5432
lsof -i :9000

# Остановить процессы или изменить порты в docker-compose.yml
```

---

## 📝 Дополнительная информация

### Структура Docker Setup:

```
┌─────────────────────┐
│   Nginx (порт 80)   │
│   (Веб-сервер)      │
└──────────┬──────────┘
           │
           ↓
┌─────────────────────────┐
│   PHP-FPM (порт 9000)   │
│   (Приложение)          │
│  - Symfony 6.4          │
│  - PHP 8.1              │
└──────────┬──────────────┘
           │
           ↓
┌─────────────────────────┐
│ PostgreSQL (порт 5432)  │
│ (База данных)           │
│ - Database: app         │
│ - User: app             │
│ - Password: secret123   │
└─────────────────────────┘
```

### Переменные окружения:

- `APP_ENV=dev` - режим разработки
- `APP_SECRET=f961c994f994ef220843174e58d900ba` - секретный ключ Symfony
- `DATABASE_URL=postgresql://app:secret123@postgres:5432/app` - строка подключения к БД

---

## 📞 Остановка и очистка

```bash
# Остановить контейнеры
docker-compose down

# Остановить и удалить тома
docker-compose down -v

# Удалить неиспользуемые образы
docker image prune

# Полная очистка (осторожно!)
docker-compose down -v
docker image prune -a
```

---

**Готово! Теперь приложение полностью настроено для работы в Docker** 🎉

