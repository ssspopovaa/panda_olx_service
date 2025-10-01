# Price Watcher

A Laravel application for tracking price changes on OLX listings. Users can subscribe to listings via URL, verify their subscription via email, and receive notifications when prices change.

## Features
- Subscribe to OLX listings with email verification
- Periodic price checks using a scheduler
- Email notifications for price changes
- API-driven subscription management
- Queue-based processing for price checks and notifications
- Web UI for subscribing and viewing subscriptions
- Price history tracking for each advert
- Dual OLX client support: API client (OAuth) and HTML parser fallback
- Repository pattern architecture
- Comprehensive test coverage

## Architecture
The application uses:
- **Services Layer**: `SubscriptionService`, `PriceWatcherService`, `OlxApiClient`, `OlxParserClient`
- **Repository Pattern**: `AdvertRepository`, `SubscriptionRepository`
- **Jobs**: `CheckAdvertJob`, `NotifySubscribersJob`
- **Models**: `Advert`, `Subscription`, `AdvertPrice`

See `diagram.png` in the root directory for system architecture visualization.

## Requirements
- PHP >= 8.2
- Composer
- Laravel 12.x
- MySQL 8.0
- Redis 7
- Node.js & NPM (for frontend assets)
- Docker & Docker Compose (recommended)

## Installation

### Using Docker (Recommended)

1. **Clone the repository**:
   ```bash
   git clone git@github.com:ssspopovaa/panda_olx_service.git price-watcher
   cd price-watcher
   ```

2. **Set up environment**:
   ```bash
   cp .env.example .env
   ```

   Update the following in `.env`:
   ```env
   APP_URL=http://localhost

   # Database (Docker)
   DB_CONNECTION=mysql
   DB_HOST=db
   DB_PORT=3306
   DB_DATABASE=panda
   DB_USERNAME=root
   DB_PASSWORD=root

   # Redis (Docker)
   REDIS_HOST=redis

   # Mail (MailHog for local development)
   MAIL_MAILER=smtp
   MAIL_HOST=mailhog
   MAIL_PORT=1025
   MAIL_FROM_ADDRESS="noreply@pricewatch.local"

   # Queue
   QUEUE_CONNECTION=database

   # OLX API (optional - falls back to HTML parser)
   OLX_BASE_URL=https://api.olx.ua
   OLX_CLIENT_ID=your-client-id
   OLX_CLIENT_SECRET=your-client-secret
   ```

3. **Start Docker containers**:
   ```bash
   docker compose up --build -d
   ```

4. **Install dependencies**:
   ```bash
   docker exec -it panda_app composer install
   docker exec -it panda_app npm install
   ```

5. **Generate application key**:
   ```bash
   docker exec -it panda_app php artisan key:generate
   ```

6. **Run migrations**:
   ```bash
   docker exec -it panda_app php artisan migrate
   ```

7. **Build frontend assets**:
   ```bash
   docker exec -it panda_app npm run build
   ```

### Local Installation (Without Docker)

1. **Clone and install**:
   ```bash
   git clone git@github.com:ssspopovaa/panda_olx_service.git price-watcher
   cd price-watcher
   composer install
   npm install
   ```

2. **Configure environment**:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

   Update database and mail settings in `.env` according to your local setup.

3. **Run migrations**:
   ```bash
   php artisan migrate
   ```

4. **Build assets**:
   ```bash
   npm run build
   ```

## Usage

### Development Mode

Run all services concurrently (server, queue, logs, vite):
```bash
composer dev
```

Or run services separately:

1. **Start the application**:
   - Docker: `http://localhost` (nginx)
   - Local: `php artisan serve` → `http://localhost:8000`

2. **Run queue worker**:
   ```bash
   php artisan queue:work
   # Or with Docker:
   docker exec -it panda_app php artisan queue:work
   ```

3. **Run scheduler** (for periodic price checks):
   ```bash
   php artisan schedule:work
   # Or with Docker:
   docker exec -it panda_app php artisan schedule:work
   ```

4. **Access MailHog** (Docker only):
   - UI: `http://localhost:8025`
   - View all outgoing emails during development

### Subscribing to Listings

#### Via Web UI
1. Navigate to `http://localhost/subscribe`
2. Enter OLX listing URL and email
3. Check your email for verification link
4. Click verification link to activate subscription

#### Via API
```bash
# Subscribe
curl -X POST http://localhost/api/subscribe \
  -H "Content-Type: application/json" \
  -d '{"url":"https://www.olx.ua/d/uk/obyavlenie/example","email":"test@example.com"}'

# Verify subscription
curl "http://localhost/api/verify?token=YOUR_TOKEN"

# List subscriptions by email
curl "http://localhost/api/subscriptions?email=test@example.com"
```

### Manual Price Checks

Trigger price checks for adverts not checked in last 15 minutes:
```bash
php artisan adverts:enqueue-checks --interval=15
```

### Available Artisan Commands

- `adverts:enqueue-checks` - Enqueue adverts for price checking
  - `--interval=15` - Check adverts not checked in last N minutes (default: 15)

### API Endpoints

- `POST /api/subscribe` - Create new subscription (requires email verification)
- `GET /api/verify?token={token}` - Verify email subscription
- `GET /api/subscriptions?email={email}` - List subscriptions with price history

### Web Routes

- `GET /` - Welcome page
- `GET /subscribe` - Subscription form
- `POST /subscribe` - Process subscription (web form)
- `GET /verify?token={token}` - Verify subscription
- `GET /subscriptions` - View subscriptions form
- `POST /subscriptions` - List subscriptions by email

## Testing

The application includes comprehensive test coverage:
- **Feature Tests**: `SubscribeFlowTest`, `PriceWatcherTest`
- **Unit Tests**: `OAuthTokenManagerTest`, `OlxParserClientTest`

### Running Tests

1. **Configure `.env.testing`** (if not exists):
   ```env
   DB_CONNECTION=sqlite
   DB_DATABASE=:memory:
   QUEUE_CONNECTION=sync
   CACHE_DRIVER=array
   MAIL_MAILER=log
   ```

2. **Run all tests**:
   ```bash
   php artisan test
   # Or with debugging:
   php artisan test --debug
   # Or with coverage (requires Xdebug):
   php artisan test --coverage
   ```

3. **Run specific test suite**:
   ```bash
   php artisan test --testsuite=Feature
   php artisan test --testsuite=Unit
   ```

4. **Run with Docker**:
   ```bash
   docker exec -it panda_app php artisan test
   ```

### Using Composer Scripts

Quick test command:
```bash
composer test
```

## Docker Services

The docker-compose setup includes:
- **nginx** (port 80) - Web server
- **app** (panda_app) - PHP-FPM application container
- **db** (panda_db) - MySQL 8.0 database
- **redis** - Redis cache/queue backend
- **mailhog** (ports 1025, 8025) - Email testing

### Accessing Containers

```bash
# App container shell
docker exec -it panda_app bash

# Database
docker exec -it panda_db mysql -uroot -proot panda

# View logs
docker logs panda_app
docker logs panda_nginx
```

## How It Works

1. **Subscription Flow**:
   - User subscribes to OLX listing URL
   - System fetches initial price using OlxApiClient or OlxParserClient
   - Verification email sent to user
   - User clicks verification link to activate subscription

2. **Price Monitoring**:
   - Scheduler runs `adverts:enqueue-checks` command periodically
   - Command queues `CheckAdvertJob` for each active advert
   - Job fetches current price from OLX
   - If price changed, `NotifySubscribersJob` is dispatched
   - Subscribers receive email notification with old/new prices
   - Price history stored in `advert_prices` table

3. **OLX Data Fetching**:
   - **Primary**: OlxApiClient (requires OAuth credentials)
   - **Fallback**: OlxParserClient (HTML parsing with JSON-LD extraction)
   - Automatic failover if API is unavailable

4. **Error Handling**:
   - Failed checks increment `check_error_count`
   - After 10 consecutive failures, advert is marked inactive
   - Cache locks prevent duplicate concurrent checks

## Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Add tests for new functionality
5. Ensure all tests pass: `composer test`
6. Submit a pull request

## License

MIT License
