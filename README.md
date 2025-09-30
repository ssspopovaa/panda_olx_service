# Price Watcher

A Laravel application for tracking price changes on OLX listings. Users can subscribe to listings via URL, verify their subscription via email, and receive notifications when prices change.

## Features
- Subscribe to OLX listings with email verification.
- Periodic price checks using a scheduler.
- Email notifications for price changes.
- API-driven subscription management.
- Queue-based processing for price checks and notifications.

## Diagram
- Diagramm image "diagram.png" is located in the root directory of the project. 

## Requirements
- PHP >= 8.1
- Composer
- Laravel 12.x
- MySQL or SQLite
- Redis (optional, for queues)
- SMTP service (e.g., Mailtrap)

## Installation
1. **Clone the repository**:
   ```bash
   git clone git@github.com:ssspopovaa/panda_olx_service.git price-watcher
   cd price-watcher
   docker compose up --build
   ```

2. **Install dependencies (inside container)**:
   ```bash
   composer install
   ```

3. **Set up `.env`**:
   ```bash
   cp .env.example .env
   ```
   Configure:
   ```env
   APP_URL=http://localhost
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=price_watcher
   DB_USERNAME=root
   DB_PASSWORD=

   QUEUE_CONNECTION=database
   CACHE_DRIVER=array

   MAIL_MAILER=smtp
   MAIL_HOST=smtp.mailtrap.io
   MAIL_PORT=2525
   MAIL_USERNAME=your-username
   MAIL_PASSWORD=your-password
   ```

4. **Generate app key**:
   ```bash
   php artisan key:generate
   ```

5. **Run migrations**:
   ```bash
   php artisan migrate
   ```

## Usage
1. **Run queue worker**:
   ```bash
   php artisan queue:work
   ```

2. **Run scheduler** (local testing):
   ```bash
   php artisan schedule:work
   ```

3. **Subscribe to a listing**:
   ```bash
   curl -X POST http://localhost:8000/api/subscribe -H "Content-Type: application/json" -d '{"url":"https://olx.ua/test","email":"test@example.com"}'
   ```

4. **Verify subscription**:
   Click the link in the verification email.

5. **Check prices manually**:
   ```bash
   php artisan adverts:enqueue-checks --interval=15
   ```

## Testing
1. Configure `.env.testing`:
   ```env
   DB_CONNECTION=sqlite
   DB_DATABASE=:memory:
   QUEUE_CONNECTION=sync
   CACHE_DRIVER=array
   MAIL_MAILER=log
   ```

2. Run tests:
   ```bash
   php artisan test --debug
   ```

## License
MIT License
