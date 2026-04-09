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
- **Invoice usage meter** (Free and Starter plans): progress bar showing "X of Y invoices this month" with colour coding (indigo → amber at 80% → red at 100%) and an "Upgrade" CTA link when approaching the limit

---

## Client Management

**Fields**: name, email, address, country (41 countries), VAT number, registration number, currency, default invoice language

- List with name search + pagination (15/page)
- Create, edit, delete (with confirmation)
- Per-country EU VAT number format validation (regex for all 26 EU member states)
- Plan gate on creation: Free plan capped at 3 clients
- Client's `default_language` pre-fills invoice language on invoice creation

### Company Lookup & Auto-fill

Enter a VAT number or national registration number on the New Client screen to auto-fill all company details:

- **Layer 1 — EU VIES**: For EU VAT numbers (e.g. `DE123456789`), the free VIES REST API is queried first. Returns verified name, address, and VAT-registered status. No limits apply.
- **Layer 2 — Gemini AI fallback**: For non-VAT-registered companies (e.g. Bulgarian ЕИК `203137077`) or when VIES has no data, a Gemini AI lookup is performed using country-specific context. Results are cached 24 h per number.
- **Localized registration labels**: The registration number field label updates per selected country — ЕИК (BG), KVK-nummer (NL), SIRET (FR), Handelsregisternummer (DE), CVR-nummer (DK), etc.
- **Duplicate detection**: If the looked-up VAT or registration number matches an existing client, a warning banner with a link is shown.
- **Source badge**: VIES results show a green "Verified via EU VIES" badge; Gemini results show an amber "AI-sourced — please verify" badge.
- **Lookup limits** (Gemini only): Free plan = 2 AI lookups/day; Starter = 10/day; Pro = unlimited. Users with their own Gemini API key bypass limits entirely.

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
- **Create Payment Link** (`POST /invoices/{invoice}/payment-link`): generates a Stripe Payment Link for the invoice amount, stores the URL in `stripe_payment_link_url`, shows a copyable link on the invoice detail page; client can pay directly via the link from the portal
- Plan gate: creation blocked and redirects to billing when monthly invoice limit is reached

**Per-invoice VAT exempt override**: when the company is VAT-exempt, a Pro-accessible checkbox allows charging VAT on a one-off basis (e.g. for a cross-border supply outside the exemption)

**Per-invoice payment method**: each invoice can override the company default payment method. The selected payment method is snapshotted at save time so issued invoices are immutable.

---

## Payment Methods

Multiple payment methods per company, managed in Settings → Business tab.

**Types**: Bank Transfer (IBAN/BIC), Stripe (auto-created on Stripe Connect), Cash

**Features**:
- Add, edit, delete payment methods
- Set a default payment method per company
- Per-invoice payment method override (dropdown in invoice builder, defaults to company default)
- Payment method snapshot stored on invoice at creation — issued invoices never change retroactively
- Stripe Connect integration: connecting Stripe auto-creates a Stripe payment method; disconnecting removes it
- Plan-gated: Free = 1 method, Starter = 3, Pro = unlimited

**EU compliance**: Invoices must display at least one payment method. All PDF templates and the client portal render the resolved payment method (snapshot → live method → company default → legacy bank fields).

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

**Business tab**: company name, full address, VAT number, registration number, payment methods management (bank transfer, Stripe, cash)

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
| Payment methods | 1 | 3 | Unlimited |
| AI document imports/day | 2 | 50 | Unlimited |

- Limits enforced at creation time with inline error (clients) or redirect to billing (invoices)
- AI import limits checked in `DocumentImporter`; shows dual CTA (upgrade + own API key) when reached
- Billing page shows current plan badge, client + invoice + AI import usage counts, upgrade cards
- `User::isFree()`, `isStarter()`, `isPro()` helpers
- **BYOK (Bring Your Own Key)**: users can save a personal Gemini API key in Settings → AI tab; this bypasses all app-enforced AI import limits entirely. Key is stored encrypted at rest.
- **System daily cap**: admin-provided keys are hard-capped at 1000 AI imports/day (configurable via `AI_SYSTEM_DAILY_CAP` env var or `config/ai.php`)
- All limit values configurable via `config/ai.php` (`limits.free`, `limits.starter`, `limits.system_daily_cap`)

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
- **Pay Online button**: when a Stripe Payment Link has been created for the invoice, a prominent "Pay Online" CTA links directly to the Stripe-hosted payment page (shown only for non-paid invoices)
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
- **Invoice + client usage progress bars** showing current usage against plan limits with colour coding (indigo → amber at 80% → red at 100%)
- **Compare Plans** button opens a plan comparison modal overlay with Starter and Pro feature lists and an upgrade CTA
- **Cancel Subscription** button (shown when subscription is active): opens a confirmation modal with an end-of-period vs. immediate cancellation toggle, an optional reason field, and a "Keep Subscription" escape hatch
- **Billing History** table: date, description, amount, status badge (paid/open), and a PDF receipt link — pulled live from Stripe invoices API

**Subscription cancellation** (`POST /billing/cancel`):
- `cancel_at_period_end = 1`: schedules cancellation via Stripe, user retains access until period end
- `cancel_at_period_end = 0`: immediately cancels via Stripe, downgrades user to Free plan locally

**`User` helpers**: `isOnTrial()`, `hasActiveSubscription()`, `isFree()`, `isStarter()`, `isPro()`

---

## Progressive Web App (PWA)

InvoiceKit ships as a fully installable Progressive Web App available to all users with no extra setup.

**Manifest** (`/manifest.json`):
- `display: standalone` — runs full-screen with no browser chrome
- `start_url: /dashboard` — lands on dashboard on launch
- Icons at 192 × 192 and 512 × 512 (maskable) for home screen and splash
- App shortcuts: "New Invoice" → `/invoices/create`

**Service Worker** (`/public/sw.js`):
- Pre-caches static assets (manifest, icons) on install
- Cache-first strategy for all Vite build assets (`/build/`)
- Cache-first for icons and manifest; network-first for page navigations
- Stale-while-revalidate for API responses to keep data fresh
- Old caches purged on activation

**Registration** (`resources/js/app.js`): service worker registered on `window.load`; failures are silent so the app works without SW support.

**Install experience**:
- Browser displays the native "Add to Home Screen" install prompt on iOS, Android, Chrome desktop, and Edge
- Once installed: launches from home screen / taskbar, runs standalone (no address bar), and behaves identically to the web version
- Works offline for previously cached views; deferred sync resumes when connectivity is restored

**Push notifications** are delivered through the same service worker registration (see Notifications & Emails section).

---

## AI Document Import

InvoiceKit supports batch document import powered by Google Gemini 1.5 Flash (free-tier multimodal AI). Users can upload scanned invoices, receipts, or expense documents and have the extracted data reviewed before saving.

### Supported Formats
- PDF, JPG, JPEG, PNG — up to 10 MB per file, up to 10 files per batch

### AI API Key Management (Filament Admin)
- **Model**: `AiApiKey` — encrypts the `api_key` field at rest
- **Filament resource** (`/admin/ai-api-keys`): create, edit, delete, toggle active, copy label
- **Test Key action**: sends a minimal request to Gemini to verify the key is valid; shows success/error feedback
- **Key rotation**: `AiKeyRotationService` selects the next available key via round-robin and applies a 60-second cooldown after any error. Throws `NoAvailableApiKeyException` when no key is available

### Import Pipeline
1. User navigates to **Import Invoices** or **Import Expenses** (buttons on list pages and dashboard shortcuts)
2. `DocumentImporter` Livewire component handles drag-and-drop / file picker upload
   - Files stored to MinIO: `imports/{userId}/{batchId}/`
   - A `DocumentImport` record is created per file with `status = pending`
   - `ProcessDocumentImport` job dispatched to the `imports` queue
3. The page polls every 2 s to show live extraction status: `pending → processing → extracted → completed / failed`
4. Once `extracted`, a **Review** button appears linking to the review page
5. **Invoice Import Review** (`/invoices/import/{import}/review`):
   - Pre-fills invoice number, date, due date, currency, line items from Gemini output
   - Auto-matches client by VAT number or name (case-insensitive LIKE)
   - User can add/remove line items and adjust all fields
   - **Confirm Import** creates a draft `Invoice` + `InvoiceItem` records in a DB transaction; marks import as `completed`
   - **Skip** discards the import without creating a record
6. **Expense Import Review** (`/expenses/import/{import}/review`):
   - Pre-fills amount, description, vendor, category, date, currency from Gemini output
   - **Confirm Import** copies the original uploaded file from MinIO `imports/` → `receipts/{userId}/` and attaches it as `receipt_file`; creates `Expense` record; marks import as `completed`

### Notifications
- `DocumentImportSuccessNotification`: sent via WebPush + database channel; links to the correct review route based on `document_type`
- `DocumentImportFailedNotification`: sent via WebPush + database channel; includes the error message

### Client Filter Enhancement
- Invoice and expense list pages now include a **Filter by client** `<select>` dropdown (URL-bound via `#[Url]`)
- Client detail page (`/clients/{client}`) shows stats (Total Invoiced, Total Paid, Outstanding, Total Expenses), recent invoices and expenses tables, and shortcuts to create a new invoice or expense for that specific client

