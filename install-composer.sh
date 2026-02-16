#!/bin/bash

# Скрипт для установки Composer на Linux/macOS
# Использование: ./install-composer.sh

set -e

echo "🐳 Установка Composer"
echo ""

# Проверка, установлен ли PHP
if ! command -v php &> /dev/null; then
    echo "❌ PHP не установлен. Пожалуйста, установи PHP 8.1 или выше."
    echo ""
    echo "На Ubuntu/Debian:"
    echo "  sudo apt update"
    echo "  sudo apt install -y php8.1-cli php8.1-curl php8.1-zip"
    exit 1
fi

PHP_VERSION=$(php -v | head -n 1)
echo "✅ Найден $PHP_VERSION"
echo ""

# Проверка, установлен ли Composer
if command -v composer &> /dev/null; then
    echo "✅ Composer уже установлен:"
    composer --version
    echo ""
    read -p "Хотите переустановить? (y/n) " -n 1 -r
    echo
    if [[ ! $REPLY =~ ^[Yy]$ ]]; then
        echo "Отмена."
        exit 0
    fi
fi

echo "📥 Скачивание Composer..."
cd /tmp

# Скачиваем инсталлятор Composer
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"

# Проверяем контрольную сумму
echo "🔐 Проверка контрольной суммы..."
EXPECTED_CHECKSUM=$(php -r "echo hash_file('sha384', 'https://composer.github.io/installer.sha384bin');")
ACTUAL_CHECKSUM=$(php -r "echo hash_file('sha384', 'composer-setup.php');")

if [ "$EXPECTED_CHECKSUM" != "$ACTUAL_CHECKSUM" ]; then
    echo "❌ Контрольная сумма не совпадает!"
    php -r "unlink('composer-setup.php');"
    exit 1
fi

echo "✅ Контрольная сумма верна"
echo ""

# Устанавливаем Composer
echo "📦 Установка Composer..."
php composer-setup.php --quiet

# Переместить в глобальное место
if [ -f "composer.phar" ]; then
    echo "📍 Перемещение Composer в /usr/local/bin..."
    sudo mv composer.phar /usr/local/bin/composer
    chmod +x /usr/local/bin/composer

    # Удаляем инсталлятор
    rm -f composer-setup.php

    echo ""
    echo "✅ Composer успешно установлен!"
    echo ""
    composer --version
    echo ""
    echo "Проверь установку:"
    echo "  composer --version"
    echo ""
    echo "Перейди в проект и установи зависимости:"
    echo "  cd /home/jans/Projects/loanrepayment"
    echo "  composer install"
else
    echo "❌ Ошибка: composer.phar не найден"
    exit 1
fi

