# InvoiceKit — Build Plan

## What
EU-compliant invoicing + time tracking SaaS for European freelancers.
One tool: track time → generate legally compliant VAT invoice → get paid.

## Stack
- Laravel 12 + Livewire 3 + Tailwind CSS
- PostgreSQL (multi-tenant via stancl/tenancy)
- DomPDF for invoice PDF generation
- Stripe for subscriptions
- Hetzner EU hosting

## Pricing
| Plan | Price | Limits |
|------|-------|--------|
| Free | €0 | 3 clients, 5 invoices/mo |
| Starter | €15/mo | Unlimited clients, 20 invoices/mo |
| Pro | €29/mo | Unlimited everything, recurring invoices, client portal |

---

## Phase 0 — Laravel Scaffold (Week 1)
- [x] `composer create-project laravel/laravel .`
- [x] Install: livewire/livewire, stancl/tenancy, barryvdh/laravel-dompdf, stripe/stripe-php
- [ ] Configure PostgreSQL connection
- [ ] Set up multi-tenancy (one DB per workspace, subdomain routing)
- [ ] Auth: Laravel Breeze (email + password)
- [ ] Base layout: sidebar nav + Tailwind

## Phase 1 — Core Models & Migrations (Week 1–2)
- [x] `users` table (standard)
- [x] `clients` table: id, user_id, name, email, address, country (ISO 2), vat_number (nullable), currency, timestamps
- [x] `projects` table: id, user_id, client_id, name, hourly_rate, currency, status (active/archived), timestamps
- [x] `time_entries` table: id, user_id, project_id, description, started_at, stopped_at (nullable), duration_minutes (nullable), timestamps
- [x] `invoices` table: id, user_id, client_id, invoice_number, status (draft/sent/paid/overdue), issue_date, due_date, currency, subtotal, vat_rate, vat_amount, total, notes, paid_at, timestamps
- [x] `invoice_items` table: id, invoice_id, description, quantity, unit_price, vat_rate, total, timestamps
- [x] Eloquent models with relationships + casts

## Phase 2 — EU VAT Engine (Week 2)
- [x] `app/Services/EuVatService.php`
  - `calculateVat(sellerCountry, buyerCountry, buyerHasVat, amount)` → `[rate, amount, type]`
  - Types: `standard` | `reverse_charge` | `oss` | `exempt`
  - Rules:
    - Same country → apply local VAT rate
    - EU business with VAT number → reverse charge (0%)
    - EU consumer (no VAT number) → OSS rules (seller's rate)
    - Non-EU buyer → 0% exempt
  - VAT rates hardcoded: BG 20%, DE 19%, FR 20%, RO 19%, PL 23%, CZ 21%, IT 22%, ES 21%, NL 21%, PT 23%, AT 20%, BE 21%, HR 25%, HU 27%, SE 25%
- [ ] Unit tests for all VAT rule combinations

## Phase 3 — Timer (Week 2–3)
- [x] Livewire component: `ActiveTimer`
  - Select project from dropdown
  - Start/stop button with running clock display
  - Description field
  - Auto-saves entry on stop
- [ ] Time entries list: log per day, total hours per project
- [ ] Manual time entry form (for past work)
- [ ] Weekly summary: hours per project, total hours

## Phase 4 — Client Management (Week 3)
- [x] `Clients/ClientList` Livewire: table with search, country flag, VAT number, action buttons
- [ ] `Clients/CreateEditClient` modal form
- [ ] Country selector with ISO 2 codes + EU VAT number validation format check
- [ ] Currency per client (EUR, USD, BGN, RON, PLN, CZK, HUF)

## Phase 5 — Invoice Builder (Week 3–4)
- [ ] `Invoices/CreateInvoice` Livewire:
  - Select client (auto-fills VAT info)
  - Add line items (description, quantity, unit price)
  - VAT auto-calculated per EuVatService
  - Preview totals (subtotal, VAT, total)
  - Issue date + due date
  - Notes field
- [ ] Invoice number auto-generation (format: INV-2026-0001)
- [x] `Invoices/InvoiceList` Livewire: sortable table with status badges
- [ ] Status transitions: draft → sent → paid / overdue
- [ ] Overdue auto-flag via scheduled job (daily)

## Phase 6 — PDF Generation (Week 4)
- [ ] `resources/views/invoices/pdf.blade.php`
  - Required EU fields: seller info + VAT number, buyer info + VAT number
  - Tax base, VAT amount, total clearly labeled
  - Reverse charge notice when applicable ("VAT reverse charge — buyer accounts for VAT")
  - OSS notice when applicable
  - Issue date, due date, invoice number, payment terms
- [x] `InvoiceController::pdf()` → DomPDF → download or inline view
- [ ] BG + EN language toggles on PDF
- [ ] BGN + EUR + USD currency formatting

## Phase 7 — Payment Tracking + Reminders (Week 5)
- [ ] Mark invoice as paid (manual, with payment date)
- [ ] Reminder emails: queued Laravel jobs
  - 3 days before due date
  - On due date
  - 7 days overdue
- [ ] Email template: clean, professional, includes invoice PDF attachment
- [x] Dashboard widget: overdue invoices list + total outstanding

## Phase 8 — Stripe Subscriptions (Week 5–6)
- [ ] Stripe Checkout integration (Laravel Cashier)
- [ ] Plan: Free / Starter €15 / Pro €29
- [ ] Usage enforcement: check invoice count, client count against plan limits
- [ ] Upgrade prompt when limit hit
- [ ] Billing portal link (Stripe Customer Portal)

## Phase 9 — Polish & Launch Prep (Week 6)
- [ ] Landing page: headline, features, pricing, FAQ
  - Hero: "Invoice like a European. Track time like a pro."
  - EU compliance angle front and center
- [ ] Onboarding flow: 3-step wizard (company info → first client → first invoice)
- [ ] Email verification + welcome email
- [ ] Basic SEO: meta tags, sitemap, robots.txt
- [ ] Error pages: 404, 500
- [ ] Cookie consent (ironically — use a simple first-party only banner)
- [ ] Privacy policy + terms of service

---

## Post-Launch Roadmap
- v1.1: Recurring invoices (monthly auto-generation)
- v1.2: Client portal (clients view + download their invoices)
- v1.3: Expense tracking
- v1.4: Peppol e-invoicing support (B2G EU requirement growing)
- v2.0: Multi-language invoices (BG, EN, DE, RO, PL)

---

## Revenue Projection
| Period | Users | Avg Price | MRR |
|--------|-------|-----------|-----|
| Month 3 | 80 | €19 | €1,520 |
| Month 6 | 300 | €22 | €6,600 |
| Month 12 | 700 | €22 | €15,400 |

## Distribution
- CEE freelancer Facebook groups (BG, RO, PL dev communities)
- ProductHunt launch
- "GDPR-compliant invoicing" SEO content
- Direct outreach to EU freelancers currently using Toggl + Excel combo
