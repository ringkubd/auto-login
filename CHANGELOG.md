# Changelog

All notable changes to `auto-login` will be documented in this file

## 1.1.0 - 2026-09-29

### Security

- **Tokens are hashed at rest.** `app_token` now stores `sha256(token)`; the
  plaintext is returned to the caller only once. A database leak can no longer
  be replayed.
- **Active accounts only.** Tokens are issued/consumed only for users with
  `active = 1` and `is_delete = 0` (configurable via `require_active`).
- **No user enumeration / no password-hash leakage.** Failures return a generic
  message instead of the raw user row.
- **Session fixation protection.** The session id is regenerated after
  authentication.
- **Rate limiting.** `generate_token` is throttled to 10/min and `auto-login`
  to 20/min.
- **Optional shared secret.** `generate_token` can require an
  `X-AutoLogin-Secret` header for trusted server-to-server calls.
- **Backward compatible.** Legacy plaintext tokens continue to work and are
  transparently upgraded to a hash on first use.
- Added an index on `app_token` for fast lookups.

## 1.0.0 - 201X-XX-XX

- Initial release.
