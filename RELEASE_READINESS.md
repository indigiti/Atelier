# Atelier Release Hardening — 2026-11-08

Status: **STAGING ONLY**. Production is explicitly blocked in `src/bootstrap.php`.

## Implemented in this branch

- Regression suite executes in a disposable workspace and never copies application private data.
- Every regression entry point rejects direct execution outside the isolated sandbox.
- JSON reads reject corrupt/unreadable existing stores; encrypted mutations reject invalid envelopes.
- Payment webhook retries reconcile recorded payment attempts with order state; contradictory replay is rejected.
- Payment webhooks no longer clear the browser session cart; sandbox browser success clears it explicitly.
- Checkout idempotency is rechecked inside the locked order write.
- Merchant XML declaration/newline corrected and XML parsing regression added.
- Consultation enquiries persist encrypted, have validation and throttling, and are reviewable in Admin OS.
- Encrypted transactional email outbox, SendGrid provider adapter, retryable CLI delivery and host allowlisting.
- Automated GitHub Actions PHP 8.4 / Python 3.12 isolation regression.

## Still blocking production certification

- **Transactional inventory reservations:** stock and orders require a single authoritative transactional backend; no concurrency-safe stock decrement is claimed.
- **PostgreSQL migration:** the current file-mode domain modules remain authoritative. Build and rehearse data migration, compare read/write parity and rollback under live-like load.
- **Real payment/refunds:** only sandbox verification exists. Provider keys, webhook and refund reconciliation, chargebacks and settlement testing are outstanding.
- **Transactional mail service:** configure `ATELIER_SENDGRID_API_KEY`, `ATELIER_MAIL_FROM`, `ATELIER_ALLOWED_HOSTS` and run the scheduled worker; verify a real delivered reset email.
- **Legal/commercial:** complete production rights clearance, price/tax and product specification review, refunds/privacy/terms and shipping-policy signoff.
- **Performance and visual certification:** load tests, browsers on real iOS/Android devices, keyboard/screen-reader checks and recovery drills.

## Operating rules

Run `bash scripts/full_regression.sh` only. Do not invoke individual tests directly. Configure a unique `ATELIER_DATA_KEY` before storing user data. Store all secrets outside Git. Do not bypass the production guard to deploy this release. Keep media and private storage backups prior to any environment migration.

**Next milestone:** PostgreSQL-authoritative A4, with inventory reservations and transactional payment/order transitions implemented in one database transaction. This must be completed before enabling real checkout.
