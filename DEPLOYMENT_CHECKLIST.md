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
