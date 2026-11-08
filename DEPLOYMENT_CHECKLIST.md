# Deployment Checklist — v0.9.8.1 Marketplace Luxe

- [ ] Extract into a new release directory.
- [ ] Point Nginx/Apache document root to `/public`.
- [ ] Configure a unique 32-byte `ATELIER_DATA_KEY` outside source control.
- [ ] Configure `ATELIER_SANDBOX_WEBHOOK_SECRET` for staging payment verification.
- [ ] Configure a 32+ character `ATELIER_AGENT_SIGNING_KEY` where Agent APIs are enabled.
- [ ] Confirm private storage is not web-addressable.
- [ ] Run PHP syntax checks and `scripts/full_regression.sh`.
- [ ] Confirm `/ready` returns HTTP 200 with no failed checks.
- [ ] Verify home/shop/PDP/Sacred Spaces at desktop, tablet and mobile widths.
- [ ] Verify search, mega menu, mobile menu, Filter, Sort, room filter and pincode interaction.
- [ ] Confirm HTTPS and Secure cookies at the edge.
- [ ] Keep live payments disabled until production gateway/webhook validation is complete.
- [ ] Preserve an encrypted backup of the previous staging release before upgrade.
- [ ] Do not begin PostgreSQL cutover until the v0.9.9 shadow migration passes parity gates.

## Security & operational prerequisites added in release hardening
- [ ] Set ATELIER_ALLOWED_HOSTS to an exact comma-separated allowlist of expected application hosts.
- [ ] Configure ATELIER_MAIL_FROM and ATELIER_SENDGRID_API_KEY only in server secrets when email delivery is enabled.
- [ ] Schedule `php scripts/send_notification_queue.php 20` every minute in the application environment; monitor failures and abandoned delivery attempts.
- [ ] Never use live application storage for regression tests. Always invoke `bash scripts/full_regression.sh`, which creates a disposable test workspace.
- [ ] Exercise payment event retries and recovery after a simulated crash between webhook state and order state.
- [ ] Run PostgreSQL cutover / payment-provider validation as separate production-release projects. The current bootstrap intentionally rejects production mode.
