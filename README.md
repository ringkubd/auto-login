# Auto Login

Single sign-on across multiple Laravel applications from one central app.

A user proves their credentials to the central app, receives a token, and that
token can be handed to any sibling application to establish a session without
re-entering the password.

## Installation

```bash
composer require anwar/auto-login
php artisan vendor:publish --tag=config --provider="Anwar\AutoLogin\AutoLoginServiceProvider"
php artisan migrate
```

The migration adds two columns to your `users` table:

- `app_token` — stores the **SHA-256 hash** of the current token
- `app_reference` — records where the token was issued

## Usage

### 1. Obtain a token (central app)

```http
POST /auto-login/generate_token
Content-Type: application/json

{ "email": "user@example.com", "password": "secret" }
```

Response:

```json
{ "status": true, "app_token": "<plaintext-token>" }
```

The plaintext token is returned **once**. Only its hash is stored.

### 2. Consume a token (sibling app)

```
GET /auto-login?app_token=<plaintext-token>
```

On success the user is authenticated and redirected to
`config('autologin.redirect_to')` (default `/`).

## Security

This package is designed to be safe by default:

- **Hashed at rest** — only `sha256(token)` is stored. A database leak cannot be
  replayed.
- **Active accounts only** — tokens are issued/consumed only for `active = 1`
  and `is_delete = 0` users (configurable via `require_active`).
- **No user enumeration** — failures return a generic message.
- **Session fixation protection** — the session id is regenerated on login.
- **Rate limited** — `generate_token` (10/min) and `auto-login` (20/min).
- **Optional shared secret** — set `AUTOLOGIN_SHARED_SECRET` (or
  `shared_secret` in config) to require an `X-AutoLogin-Secret` header on
  `generate_token` for server-to-server calls.
- **Legacy migration** — existing plaintext tokens keep working and are
  transparently upgraded to a hash on first use.

### Configuration

```php
// config/autologin.php
return [
    'users_table'   => 'users',
    'redirect_to'   => '/',
    'require_active' => true,
    'shared_secret' => env('AUTOLOGIN_SHARED_SECRET', ''),
    'token_length'  => 64,
];
```

### Important

- Always serve `generate_token` and `auto-login` over **HTTPS**.
- Rotate a user's token by calling `generate_token` again (invalidates the old
  one).
- The browser-facing `auto-login` route must **not** be CSRF-exempt; only the
  machine-to-machine `generate_token` call needs to be in
  `VerifyCsrfToken::$except`.

## Testing

```bash
composer test
```

## Changelog

See [CHANGELOG](CHANGELOG.md).

## License

The MIT License (MIT). See [LICENSE](LICENSE.md).
