# InvoiceKit — Feature Reference

Implemented features as of March 2026.

---

## Authentication & Onboarding

- Email/password registration with password confirmation
- Login with post-auth onboarding redirect (redirects to `/onboarding` if `onboarding_completed = false`)
- Password reset (email link)
- Email verification (`MustVerifyEmail`)
- Password confirmation gate for sensitive actions
- Password change in settings
- Account deletion with `current_password` confirmation
- **Onboarding wizard** (3 steps, skips if completed):
  - Step 1: company name + country (33 countries)
  - Step 2: first client — name, email, country, currency
  - Step 3: first project — name + hourly rate (skippable)

---

## Dashboard

- **Tracked This Month** — total billable hours for the current calendar month
- **Unpaid Invoices** — count and summed total of `sent` + `overdue` invoices
- **Overdue** — count with red pulsing indicator when > 0
- Overdue invoices table (up to 10): invoice number, client, due date, days overdue, total

---

## Client Management

**Fields**: name, email, address, country (41 countries), VAT number, currency, default invoice language

- List with name search + pagination (15/page)
- Create, edit, delete (with confirmation)
- Per-country EU VAT number format validation (regex for all 26 EU member states)
- Plan gate on creation: Free plan capped at 3 clients
- Client's `default_language` pre-fills invoice language on invoice creation

---

## Project Management

**Fields**: name, linked client (optional), hourly rate, currency, status (active/archived)

- List with search + active/archived tab switcher
- Create, edit, delete
- Archive and restore
- Project detail page: total hours tracked, total earnings, paginated time entries list (20/page), delete individual entries
- Auto-fills project currency from client when client is selected on create

---

## Time Tracking

**Live timer** (`ActiveTimer`):
- Start timer against a selected project; persists across page loads
- Stop records `stopped_at` and calculates `duration_minutes`
- Elapsed time display (HH:MM:SS) ticks via Livewire polling

**Manual entry**: date + start time + end time + optional description + project

**Entry list**: all completed entries grouped by date; totals-by-project sidebar sorted by hours

**Weekly summary**: navigable week view (prev/next), entries grouped by project, total hours and entry counts per week

---

## Invoices

**Statuses**: `draft` → `sent` → `paid` (or `overdue`)

**Fields**: invoice number, issue date, due date, currency, language, line items, notes, VAT type, subtotal, VAT amount, total, `vat_exempt_applied`, `vat_exempt_notice`

**Invoice numbering**: auto-generated `INV-{YEAR}-{NNNN}`, sequential per user per year, editable before save

**Line items**: description, quantity (decimal), unit price; VAT recalculated live on every change

**Actions**:
- Create, edit (draft only), view, delete
- Mark Sent, Mark Paid (sets `paid_at`, fires `InvoicePaidNotification`)
- Download PDF
- Plan gate: creation blocked and redirects to billing when monthly invoice limit is reached

**Per-invoice VAT exempt override**: when the company is VAT-exempt, a Pro-accessible checkbox allows charging VAT on a one-off basis (e.g. for a cross-border supply outside the exemption)

---

## EU VAT Engine

Automatic VAT calculation with the following rules (applied in priority order):

| Scenario | Treatment |
|----------|-----------|
| Seller is VAT-exempt small business | 0%, type `vat_exempt` |
| Same country | Seller country rate, type `standard` |
| Cross-border EU B2B (buyer has VAT number) | 0%, type `reverse_charge` |
| Cross-border EU B2C (buyer has no VAT number) | Seller's country rate, type `oss` |
| Non-EU buyer | 0%, type `exempt` |

**Supported country rates**: BG 20% · DE 19% · FR 20% · RO 19% · PL 23% · CZ 21% · IT 22% · ES 21% · NL 21% · PT 23% · AT 20% · BE 21% · HR 25% · HU 27% · SE 25%

---

## VAT Exempt Mode (Small Business — All EU Countries)

**Config** (`config/vat_exemptions.php`): all 27 EU countries keyed by ISO-2 code. Each entry includes `available` flag, `threshold_amount`, `threshold_currency`, `threshold_eur_approx`, `legal_basis`, `invoice_notice_local` (native language), `invoice_notice_en`. Spain (`ES`) is marked `available: false` with an explanation.

**`VatExemptionService`**: `getExemptionForCountry()`, `isExemptionAvailable()`, `getInvoiceNotice()`. Reads exclusively from config.

**Company settings**: `vat_exempt` toggle, `vat_exempt_reason` (free text), `vat_exempt_notice_language` (`local` or `en`)

**Invoice behaviour**:
- When active: `vat_exempt_applied = true` and `vat_exempt_notice` text snapshot stored on the invoice at creation
- Yellow banner shown in invoice builder
- Per-invoice override checkbox bypasses exemption for a single invoice

**PDF**: VAT row hidden; legal notice printed at the bottom in a visually distinct bordered block

---

## Settings

Three-tab settings page:

**Profile tab**: display name, tagline, website, phone, profile photo (stored in S3/MinIO), locale/language preference

**Business tab**: company name, full address, VAT number, registration number, bank name, IBAN, BIC

**Invoicing tab**: default currency, default payment terms, default invoice notes, invoice logo upload, VAT exemption toggle + reason + notice language. Country-aware info panel shows threshold and legal basis for the company's country.

---

## Internationalisation

- **24 EU languages**: `bg cs da de el en es et fi fr ga hr hu it lt lv mt nl pl pt ro sk sl sv`
- All UI strings use `__()`; translation files at `resources/lang/{locale}.json`
- `locale` column on `users` table stores the user's preferred language
- `SetLocale` middleware resolves locale: DB preference → session → app default
- Language switcher in Profile settings (dropdown with native language names and flags)
- Invoice PDFs rendered in the invoice's own language (independent of UI locale), defaulting to client's `default_language`
- CI test (`LocaleCompletionTest`) asserts every key in `en.json` exists in all other locale files

---

## PDF Invoice Generation

Generated via DomPDF (DejaVu Sans font for full Unicode / multi-language support):

- **Header**: brand + "INVOICE" + invoice number
- **PAID stamp**: diagonal green overlay on paid invoices
- **From block**: company name, full address, VAT number, registration number, bank IBAN
- **Bill To block**: client name, address, country, email, VAT number
- **Dates**: issue date, due date, payment date (if paid)
- **Line items table**: description, qty, unit price, amount
- **VAT notice**: colour-coded box — green (exempt with stored legal text), amber (reverse charge `Art. 196 EU VAT Directive`), blue (OSS)
- **Totals**: tax base (subtotal), VAT with rate %, total due
- **Notes** section (if present)
- **Footer**: "Generated by InvoiceKit" + generation date
- Also attached to invoice reminder emails

---

## Notifications & Emails

**In-app notification bell**: latest 20 database notifications, unread count badge, mark one/all as read

**Web push notifications** (via `laravel-notification-channels/webpush`):
- Subscribe/unsubscribe: `POST /push-subscriptions`, `DELETE /push-subscriptions`

**`InvoicePaidNotification`**: triggered on Mark Paid — web push + database notification

**`InvoiceReminderNotification`** (dispatched by `SendInvoiceReminder` job):
- Three types: `due_soon`, `due_today`, `overdue`
- Channels: email + web push + database
- Email includes the invoice PDF as an attachment

---

## Plan / Subscription Limits

| Feature | Free | Starter (€9/mo) | Pro (€29/mo) |
|---------|------|-----------------|--------------|
| Clients | 3 | Unlimited | Unlimited |
| Invoices/month | 5 | 20 | Unlimited |

- Limits enforced at creation time with inline error (clients) or redirect to billing (invoices)
- Billing page shows current plan badge, client + invoice usage counts, upgrade cards
- `User::isFree()`, `isStarter()`, `isPro()` helpers

---

## Multi-Currency

**Supported**: EUR, USD, BGN, RON, PLN, CZK, HUF

- Default currency per company, per client, per project, per invoice
- Auto-applied: selecting a client on invoice create fills the invoice currency from the client's preference
- `formatCurrency()` helper renders correct symbol/suffix (€, $, лв., RON, zł, Kč, Ft)

---

## Recurring Invoices

**Fields**: recurrence interval (`weekly`, `monthly`, `quarterly`, `yearly`), `next_issue_date`, `recurring_ends_at` (optional)

- **Make Recurring** button on any draft or sent invoice
- Configure interval, start date, and optional end date via modal dialog
- `GenerateRecurringInvoices` Artisan command: clones due recurring invoices, advances `next_issue_date`, copies all line items and VAT settings
- Scheduled daily via `routes/console.php`
- Cancel recurrence resets `is_recurring` and clears schedule fields
- Invoice list badges indicate recurring invoices

---

## Client Portal

**Tokenised public URLs** — no login required for clients:

- `InvoiceAccessToken` model: UUID token, `expires_at` (30 days), belongs to an invoice
- `GET /portal/{token}` — view invoice with full line items, totals, VAT notice
- `GET /portal/{token}/pdf` — download PDF directly
- **Generate portal link** action on invoice detail page; copies URL to clipboard
- Expired/invalid tokens return 404
- Portal view styled identically to the logged-in invoice view

---

## Expense Tracking

**Fields**: date, description, amount, currency, category (10 categories), vendor, receipt (file upload), project (optional link)

**Categories**: `office_supplies`, `software`, `travel`, `meals`, `utilities`, `marketing`, `equipment`, `professional_services`, `rent`, `other`

- List with date-range filter, category filter, search, pagination (15/page)
- Create, edit, delete
- Receipt upload (PDF or image, max 10 MB) stored in S3/MinIO
- **CSV export** with active filters applied — downloads as `expenses-{date}.csv`
- Monthly total summary card
- Plan gate: expense tracking requires Starter or Pro

---

## Peppol / e-Invoicing (UBL 2.1)

**Standard**: UBL 2.1 / Peppol BIS Billing 3.0 (`urn:cen.eu:en16931:2017#compliant#urn:fdc:peppol.eu:2017:poacc:billing:3.0`)

- `UblXmlService`: generates fully valid UBL 2.1 XML using PHP `DOMDocument`
- **BT/BG coverage**: BT-1 ID, BT-2 IssueDate, BT-3 InvoiceTypeCode (380), BT-5 CurrencyCode, BT-9 DueDate, BT-23 ProfileID, BT-24 CustomizationID
- **BG-4 Seller** (AccountingSupplierParty): company name, VAT number (`PartyTaxScheme`), postal address with country code, contact email
- **BG-7 Buyer** (AccountingCustomerParty): client name, VAT number, address, email
- **BG-22** Document totals: `LineExtensionAmount`, `TaxExclusiveAmount`, `TaxInclusiveAmount`, `PayableAmount`
- **BG-23** Tax total + subtotal with `TaxCategory`: ID `S` (standard) or `E` (exempt for VAT-exempt invoices), percent
- **BG-25** Invoice lines: ID, quantity (`unitCode="EA"`), `LineExtensionAmount`, Item description + `ClassifiedTaxCategory`, unit price
- `GET /invoices/{invoice}/xml` — downloads as `invoice-{number}.xml` (auth-gated, policy-checked)
- **"Download XML"** button on invoice detail page alongside "Download PDF"

---

## Billing & Subscriptions (Stripe)

**SDK**: `stripe/stripe-php` v19 (raw SDK — not Cashier)

**Plans**: Free, Starter (€9/mo), Pro (€29/mo)

**Stripe Checkout**:
- `POST /billing/checkout/{plan}` — creates Stripe Customer if none exists, creates a hosted Checkout Session for the selected price
- Redirects to Stripe-hosted payment page; returns to billing page with success banner on completion

**Stripe Customer Portal**:
- `POST /billing/portal` — creates a Billing Portal session for subscription management (payment method, cancellation)
- Requires an existing Stripe customer (`stripe_customer_id`)

**Webhooks** (`POST /billing/webhook`, CSRF-exempt, signature-verified):
| Event | Effect |
|---|---|
| `checkout.session.completed` | Sets `stripe_customer_id`, `stripe_subscription_id`, `subscription_status = active`, `plan`, `subscribed_until` |
| `customer.subscription.updated` | Updates `subscription_status`, `plan`, `subscribed_until` |
| `customer.subscription.deleted` | Sets `subscription_status = canceled`, clears `subscribed_until` |
| `invoice.payment_failed` | Sets `subscription_status = past_due`, sends `InvoiceReminderNotification` |

**Trial**: new registrations receive a 14-day Pro trial (`plan = pro`, `trial_ends_at = now() + 14 days`)

**Billing page** (`/billing`):
- Current plan badge (Active / Trial / Payment overdue / Canceled)
- Trial countdown banner (amber, shows days remaining)
- Renewal date
- Upgrade/checkout buttons per plan
- "Manage Billing" button when a Stripe customer exists

**`User` helpers**: `isOnTrial()`, `hasActiveSubscription()`, `isFree()`, `isStarter()`, `isPro()`
