# MakemeupAI API (Laravel 11)

REST API with Laravel Sanctum SPA cookie authentication for the Vue 3 frontend at `http://localhost:5173`.

## Prerequisites

- PHP 8.2+
- Composer 2.x
- SQLite (enabled) or MySQL

## First-time setup

```bash
cd backend
composer install
copy .env.example .env
php artisan key:generate
```

Create SQLite database file:

```bash
# Windows PowerShell
New-Item -ItemType File -Path database\database.sqlite -Force
```

Run migrations:

```bash
php artisan migrate
```

Start API server:

```bash
php artisan serve
```

API base URL: `http://localhost:8000`

## Environment (SPA auth)

Required `.env` values:

```env
APP_URL=http://localhost:8000
FRONTEND_URL=http://localhost:5173
SESSION_DOMAIN=localhost
SESSION_DRIVER=database
SESSION_SAME_SITE=lax
SANCTUM_STATEFUL_DOMAINS=localhost:5173
FILESYSTEM_DISK=public
OPENWEATHER_KEY=
```

## Auth endpoints

| Method | URL | Auth |
|--------|-----|------|
| POST | `/api/auth/register` | No |
| POST | `/api/auth/login` | No |
| POST | `/api/auth/forgot-password` | No |
| POST | `/api/auth/reset-password` | No |
| GET | `/api/auth/email/verify/{id}/{hash}` | Signed URL |
| POST | `/api/auth/logout` | Yes (`auth:sanctum`) |
| GET | `/api/auth/me` | Yes (`auth:sanctum`) |
| POST | `/api/auth/email/resend` | Yes (`auth:sanctum`) |

Protected app routes (wardrobe, bookings, recommendations, AI) require `auth:sanctum` **and** verified email (`verified` middleware).

Primary auth pattern: **session-based Sanctum SPA** (`statefulApi`, CSRF cookie flow). Bearer tokens are also issued as a fallback for API clients.

CSRF (Sanctum): `GET /sanctum/csrf-cookie` — `SANCTUM_STATEFUL_DOMAINS` must include `localhost:5173`.

## Response format

```json
{
  "success": true,
  "message": "Login successful.",
  "data": { "user": { ... } }
}
```

Errors (validation):

```json
{
  "success": false,
  "message": "Invalid credentials.",
  "data": {},
  "errors": { "email": ["..."] }
}
```

## Verify CSRF route

```bash
curl -i http://localhost:8000/sanctum/csrf-cookie
```

Expect HTTP 204/200 with `Set-Cookie` headers.

## Smoke test (register + me)

```bash
curl -c cookies.txt -b cookies.txt http://localhost:8000/sanctum/csrf-cookie
curl -c cookies.txt -b cookies.txt -X POST http://localhost:8000/api/auth/register ^
  -H "Content-Type: application/json" ^
  -H "Accept: application/json" ^
  -d "{\"name\":\"Test User\",\"email\":\"test@example.com\",\"password\":\"password123\",\"password_confirmation\":\"password123\"}"
curl -c cookies.txt -b cookies.txt http://localhost:8000/api/auth/me -H "Accept: application/json"
```

Use `localhost` (not `127.0.0.1`) for both frontend and API when testing cookies.

## Local email testing

Email verification and password reset require outbound mail. By default `.env.example` uses `MAIL_MAILER=log`, which writes messages to `storage/logs/laravel.log` (no real inbox).

To test like a real user (click links in an inbox):

1. Create a free [Mailtrap](https://mailtrap.io) account.
2. Copy sandbox SMTP credentials into your local `backend/.env`:
   ```env
   MAIL_MAILER=smtp
   MAIL_HOST=sandbox.smtp.mailtrap.io
   MAIL_PORT=2525
   MAIL_USERNAME=your_mailtrap_username
   MAIL_PASSWORD=your_mailtrap_password
   MAIL_ENCRYPTION=tls
   ```
3. Run `php artisan config:clear` and trigger registration or forgot-password.

Never commit real Mailtrap credentials; `.env` is gitignored.

## Production HTTPS (TODO)

For production (cross-site SPA + API), enable secure cookies:

```env
SESSION_SECURE_COOKIE=true
SESSION_SAME_SITE=none
```

Use a production mail provider (e.g. Mailgun, Postmark, SES) instead of Mailtrap.
