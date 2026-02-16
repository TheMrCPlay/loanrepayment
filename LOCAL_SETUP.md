# 🖥️ Локальная установка (без Docker)

Если ты хочешь запустить проект локально без Docker, вот инструкция.

## 📋 Требования

- **PHP 8.1+** с расширениями: bcmath, zip, pdo, pdo_pgsql
- **PostgreSQL 16** (или другая версия)
- **Composer** (менеджер зависимостей PHP)
- **Nginx или Apache** (веб-сервер)

## 🔧 Шаг 1: Установить PHP 8.1

### Ubuntu/Debian:

```bash
# Обновить пакеты
sudo apt update

# Установить PHP 8.1 и необходимые расширения
sudo apt install -y \
    php8.1-cli \
    php8.1-fpm \
    php8.1-pgsql \
    php8.1-zip \
    php8.1-curl \
    php8.1-bcmath

# Проверить установку
php -v
```

### macOS (с Homebrew):

```bash
# Установить PHP 8.1
brew install php@8.1

# Добавить в PATH
echo 'export PATH="/usr/local/opt/php@8.1/bin:$PATH"' >> ~/.zshrc
source ~/.zshrc

# Проверить
php -v
```

## 🔧 Шаг 2: Установить PostgreSQL 16

### Ubuntu/Debian:

```bash
# Установить PostgreSQL
sudo apt install -y postgresql postgresql-contrib

# Запустить сервис
sudo systemctl start postgresql
sudo systemctl enable postgresql

# Проверить статус
sudo systemctl status postgresql
```

### macOS (с Homebrew):

```bash
# Установить PostgreSQL
brew install postgresql@16

# Запустить
brew services start postgresql@16

# Проверить
psql --version
```

### Создать БД и пользователя

```bash
# Подключиться к PostgreSQL
sudo -u postgres psql

# Внутри psql выполнить:
CREATE USER app WITH PASSWORD 'secret123';
CREATE DATABASE app OWNER app;
GRANT ALL PRIVILEGES ON DATABASE app TO app;

# Выход
\q
```

## 📦 Шаг 3: Установить Composer

### Вариант 1: Через скрипт (рекомендуется)

```bash
cd /home/jans/Projects/loanrepayment
./install-composer.sh
```

### Вариант 2:Ручная установка

```bash
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php composer-setup.php
sudo mv composer.phar /usr/local/bin/composer
php -r "unlink('composer-setup.php');"

# Проверить
composer --version
```

## 🚀 Шаг 4: Установить зависимости проекта

```bash
cd /home/jans/Projects/loanrepayment

# Установить PHP зависимости
composer install

# Если возникнут проблемы с памятью:
php -d memory_limit=-1 composer install
```

## 🗄️ Шаг 5: Инициализировать БД

```bash
# Создать таблицы (запустить миграции)
php bin/console doctrine:migrations:migrate

# Проверить, что таблицы созданы
php bin/console doctrine:query:sql "SELECT * FROM information_schema.tables WHERE table_schema='public';"
```

## 🌐 Шаг 6: Запустить веб-сервер

### Вариант 1: Встроенный сервер Symfony (для разработки)

```bash
# Запустить на localhost:8000
symfony server:start

# Или напрямую
php -S localhost:8000 -t public/
```

### Вариант 2: Nginx

**Создать конфиг `/etc/nginx/sites-available/loanrepayment`:**

```nginx
server {
    listen 80;
    server_name localhost;
    root /home/jans/Projects/loanrepayment/public;

    location / {
        try_files $uri /index.php$is_args$args;
    }

    location ~ ^/index\.php(/|$) {
        fastcgi_pass 127.0.0.1:9000;
        fastcgi_split_path_info ^(.+\.php)(/.*)$;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        fastcgi_param DOCUMENT_ROOT $realpath_root;
    }

    location ~ \.php$ {
        return 404;
    }
}
```

**Активировать:**

```bash
sudo ln -s /etc/nginx/sites-available/loanrepayment /etc/nginx/sites-enabled/

# Проверить конфиг
sudo nginx -t

# Перезагрузить Nginx
sudo systemctl restart nginx

# Убедиться, что PHP-FPM работает
sudo systemctl start php8.1-fpm
sudo systemctl enable php8.1-fpm
```

### Вариант 3: Apache

**Создать конфиг `/etc/apache2/sites-available/loanrepayment.conf`:**

```apache
<VirtualHost *:80>
    ServerName localhost
    DocumentRoot /home/jans/Projects/loanrepayment/public

    <Directory /home/jans/Projects/loanrepayment/public>
        AllowOverride All
        Require all granted
        
        <IfModule mod_rewrite.c>
            RewriteEngine On
            RewriteCond %{REQUEST_FILENAME} !-f
            RewriteRule ^(.*)$ index.php [QSA,L]
        </IfModule>
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/loanrepayment_error.log
    CustomLog ${APACHE_LOG_DIR}/loanrepayment_access.log combined
</VirtualHost>
```

**Активировать:**

```bash
sudo a2ensite loanrepayment
sudo a2enmod rewrite
sudo apache2ctl configtest
sudo systemctl restart apache2
```

## ✅ Проверка установки

```bash
# Проверить, что все работает
curl http://localhost/api/payment/

# Должна вернуться ошибка 404 (нет маршрута "/"), что нормально
```

## 🧪 Тестирование

### Загрузить платеж через API

```bash
curl -X POST http://localhost/api/payment/ \
  -H "Content-Type: application/json" \
  -d '{
    "firstname": "Иван",
    "lastname": "Иванов",
    "paymentDate": "2024-02-16T15:30:00+00:00",
    "amount": "150.99",
    "description": "LN20240216",
    "refId": "local-test-001"
  }'
```

### Импортировать CSV

```bash
# Создать CSV
cat > /tmp/payments.csv << 'EOF'
firstname,lastname,paymentDate,amount,description,refId
Петр,Петров,2024-02-16T16:45:00+00:00,200.50,LN20240216,local-test-002
EOF

# Импортировать
php bin/console import --file=/tmp/payments.csv
```

### Запустить тесты

```bash
# Unit тесты
php bin/phpunit

# Или конкретный тест
php bin/phpunit tests/Domain/Service/
```

## 🔧 Полезные команды

```bash
# Показать информацию о Symfony
php bin/console about

# Показать маршруты
php bin/console debug:router

# Показать сервисы
php bin/console debug:container

# Очистить кэш
php bin/console cache:clear

# Создать миграцию
php bin/console make:migration

# Статус миграций
php bin/console doctrine:migrations:status

# Откатить миграцию
php bin/console doctrine:migrations:migrate prev
```

## 🗄️ Работа с БД через psql

```bash
# Подключиться к БД
psql -U app -d app -h localhost

# Внутри psql
\dt              -- показать таблицы
SELECT * FROM payment;  -- показать платежи
\q              -- выход
```

## 🆘 Решение проблем

### Ошибка: "SQLSTATE[HY000]: General error: 7 Cannot add a new role"

```bash
# Решение: создать пользователя БД правильно
sudo -u postgres psql -c "CREATE USER app WITH PASSWORD 'secret123';"
sudo -u postgres psql -c "CREATE DATABASE app OWNER app;"
sudo -u postgres psql -c "GRANT ALL PRIVILEGES ON DATABASE app TO app;"
```

### Ошибка: "PDOException: SQLSTATE[08006]"

```bash
# Проверить, что PostgreSQL работает
sudo systemctl status postgresql

# Или перезагрузить
sudo systemctl restart postgresql
```

### Ошибка: "Module php8.1-pgsql is not enabled"

```bash
# Включить расширение
sudo phpenmod pgsql

# Или установить, если не установлено
sudo apt install php8.1-pgsql

# Перезагрузить PHP-FPM
sudo systemctl restart php8.1-fpm
```

### Composer требует слишком много памяти

```bash
# Установить с увеличенным лимитом
php -d memory_limit=-1 composer install

# Или отредактировать php.ini
sudo nano /etc/php/8.1/cli/php.ini
# Найти memory_limit и установить -1 (неограниченная память)
```

## 📊 Переменные окружения

Убедись, что `.env` файл содержит правильные данные:

```env
APP_ENV=dev
APP_SECRET=f961c994f994ef220843174e58d900ba
DATABASE_URL="postgresql://app:secret123@127.0.0.1:5432/app?serverVersion=16&charset=utf8"
```

## 🔒 Безопасность для разработки

⚠️ **Важно:** Эти настройки только для разработки!

Для production:
- Изменить пароли БД
- Использовать HTTPS
- Установить `APP_ENV=prod`
- Отключить debug mode
- Настроить firewall
- Использовать переменные окружения вместо `.env`

## 📚 Дополнительные ресурсы

- Symfony: https://symfony.com/doc/current/setup.html
- PostgreSQL: https://www.postgresql.org/docs/
- Composer: https://getcomposer.org/doc/
- PHP: https://www.php.net/manual/

---

**Готово! Теперь у тебя есть локальная установка проекта.** 🎉

