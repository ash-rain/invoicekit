# InvoiceKit

EU freelancer invoicing + time tracking SaaS built with Laravel 12, Livewire 4, and MySQL.

## Overview

InvoiceKit helps EU-based freelancers manage clients, track billable time, generate VAT-compliant invoices, and accept payments via Stripe. It handles the complexity of EU VAT rules including reverse charge, OSS, and exempt scenarios automatically.

## Features

- **Time Tracking** — Start/stop timer per project, manual entry, monthly reports
- **Client Management** — Store client details with country, VAT number, and preferred currency
- **Project Management** — Hourly rate per project, multi-currency support, archive/active status
- **EU VAT Engine** — Automatic VAT calculation: standard rate, reverse charge (B2B), OSS (B2C EU consumers), exempt (non-EU)
- **Invoice Generation** — PDF invoices via DomPDF, invoice numbering, draft/sent/paid/overdue lifecycle
- **Multi-currency** — EUR, USD, BGN, RON, PLN, CZK, HUF
- **Stripe Payments** — Payment links on invoices for online collection
- **Multi-tenant** — Per-user data isolation via stancl/tenancy

## Tech Stack

| Layer | Technology |
|-------|------------|
| Framework | Laravel 12 |
| Frontend | Livewire 4 |
| Database | MySQL (DigitalOcean Managed) |
| Cache/Queue | Redis |
| PDF | barryvdh/laravel-dompdf |
| Payments | Stripe |
| Tenancy | stancl/tenancy |
| Container | Docker (PHP-FPM + Nginx) |

## EU VAT Rules

| Scenario | VAT Treatment |
|----------|--------------|
| Same country | Local VAT rate applied |
| EU business with VAT number | Reverse charge (0%, buyer accounts for VAT) |
| EU consumer (no VAT number) | Seller's rate or OSS rate |
| Non-EU buyer | Exempt (0%) |

### Supported VAT Rates

BG 20% · DE 19% · FR 20% · RO 19% · PL 23% · CZ 21% · IT 22% · ES 21% · NL 21% · PT 23% · AT 20% · BE 21% · HR 25% · HU 27% · SE 25%

## Getting Started

```bash
cp .env.example .env
docker-compose up -d
docker-compose exec app php artisan key:generate
docker-compose exec app php artisan migrate
```

See [DEPLOY.md](DEPLOY.md) for production and staging deployment on DigitalOcean Kubernetes.

## Environment Variables

See `.env.example` for all required variables. Key additions over standard Laravel:

- `STRIPE_KEY` / `STRIPE_SECRET` — Stripe API keys
- `APP_CURRENCY` — Default currency (default: `EUR`)

## Supported Currencies

EUR, USD, BGN, RON, PLN, CZK, HUF

## Supported Languages

English (`en`), Bulgarian (`bg`)

## License

MIT
