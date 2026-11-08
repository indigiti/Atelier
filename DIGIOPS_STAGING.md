# Atelier — DigiOps / Cloudways staging deployment

Status: **packaging-ready staging candidate; not a production release**.

## Release lineage

- Source repository: indigiti/Atelier, branch main
- CI workflow: .github/workflows/ci.yml
- Artifact name: digiops-release; uploaded only after tests pass on a main-branch push
- Package format: public/ and private/app/ (plus RELEASE.json at the artifact root)
- App ID: use the registered application ID. Never overwrite an unrelated app.

Create the artifact with bash scripts/package_digiops_release.sh and validate it with bash tests/digiops_package.sh.

## Folder mapping

| Location | Purpose | Persistence |
|---|---|---|
| public_html/<app>/ | Front controller, .htaccess, stock media/assets | Replaced on deployment |
| private_html/<app>/app/ | PHP source, worker code, templates, first-run seed data | Replaceable code only |
| private_html/<app>/storage/ | Catalog, CMS edits, encrypted records, uploaded media/QR, audit | Persistent; never packaged |

The public entrypoint resolves the private application from sibling private_html/<app>/app/public/index.php and configures ATELIER_STORAGE_ROOT and ATELIER_PUBLIC_ROOT. New media is served through validated /media/products/<filename> and /media/passports/<filename> paths; binary uploads stay under private storage.

**Existing-installation warning:** if an earlier Atelier installation already contains state elsewhere, stop and migrate/backup it before enabling the new layout. First-run seeds are created only when the corresponding persistent file does not exist. A deployment must not reset an existing catalog or customer database.

## Set up in DigiOps

1. Sign in to DigiOps and locate the application. Register the app as atelier in the Stage target only if it is not already present.
2. Configure repository indigiti/Atelier, branch main, artifact digiops-release, route /atelier/ (for ID atelier), and health path /health.
3. After CI passes, choose Check update. Verify exact SHA, workflow/run and artifact IDs before deploying.
4. DigiOps must use the split public/private release and preserve the persistent storage area. Back up any existing application state and verify rollback.
5. Verify /health, /sitemap.xml, /robots.txt, /llms.txt, /feeds/knowledge.json, /feeds/google-merchant.xml, /admin/seo and several product pages.
6. Confirm public HTML has noindex via meta and X-Robots-Tag. Confirm static and uploaded product images load. Recheck catalogue and account records after deployment.
7. Record installed artifact identity, SHA and health results from DigiOps. Roll back on failure.

**CI is not proof of an actual server deployment.** A successful remote DigiOps deployment and real-site checks are separate gates.

## Cloudways requirements

- PHP 8.4 with curl, GD, DOM, SimpleXML, fileinfo, and sodium or OpenSSL.
- A stable, unique ATELIER_DATA_KEY (base64-encoded 32 bytes) and ATELIER_SANDBOX_WEBHOOK_SECRET, kept only in secure Cloudways/server settings.
- Optional ATELIER_AGENT_SIGNING_KEY, ATELIER_ALLOWED_HOSTS, ATELIER_SENDGRID_API_KEY and ATELIER_MAIL_FROM.
- The web server must support PHP routing to public_html/<app>/index.php and preserve SCRIPT_NAME=/atelier/index.php when the app is hosted under /atelier.
- Schedule notification CLI processing only when email is configured.
- Do not enable APP_ENV=production: the application rejects file-mode production until the PostgreSQL migration is certified.

## Search and AI launch gates

Staging must remain noindex. Merchant feed XML, structured data and AI feeds do not prove Merchant Center acceptance or actual Google/AI indexing. Independently verify after a properly approved production deployment.
