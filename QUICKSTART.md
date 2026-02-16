# 🚀 Quick Start - Docker Loan Repayment

## Запуск за 3 шага

### 1️⃣ Запустить контейнеры
```bash
cd /home/jans/Projects/loanrepayment
docker-compose up -d
```

### 2️⃣ Инициализировать БД
```bash
docker-compose exec php81 php bin/console doctrine:migrations:migrate
```

### 3️⃣ Проверить, что всё работает
```bash
curl http://localhost/api/payment/
# или
curl -X POST http://localhost/api/payment/ \
  -H "Content-Type: application/json" \
  -d '{"firstname":"Test","lastname":"User","paymentDate":"2024-02-16T15:30:00+00:00","amount":"100.00","description":"Test","refId":"test-ref-id"}'
```

---

## 📱 Тестировать API

### Загрузить один платеж:
```bash
curl -X POST http://localhost/api/payment/ \
  -H "Content-Type: application/json" \
  -d '{
    "firstname": "Иван",
    "lastname": "Иванов",
    "paymentDate": "2024-02-16T15:30:00+00:00",
    "amount": "150.99",
    "description": "LN20240216",
    "refId": "a1b2c3d4-e5f6-47g8-h9i0-j1k2l3m4n5o6"
  }'
```

### Импортировать CSV:

**1. Создать CSV файл:**
```bash
cat > /home/jans/Projects/loanrepayment/payments.csv << 'EOF'
firstname,lastname,paymentDate,amount,description,refId
Иван,Иванов,2024-02-16T15:30:00+00:00,150.99,LN20240216,a1b2c3d4-e5f6-47g8-h9i0-j1k2l3m4n5o6
Петр,Петров,2024-02-16T16:45:00+00:00,200.50,LN20240216,b2c3d4e5-f6g7-48h9-i0j1-k2l3m4n5o6p7
EOF
```

**2. Запустить импорт:**
```bash
docker-compose exec php81 php bin/console import --file=/app/payments.csv
```

---

## 🛑 Остановить

```bash
docker-compose stop
# или полностью удалить
docker-compose down -v
```

---

## 📖 Подробная инструкция

Смотри `DOCKER_SETUP.md`

---

## 🆘 Проблемы?

```bash
# Посмотреть логи
docker-compose logs -f php81

# Перезагрузить
docker-compose restart

# Пересобрать
docker-compose up -d --build
```

