# Kresekin Auth API

Production-ready Laravel 12 REST API for username/password and Google authentication, backed by PostgreSQL, JWT bearer tokens, OpenAPI documentation, and Pest tests.

## Stack

- Laravel 12
- PostgreSQL
- `php-open-source-saver/jwt-auth`
- `google/apiclient`
- `darkaonline/l5-swagger`
- Pest
- Docker Compose for local development only
- Laravel Cloud for production deployment

## API Endpoints

- `POST /api/v1/auth/register`
- `POST /api/v1/auth/login`
- `POST /api/v1/auth/google/register`

Successful auth responses return:

```json
{
  "access_token": "jwt-token",
  "token_type": "Bearer",
  "expires_in": 3600,
  "user": {
    "id": 1,
    "username": "rindi_dev",
    "email": "rindi@example.com",
    "auth_provider": "local"
  }
}
```

## Local Installation

### Without Docker

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan jwt:secret --force
php artisan migrate
php artisan l5-swagger:generate
php artisan serve
```

### With Docker Compose

Make sure Docker Desktop WSL integration is enabled for this distro.

```bash
docker compose up -d
docker compose exec app composer install
docker compose exec app php artisan key:generate
docker compose exec app php artisan jwt:secret --force
docker compose exec app php artisan migrate
docker compose exec app php artisan l5-swagger:generate
docker compose exec app php artisan test
```

Swagger UI is available locally at `http://localhost:8000/api/documentation`.

## Environment Configuration

### Local

Use `.env.example` defaults:

- `DB_HOST=postgres`
- `DB_PORT=5432`
- `DB_DATABASE=kresekin_auth`
- `DB_USERNAME=postgres`
- `DB_PASSWORD=postgres`
- `LOG_CHANNEL=stack`
- `LOG_STACK=stderr`
- `JWT_SECRET` generated with `php artisan jwt:secret --force`
- `GOOGLE_CLIENT_ID` or `GOOGLE_CLIENT_IDS`

### Laravel Cloud Production

Set environment variables in Laravel Cloud:

- `APP_ENV=production`
- `APP_DEBUG=false`
- `APP_URL=<laravel-cloud-app-url>`
- `DB_CONNECTION=pgsql`
- `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`
- `DB_SCHEMA=public`
- `DB_SSLMODE=require` when required by the managed database
- `LOG_CHANNEL=stack`
- `LOG_STACK=stderr`
- `QUEUE_CONNECTION=database` or another managed backend
- `CACHE_STORE=database` or managed cache
- `SESSION_DRIVER=file` unless sessions are moved to another backend
- `JWT_SECRET=<generated secret>`
- `GOOGLE_CLIENT_ID` or `GOOGLE_CLIENT_IDS`
- `L5_SWAGGER_GENERATE_ALWAYS=false`

Production does not use Docker. Laravel Cloud runs the standard Laravel app build/runtime directly.

## Testing

Run:

```bash
php artisan test
```

The test suite covers:

- register success
- register validation errors
- duplicate username and email
- login success
- login failure
- password login rejection for Google-only accounts
- Google success for create and login
- Google token failure
- provider conflict failure
- Swagger generation

## Google Auth Contract

`POST /api/v1/auth/google/register` accepts a Google ID token:

```json
{
  "id_token": "google-id-token"
}
```

The backend verifies the token with Google, validates the audience against configured client IDs, requires a verified email, and auto-generates a unique username for first-time Google users.

## Deployment Notes

- No Docker runtime dependency is required for production.
- Storage remains inside Laravel defaults and cloud-safe paths.
- Logs are configured for stderr-compatible aggregation.
- Auth uses the `api` guard with JWT and remains extendable for refresh-token work later.
