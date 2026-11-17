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

The public entrypoint resolves the private application from sibling private_html/<app>/app/public/index.php and passes trusted filesystem paths through request-local constants, without requiring PHP `putenv()`. New media is served through validated /media/products/<filename> and /media/passports/<filename> paths; binary uploads stay under private storage.

**Existing-installation warning:** if an earlier Atelier installation already contains state elsewhere, stop and migrate/backup it before enabling the new layout. First-run seeds are created only when the corresponding persistent file does not exist. A deployment must not reset an existing catalog or customer database.

## Set up in DigiOps

1. Sign in to DigiOps and locate the application. Register the app as atelier in the Stage target only if it is not already present.
2. Use the following settings for the Stage Cloudways application:

   | DigiOps field | Value |
   |---|---|
   | Repository | `indigiti/Atelier` |
   | Branch | `main` |
   | Artifact | `digiops-release` |
   | Application URL | `/atelier/` |
   | Public path | `public_html/atelier/` |
   | Private path | `private_html/atelier/` |
   | Health path | `/health` (resolved URL: `https://stage.digiti.in/atelier/health`) |

3. Choose **Check update**. Verify the candidate is a successful **push** build on `main`, its SHA matches the intended commit, and the artifact is not expired. PR jobs validate packaging but do not publish deployable artifacts. If the matching main artifact expired, rebuild the current main commit and verify the new artifact.
4. Before activating, verify DigiOps puts the contents of `public/` and `private/` in the respective target paths, without an extra directory layer. Confirm replacement and rollback never wipe `private_html/atelier/storage/`. The PHP worker must be able to read `app/` and write to `storage/`. Back up any existing state; preserve the encryption key separately.
5. After activation, confirm `/atelier/health` responds with Atelier JSON, `ok: true`, and `environment: staging`. This is a liveness check, not proof that secrets or application data are ready. Check `/atelier/ready` separately; require HTTP 200 and `ok: true` before tester handoff. If it returns 503, inspect the failed checks rather than accepting a green homepage or health probe.
6. Smoke-test the homepage, catalogue search, a direct product URL, static assets, uploaded media (if present), account/admin sign-in, and a sandbox order. Check `/atelier/sitemap.xml`, `/atelier/robots.txt`, `/atelier/llms.txt`, `/atelier/feeds/knowledge.json`, `/atelier/feeds/google-merchant.xml` and authenticated `/atelier/admin/seo`. Confirm HTML carries the staging robots meta and `X-Robots-Tag`, and generated links preserve `/atelier/`.
7. Record the installed commit and artifact ID in DigiOps. Stop tester handoff if readiness or smoke tests fail. If a previous release exists, roll back the release without replacing runtime storage. For a first install, keep the environment restricted until the failure is resolved.

**CI is not proof of an actual server deployment.** A successful remote DigiOps deployment and real-site checks are separate gates.

## Cloudways requirements

- PHP 8.4 with curl, mbstring, GD, DOM, SimpleXML, fileinfo, and sodium or OpenSSL.
- A stable, unique ATELIER_DATA_KEY (base64-encoded 32 bytes) and ATELIER_SANDBOX_WEBHOOK_SECRET, kept only in secure Cloudways/server settings.
- `ATELIER_AGENT_SIGNING_KEY` must be at least 32 characters to pass the current `/ready` gate, even if agent access is not being exercised. Use a separate secret from the data and webhook keys.
- Ensure these environment variables are visible to the web PHP process, not only to the shell. Do not rotate an existing data key as part of a release.
- Restrict staging to testers. For a fresh installation, provision the first administrator via `/atelier/admin/setup` behind access restrictions; use a securely supplied `ATELIER_SETUP_KEY` or the private setup-key file. Never expose the key in the document root, artifact, or logs. Ensure any restrictions still allow DigiOps to reach its health probe.
- Media/visual/QR worker flows invoke `python3` from PHP; verify the web worker can run it and import Pillow and qrcode before testing those features.
- Set `ATELIER_ALLOWED_HOSTS` to the expected hostname allowlist for staging. Set `ATELIER_SENDGRID_API_KEY` and `ATELIER_MAIL_FROM` only if enabling outbound email.
- The web server must support PHP routing to public_html/<app>/index.php and preserve SCRIPT_NAME=/atelier/index.php when the app is hosted under /atelier.
- If email is enabled, schedule `php scripts/send_notification_queue.php 20` from the private `app/` directory in the application environment. Verify delivery and monitor failed or abandoned attempts.
- Do not enable APP_ENV=production: the application rejects file-mode production until the PostgreSQL migration is certified.

## Troubleshooting

- An HTTP 500 naming undefined `putenv()` in the public entry point indicates an old package. Select a successful `main` artifact containing the wrapper fix. Do not enable `putenv()` or delete runtime storage to work around it.
- A responding `/health` with a failing `/ready` requires resolving the reported readiness checks before handoff.

## Search and AI launch gates

Staging must remain noindex. Merchant feed XML, structured data and AI feeds do not prove Merchant Center acceptance or actual Google/AI indexing. Independently verify after a properly approved production deployment.
