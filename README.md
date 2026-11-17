# Atelier Wood / LivingOS

Atelier Wood is a PHP storefront and operations suite for handcrafted furniture and décor. It includes a searchable catalogue, product configuration, sandbox checkout, customer accounts, and administration for content, quotes, workshop jobs, inventory, and fulfilment.

**Staging only.** This release uses file-backed persistence and sandbox payments. The application rejects production mode. See [production release gates](RELEASE_READINESS.md).

## Run locally

Use PHP 8.4 and Python 3.12 with Pillow and qrcode for the worker-backed features. See the [staging runbook](DIGIOPS_STAGING.md) for PHP extensions and required secrets. Configure local secrets outside Git before testing encrypted storage or checkout. Use disposable data, not a copy of live private storage.

```bash
php -S 127.0.0.1:8080 -t public router.php
```

## Verify and deploy

```bash
bash scripts/full_regression.sh
bash tests/digiops_package.sh
```

The regression suite uses an isolated workspace. The package test simulates the Cloudways directory layout with PHP `putenv()` disabled. GitHub Actions publishes `digiops-release` only on successful pushes to `main`. A successful build does not prove deployment or readiness.

| Guide | Purpose |
| --- | --- |
| [DigiOps staging](DIGIOPS_STAGING.md) | Cloudways setup, deploy, health, and rollback |
| [Release readiness](RELEASE_READINESS.md) | Outstanding production gates |
| [SEO and discoverability](SEO_GEO_READINESS.md) | Feeds, indexing boundaries, and launch checks |
| [Milestone history](MILESTONE_HISTORY.md) | Feature and release history |

Historical audit snapshots and generated reports are available in Git history. Use CI results for the commit under review rather than treating old snapshots as current evidence.
