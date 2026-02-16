# 📦 Установка и настройка Composer

Composer - это инструмент управления зависимостями для PHP. Вот инструкция по установке и использованию.

## 🔧 Установка Composer

### Вариант 1: Официальная установка (Рекомендуется)

```bash
# Скачать инсталлятор
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"

# Проверить сумму (опционально)
php -r "if (hash_file('sha384', 'composer-setup.php') === 'e21205b86b891a7529f23a29188d4f60f24cee675eca9e47d8032ade8e390413323220908a62e1f6b9b5dd9757e3dccb') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"

# Установить Composer локально
php composer-setup.php

# Удалить инсталлятор
php -r "unlink('composer-setup.php');"

# Переместить в global бин (опционально)
sudo mv composer.phar /usr/local/bin/composer
```

### Вариант 2: Через apt (для Debian/Ubuntu)

```bash
# Обновить пакеты
sudo apt update

# Установить Composer
sudo apt install composer -y

# Проверить установку
composer --version
```

### Вариант 3: Через Docker (Если Composer не установлен)

У нас уже есть Composer в Docker образе. Используй:

```bash
# Вместо локального composer используй через Docker:
docker-compose exec php81 composer <command>
```

## ✅ Проверка установки

```bash
# Проверить версию
composer --version

# Должен вывести что-то вроде:
# Composer version 2.5.8 2023-11-09 17:51:19
```

## 🚀 Использование Composer в проекте

### Установка зависимостей проекта

```bash
# Вариант 1: Локально
composer install

# Вариант 2: Через Docker (если нет локального composer)
docker-compose exec php81 composer install
```

### Обновление зависимостей

```bash
# Обновить все пакеты (локально)
composer update

# Обновить через Docker
docker-compose exec php81 composer update

# Обновить конкретный пакет
composer update symfony/framework-bundle
```

### Добавление новой зависимости

```bash
# Локально
composer require symfony/http-client

# Через Docker
docker-compose exec php81 composer require symfony/http-client

# Добавить dev зависимость
composer require --dev symfony/maker-bundle
```

### Удаление зависимости

```bash
# Локально
composer remove symfony/http-client

# Через Docker
docker-compose exec php81 composer remove symfony/http-client
```

## 📋 Полезные команды

```bash
# Показать установленные пакеты
composer show

# Показать информацию о конкретном пакете
composer show symfony/framework-bundle

# Очистить кэш
composer clear-cache

# Обновить автолоадер
composer dump-autoload

# Обновить автолоадер с оптимизацией
composer dump-autoload --optimize

# Проверить на уязвимости (audit)
composer audit

# Проверить зависимости на устаревание
composer outdated

# Проверить конфигурацию
composer validate
```

## 📝 Файлы Composer

### `composer.json`
Основной файл конфигурации проекта. Содержит:
- Название проекта
- Требуемые зависимости
- Версии PHP
- Автолоадинг
- Скрипты

### `composer.lock`
Файл блокировки версий. **НЕ РЕДАКТИРУЙ ВРУЧНУЮ!**
- Содержит точные версии установленных пакетов
- Гарантирует одинаковое окружение на разных машинах
- Всегда коммитится в Git

## 🔍 Структура composer.json в нашем проекте

```json
{
  "type": "project",
  "license": "proprietary",
  "require": {
    "php": ">=8.1",
    "symfony/framework-bundle": "6.4.*",
    "doctrine/orm": "^3.6",
    "league/csv": "^9.28"
  },
  "require-dev": {
    "phpunit/phpunit": "^10.5",
    "symfony/maker-bundle": "^1.66"
  },
  "autoload": {
    "psr-4": {
      "App\\": "src/"
    }
  }
}
```

**Где:**
- `require` - зависимости для production
- `require-dev` - зависимости только для разработки
- `autoload` - правила автозагрузки классов (PSR-4)

## 🐳 Работа с Composer в Docker

### Установить зависимости в Docker

```bash
# Вариант 1: Во время построения образа (уже сделано в Dockerfile)
docker-compose up -d --build

# Вариант 2: После запуска контейнера
docker-compose exec php81 composer install
```

### Обновить зависимости в Docker

```bash
docker-compose exec php81 composer update
docker-compose exec php81 composer dump-autoload --optimize
```

### Добавить пакет в Docker

```bash
docker-compose exec php81 composer require symfony/http-client
```

## 🆘 Решение проблем

### Ошибка: "composer: command not found"

```bash
# Установи Composer через один из методов выше
# или используй через Docker:
docker-compose exec php81 composer <command>
```

### Ошибка: "Your requirements could not be resolved"

```bash
# Обнови Composer
composer self-update

# Очистить кэш
composer clear-cache

# Попробовать еще раз
composer install
```

### Ошибка: "Killed" при composer install

```bash
# Увеличить лимит памяти PHP
php -d memory_limit=-1 composer install

# Или через Docker
docker-compose exec php81 php -d memory_limit=-1 composer install
```

### Конфликты зависимостей

```bash
# Показать состояние конфликтов
composer diagnose

# Попробовать переустановить
composer install --no-cache

# Или обновить-переустановить
composer update --lock
```

## 📚 Дополнительные ресурсы

- 🌐 Официальный сайт: https://getcomposer.org/
- 📖 Документация: https://getcomposer.org/doc/
- 📦 Репозиторий пакетов: https://packagist.org/
- 🐛 Решение проблем: https://getcomposer.org/doc/articles/troubleshooting.md

## 💡 Лучшие практики

1. **Всегда коммитить `composer.lock`** - обеспечивает воспроизводимость
2. **Регулярно обновлять зависимости** - исправления безопасности
3. **Проверять на уязвимости** - `composer audit`
4. **Использовать точные версии** - `composer.lock`
5. **Оптимизировать автолоадер** - `composer dump-autoload --optimize`

---

**Теперь ты готов использовать Composer в проекте! 🚀**

