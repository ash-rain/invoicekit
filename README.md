# InvoiceKit

EU freelancer invoicing + time tracking SaaS built with Laravel 12, Livewire 4, and PostgreSQL.

## Overview

InvoiceKit helps EU-based freelancers manage clients, track billable time, generate VAT-compliant invoices, export Peppol/UBL XML, and manage subscriptions via Stripe. It handles the complexity of EU VAT rules — reverse charge, OSS, and small-business exemptions — automatically.

## Features

- **Time Tracking** — Live timer, manual entry, weekly summary, per-project earnings
- **Client Management** — EU VAT number validation, per-client currency and language defaults
- **Project Management** — Hourly rate, multi-currency, archive/restore
- **EU VAT Engine** — Standard rate, reverse charge (B2B), OSS (B2C), exempt (non-EU), small-business exemption for all 27 EU countries
- **Invoice Generation** — PDF (DomPDF), UBL 2.1 / Peppol BIS Billing 3.0 XML, sequential numbering, full lifecycle
- **Recurring Invoices** — Weekly / monthly / quarterly / yearly auto-generation
- **Client Portal** — Tokenised public invoice links (no login required)
- **Expense Tracking** — Receipt upload, CSV export, category filters
- **Billing** — Stripe Checkout + Customer Portal, webhook-driven subscription sync, 14-day Pro trial
- **Notifications** — In-app bell, web push (VAPID), invoice reminder emails with PDF attachment
- **Multi-currency** — EUR, USD, BGN, RON, PLN, CZK, HUF
- **24 EU languages** — Full UI and PDF localisation

## Tech Stack

| Layer | Technology |
|-------|------------|
| Framework | Laravel 12 |
| Frontend | Livewire 4 + Alpine.js + Tailwind CSS 3 |
| Database | PostgreSQL 16 |
| Cache / Queue | Redis 7 |
| File Storage | MinIO (S3-compatible) |
| PDF | barryvdh/laravel-dompdf |
| Payments | Stripe (raw `stripe/stripe-php` v19) |
| Container | Docker (PHP-FPM + Nginx) |

---

## Local Development

### Prerequisites

- Docker Desktop (or Docker Engine + Compose)
- Node.js ≥ 20 + npm (for frontend assets)

### 1 — Clone and configure

```bash
git clone https://github.com/your-org/invoicekit.git
cd invoicekit
cp .env.example .env
```

Edit `.env` and set at minimum:

```dotenv
APP_URL=http://localhost:8008

# Stripe (optional for local — leave blank to skip billing features)
STRIPE_SECRET_KEY=sk_test_...
STRIPE_PUBLISHABLE_KEY=pk_test_...
STRIPE_WEBHOOK_SECRET=whsec_...
STRIPE_PRO_PRICE_ID=price_...
STRIPE_STARTER_PRICE_ID=price_...
```

All other defaults work out of the box with Docker.

### 2 — Start containers

```bash
docker compose up -d
```

This starts: `app` (PHP-FPM), `nginx` (port **8008**), `postgres` (port **5432**), `redis` (port **6379**), `minio` (port **9002**, console **9003**), and `worker` (queue).

### 3 — Install dependencies and initialise

```bash
docker compose exec app composer install
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate --seed
```

### 4 — Create the MinIO bucket

```bash
docker compose exec app php artisan storage:minio-init
```

Or manually: open the MinIO console at **http://localhost:9003** (user `invoicekit` / password `secret123456`), create a bucket named `invoicekit`, and set its policy to public.

### 5 — Build frontend assets

```bash
npm install
npm run build
```

For hot-reload during development, run `npm run dev` in a separate terminal (or use `composer run dev` to start all processes together).

### 6 — Open the app

Visit **http://localhost:8008** and register an account. The onboarding wizard runs on first login.

---

## Running Tests

```bash
# All feature tests
docker compose exec app php artisan test --compact tests/Feature/

# Single file
docker compose exec app php artisan test --compact tests/Feature/InvoiceTest.php

# Filter by name
docker compose exec app php artisan test --compact --filter=test_user_can_create_invoice
```

Tests use an in-memory SQLite database and do not touch the development PostgreSQL instance.

---

## Useful Commands

| Command | Purpose |
|---------|---------|
| `php artisan tinker` | Interactive REPL in app context |
| `php artisan route:list --except-vendor` | List all application routes |
| `php artisan queue:work` | Process queued jobs (worker container handles this in Docker) |
| `php artisan webpush:vapid` | Generate VAPID keys for web push notifications |
| `vendor/bin/pint` | Fix PHP code style |
| `composer run dev` | Start server + queue + logs + Vite concurrently |

---

## Stripe Webhooks (local)

Use the [Stripe CLI](https://stripe.com/docs/stripe-cli) to forward webhook events to your local instance:

```bash
stripe listen --forward-to http://localhost:8008/billing/webhook
```

Copy the printed signing secret into `.env` as `STRIPE_WEBHOOK_SECRET`.

---

## Environment Variables

See [`.env.example`](.env.example) for all variables. Key groups:

| Group | Variables |
|-------|-----------|
| App | `APP_KEY`, `APP_URL`, `APP_CURRENCY` |
| Database | `DB_HOST`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` |
| Stripe | `STRIPE_SECRET_KEY`, `STRIPE_PUBLISHABLE_KEY`, `STRIPE_WEBHOOK_SECRET`, `STRIPE_PRO_PRICE_ID`, `STRIPE_STARTER_PRICE_ID` |
| MinIO | `MINIO_ACCESS_KEY`, `MINIO_SECRET_KEY`, `MINIO_ENDPOINT`, `MINIO_BUCKET`, `MINIO_PUBLIC_URL` |
| Web Push | `VAPID_PUBLIC_KEY`, `VAPID_PRIVATE_KEY`, `VAPID_SUBSCRIBER_EMAIL` |
| Mail | `MAIL_MAILER`, `MAIL_HOST`, `MAIL_FROM_ADDRESS` |

---

## EU VAT Rules

| Scenario | VAT Treatment |
|----------|---------------|
| Same country | Local VAT rate |
| EU B2B — buyer has VAT number | Reverse charge (0%) |
| EU B2C — buyer has no VAT number | Seller's rate (OSS) |
| Non-EU buyer | Exempt (0%) |
| Seller is VAT-exempt small business | Exempt (0%) with legal notice |

**Supported rates**: BG 20% · DE 19% · FR 20% · RO 19% · PL 23% · CZ 21% · IT 22% · ES 21% · NL 21% · PT 23% · AT 20% · BE 21% · HR 25% · HU 27% · SE 25%

---

See [FEATURES.md](FEATURES.md) for a detailed feature reference and [DEPLOY.md](DEPLOY.md) for production/staging deployment on Kubernetes.

## License

MIT
