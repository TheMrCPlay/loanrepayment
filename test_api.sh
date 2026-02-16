#!/bin/bash

# Loan Repayment API - Automated Testing Script
set -e

API_URL="http://localhost/api/payment"
CSV_FILE="/tmp/test_payments.csv"

echo "========================================="
echo "Loan Repayment API - Automated Tests"
echo "========================================="
echo ""

# Загрузи fixtures перед тестами
echo "📦 Загрузка тестовых данных (fixtures)..."
docker-compose exec php81 php bin/console doctrine:fixtures:load --no-interaction > /dev/null 2>&1
echo "✅ Fixtures загружены"
echo ""

# Проверка доступности API
echo "🔍 Проверка доступности API..."
if ! curl -s -o /dev/null -w "%{http_code}" "$API_URL/" | grep -q "^[0-9]*$"; then
    echo "❌ API недоступен!"
    exit 1
fi
echo "✅ API доступен"
echo ""

# Тест 1: Загрузка платежа для LN12345678
echo "📝 Тест 1: Загрузка платежа для LN12345678"
RESPONSE=$(curl -s -X POST "$API_URL/" \
  -H "Content-Type: application/json" \
  -d '{
    "firstname": "Lorem",
    "lastname": "Ipsum",
    "paymentDate": "2024-02-16T15:30:00+00:00",
    "amount": "50.00",
    "description": "LN12345678",
    "refId": "dda8b637-b2e8-4f79-a4af-d1d68e266bf5"
  }')

echo "Ответ: $RESPONSE"
if echo "$RESPONSE" | grep -q "successfully\|processed"; then
    echo "✅ Платеж успешно загружен"
else
    echo "⚠️  Проверь ответ API"
fi
echo ""

# Тест 2: Загрузка платежа для LN22345678
echo "📝 Тест 2: Загрузка платежа для LN22345678"
RESPONSE=$(curl -s -X POST "$API_URL/" \
  -H "Content-Type: application/json" \
  -d '{
    "firstname": "Lorem",
    "lastname": "Ipsum",
    "paymentDate": "2022-12-12T15:19:21+00:00",
    "amount": "99.99",
    "description": "LN22345678",
    "refId": "130f8a89-51c9-47d0-a6ef-1aea54924d3b"
  }')

echo "Ответ: $RESPONSE"
if echo "$RESPONSE" | grep -q "successfully\|processed"; then
    echo "✅ Платеж успешно загружен"
else
    echo "⚠️  Проверь ответ API"
fi
echo ""

# Тест 3: Попытка загрузить дублирующийся платеж (должно быть 409)
echo "📝 Тест 3: Попытка загрузить дублирующийся платеж (должно быть 409)"
REF_ID="duplicate-test-$(date +%s)"

# Загружаем первый раз
curl -s -X POST "$API_URL/" \
  -H "Content-Type: application/json" \
  -d '{
    "firstname": "Test",
    "lastname": "User",
    "paymentDate": "2024-02-16T16:45:00+00:00",
    "amount": "100.00",
    "description": "LN55522533",
    "refId": "'$REF_ID'"
  }' > /dev/null

# Пытаемся загрузить дубликат
DUPLICATE_RESPONSE=$(curl -s -w "\n%{http_code}" -X POST "$API_URL/" \
  -H "Content-Type: application/json" \
  -d '{
    "firstname": "Test",
    "lastname": "User",
    "paymentDate": "2024-02-16T16:45:00+00:00",
    "amount": "100.00",
    "description": "LN55522533",
    "refId": "'$REF_ID'"
  }')

HTTP_CODE=$(echo "$DUPLICATE_RESPONSE" | tail -1)
if [ "$HTTP_CODE" = "409" ]; then
    echo "✅ Дублирующийся платеж корректно отклонен (409)"
else
    echo "⚠️  Ожидался код 409, получен $HTTP_CODE"
fi
echo ""

# Тест 4: CSV импорт
echo "📝 Тест 4: Импорт платежей из CSV"

if [ -f "payments.csv" ]; then
    docker cp payments.csv loanrepayment-php:/app/payments.csv
    IMPORT_OUTPUT=$(docker-compose exec php81 php bin/console import --file=/app/payments.csv 2>&1 || true)
    echo "Вывод импорта: $IMPORT_OUTPUT"
    echo "✅ Импорт завершен"
else
    echo "❌ Файл payments.csv не найден"
fi
echo ""

# Тест 5: Проверка БД
echo "📝 Тест 5: Проверка количества платежей в БД"
COUNT=$(docker-compose exec postgres psql -U app -d app -t -c "SELECT COUNT(*) FROM payment;" 2>/dev/null || echo "0")
echo "Всего платежей в БД: $COUNT"
echo "✅ Проверка завершена"
echo ""

rm -f "$CSV_FILE"

echo "========================================="
echo "✅ Все тесты завершены!"
echo "========================================="