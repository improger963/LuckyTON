<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## 🚀 Telegram Mini App с Laravel Backend

Этот проект представляет собой Laravel API backend для Telegram Mini App с полной системой аутентификации и реального времени.

### ✨ Возможности

- ✅ Регистрация и авторизация через Telegram Mini App
- ✅ Валидация данных Telegram с использованием HMAC-SHA256
- ✅ JWT-подобная аутентификация через Laravel Sanctum
- ✅ Защищенные API endpoints
- ✅ Хранение данных пользователя из Telegram
- ✅ Тестовая страница для проверки аутентификации
- ✅ Реальное время с Laravel Reverb для игровых комнат
- ✅ Автоматическое обновление списка игроков

### 📋 Требования

- PHP 8.2+
- Composer
- MySQL/PostgreSQL/SQLite
- Docker & Docker Compose (для Laravel Sail)
- Node.js и npm (для фронтенда)

### 🛠 Установка

1. **Клонируйте репозиторий**
```bash
git clone <your-repo-url>
cd server
```

2. **Установите зависимости**
```bash
composer install
```

3. **Скопируйте .env файл**
```bash
cp .env.example .env
```

4. **Добавьте токен вашего Telegram бота в .env**
```env
TELEGRAM_BOT_TOKEN=your_bot_token_from_botfather
```

5. **Сгенерируйте ключ приложения**
```bash
php artisan key:generate
```

6. **Запустите миграции**
```bash
php artisan migrate
```

7. **Запустите сервер (если используете Sail)**
```bash
./vendor/bin/sail up -d
```

Или стандартный сервер разработки:
```bash
php artisan serve
```

### 📡 Реальное время (Laravel Reverb)

Этот проект использует Laravel Reverb для реализации реального времени в игровых комнатах.

#### Запуск Reverb сервера

```bash
php artisan reverb:start
```

#### Особенности реализации

- Автоматическое обновление списка игроков при присоединении
- Использование приватных каналов для безопасности
- Интеграция с Laravel Echo на фронтенде
- Поддержка горизонтального масштабирования через Redis

Более подробная информация в [README_REVERB.md](README_REVERB.md)

### 📚 API Документация

Полная документация API находится в файле [TELEGRAM_AUTH_API.md](TELEGRAM_AUTH_API.md)

#### Основные endpoints:

- `POST /api/auth/telegram/callback` - Аутентификация через Telegram
- `GET /api/auth/me` - Получить профиль текущего пользователя
- `POST /api/auth/logout` - Выход из системы
- `GET /api/health` - Проверка работоспособности API
- `GET /api/game-rooms/{id}` - Получить детали игровой комнаты
- `POST /api/game-rooms/{id}/join` - Присоединиться к игровой комнате

### 🧪 Тестирование

Откройте в браузере (внутри Telegram Mini App):
```
https://your-domain.com/test-telegram-auth.html
```

Для тестирования реального времени:
```
GET /api/test-broadcast
```

### 🔐 Безопасность

- Все данные от Telegram проходят криптографическую проверку
- Используется HMAC-SHA256 для валидации
- Данные аутентификации действительны 5 минут
- Токены хранятся безопасно через Laravel Sanctum
- Приватные каналы Reverb защищены авторизацией

### 📱 Настройка Telegram Bot

1. Создайте бота через [@BotFather](https://t.me/BotFather)
2. Получите токен и добавьте его в `.env`
3. Настройте Menu Button для вашего Mini App:
   ```
   /setmenubutton
   - Выберите вашего бота
   - Введите название кнопки (например: "Open App")
   - Введите URL вашего приложения
   ```

### 🗄️ Структура базы данных

Таблица `users` содержит:
- `telegram_id` - уникальный ID из Telegram
- `username` - @username
- `first_name` - имя
- `last_name` - фамилия
- `language_code` - код языка
- `is_premium` - премиум статус
- `photo_url` - URL аватара

### 📝 Пример использования во фронтенде

```javascript
const tg = window.Telegram.WebApp;
const initData = tg.initData;

const response = await fetch('/api/auth/telegram/callback', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({
    ...parseInitData(initData),
  }),
});

const { token, user } = await response.json();
localStorage.setItem('auth_token', token);
```

---