# Landing Page & Onboarding Wizard Refresh

**Date**: 2026-04-11
**Goal**: Update the landing page and onboarding wizard to reflect all features shipped since launch, with particular emphasis on BG compliance, credit notes, per-line-item VAT, and company lookup.

---

## 1. Landing Page

### 1.1 Page Flow (New)

| # | Section | Status |
|---|---------|--------|
| 1 | Hero | Keep, minor copy refresh |
| 2 | Feature Groups (4 themed) | New — replaces flat 10-card grid |
| 3 | How It Works | Keep unchanged |
| 4 | Compliance Spotlight | New — BG showcase |
| 5 | Online Payments detail | Keep unchanged |
| 6 | AI Import detail | Keep unchanged |
| 7 | Pricing | Update feature lists |
| 8 | FAQ | Expand from 7 to 11 entries |
| 9 | Final CTA | Keep unchanged |

**Removed sections**:
- Standalone EU VAT Engine detail section (absorbed into EU Compliance feature group)
- VAT Rates comparison table (low conversion value)

### 1.2 Feature Groups

The flat 10-card grid is replaced by 4 themed groups. Each group has a heading, subtitle, and 4 feature cards.

**Group 1 — Invoicing** (core value prop)
- **Invoice Builder**: Generate professional PDF invoices in seconds. Auto-numbered, legally formatted, with all required EU fields including full VAT breakdown.
- **Credit Notes & Void Invoices** (new): Issue credit notes to partially or fully reverse sent invoices. Void (сторно) cancels an invoice entirely. Both reference the original and generate proper legal documents.
- **Recurring Invoices**: Set up weekly, monthly, quarterly, or yearly invoices. InvoiceKit clones and issues them automatically — no manual work, no missed billing cycles.
- **Peppol / e-Invoicing** (promoted from pricing): Generate UBL 2.1 / Peppol BIS Billing 3.0 compliant XML files. Submit to your country's e-invoicing portal or send directly to clients who accept structured invoices.

**Group 2 — EU Compliance** (differentiator)
- **EU VAT Engine**: Automatic VAT for every EU scenario: standard rate, reverse charge (B2B), OSS (B2C EU consumers), and 0% exempt for non-EU buyers.
- **Country-Specific Validation** (new): InvoiceKit validates invoices against country-specific legal requirements. Bulgaria is fully supported today — mandatory fields, legal basis texts, and document validation gates. More countries coming.
- **Per-Line-Item VAT Rates** (new): Assign different VAT rates per line item — standard, reduced, or zero. Totals group by rate with a clear VAT summary breakdown.
- **VAT Exempt Mode**: For small businesses below the national threshold. Automatic legal notice on invoices, per-invoice override when needed. All 27 EU countries supported.

**Group 3 — Getting Paid** (money flow)
- **Client Portal & Stripe Payments**: Share a secure tokenized link. Clients view the invoice, download the PDF, and pay by card via Stripe — funds go directly to your bank. No login required.
- **Payment Methods** (new): Manage multiple payment methods — bank transfer (IBAN/BIC), Stripe, or cash. Set a default, override per invoice. Payment details are snapshotted so issued invoices never change.
- **Multi-Currency**: Invoice in EUR, USD, BGN, RON, PLN, CZK, or HUF. Each client stores their preferred currency — auto-fills on every new invoice.
- **Company Lookup** (new): Enter a VAT number or registration number when adding a client. InvoiceKit queries EU VIES first, then falls back to AI. Name, address, and VAT status auto-fill instantly.

**Group 4 — Productivity** (daily workflow)
- **Time Tracker**: Start/stop timer per project. Manual entry for past work. Daily logs, weekly summaries, and monthly reports with total billable hours.
- **Expense Tracking**: Log business expenses with receipts, categories, and vendor details. Filter by date or category, export to CSV, and keep monthly spending alongside revenue.
- **AI Document Import**: Drag and drop invoices, receipts, or expense documents. AI extracts amounts, dates, vendors, and VAT automatically. Review and save in one click.
- **Works on Any Device (PWA)**: Add InvoiceKit to your home screen on phone, tablet, or desktop. Installs like a native app, opens without a browser, and works offline.

### 1.3 Compliance Spotlight Section

Replaces the standalone VAT detail section. Tells the story: "We don't just handle EU VAT — we go deep on country-specific compliance."

- **Section tag**: EU Compliance
- **Headline**: "Deep compliance, country by country"
- **Subtitle**: InvoiceKit doesn't stop at VAT calculation. For supported countries, it validates every invoice against local legal requirements before you can issue it.
- **BG showcase card** — visual callout with Bulgarian flag:
  - Invoice validation gate — blocks issuing incomplete invoices
  - Credit notes (кредитно известие) and void/сторно
  - Per-line-item VAT with standard/reduced/zero rates
  - Legal basis auto-population on every invoice
  - Client completeness indicators
- **"More countries coming"** note — signals this is a pattern, not a one-off

### 1.4 Pricing Updates

**Free (€0)**
- Up to 3 clients
- 5 invoices per month
- Time tracking
- PDF invoice export
- EU VAT engine (new line)
- Credit notes & void invoices (new line)
- 14-day Pro trial included
- :free_limit AI imports per day

**Starter (€9/mo)**
- Unlimited clients
- 20 invoices per month
- All EU VAT rules
- Payment reminders
- Expense tracking
- Up to 3 payment methods (new line)
- Company auto-lookup — 10/day (new line)
- :starter_limit AI imports per day

**Pro (€29/mo)**
- Unlimited everything
- Recurring invoices
- Client portal
- Peppol / e-Invoicing (UBL 2.1)
- VAT Exempt Mode (all 27 EU)
- All EU VAT rules
- Country-specific compliance (new line)
- Per-line-item VAT rates (new line)
- Unlimited payment methods (new line)
- Unlimited company lookups (new line)
- Online payments via Stripe (2% fee)
- Unlimited AI imports

### 1.5 New FAQ Entries

Add these 4 entries to the existing 7 (total: 11):

**Q: What are credit notes and void invoices?**
A: Credit notes let you partially or fully reverse a sent invoice. Void (сторно) cancels an invoice entirely. Both generate proper documents with legal references to the original invoice. Available on all plans.

**Q: What is country-specific compliance?**
A: Beyond EU VAT, InvoiceKit validates invoices against country-specific legal requirements. Bulgaria is fully supported today — including mandatory fields, legal basis texts, and per-line-item VAT rates. More countries are being added.

**Q: What is Peppol / e-Invoicing?**
A: Peppol is the EU standard for electronic invoicing (UBL 2.1). InvoiceKit generates Peppol BIS Billing 3.0 compliant XML files you can submit to your country's e-invoicing portal or send directly to clients who accept structured invoices. Available on the Pro plan.

**Q: How does company auto-lookup work?**
A: Enter a VAT number or national registration number when adding a client. InvoiceKit queries the EU VIES database first, then falls back to AI lookup for non-VIES numbers. Company name, address, and VAT status are auto-filled. Lookup limits depend on your plan; adding your own free Gemini API key removes all limits.

### 1.6 Translation Strategy

All 24 language files (`resources/lang/landing/{LANG}.md`) are fully rewritten in this cycle. The EN.md file is the source of truth. All other 23 files receive complete translations for all new, updated, and existing keys.

**New keys** (~30-40): group headings/subtitles, 6 new feature cards (title + desc), compliance spotlight section (~8 keys), updated pricing lines (~6 keys), 4 new FAQ entries (8 keys).

**Removed keys** (~10): standalone VAT detail section keys, VAT rates table keys.

**Languages**: bg, cs, da, de, el, en, es, et, fi, fr, ga, hr, hu, it, lt, lv, mt, nl, pl, pt, ro, sk, sl, sv.

---

## 2. Onboarding Wizard

### 2.1 Step Structure

Expanded from 3 steps to 6 steps. Replaces the existing `OnboardingWizard` Livewire component and blade template.

| Step | Name | Required Fields | Skippable? |
|------|------|----------------|------------|
| 1 | Your Business | Company name, country | No |
| 2 | VAT & Tax | VAT number (EU countries) | No (EU); all fields optional for non-EU |
| 3 | First Client | Client name, country | No |
| 4 | First Project | Project name, hourly rate | Yes (entire step) |
| 5 | Payment Method | Type, IBAN/BIC or label | Yes (entire step) |
| 6 | You're All Set | — (launchpad) | N/A |

### 2.2 Step Details

**Step 1 — Your Business**
- Fields: company name (required), country (required, dropdown, 33 countries), address (optional), phone (optional)
- Country selection drives smart defaults for all subsequent steps
- Company name pre-fills from user's registration name (existing behavior)

**Step 2 — VAT & Tax**
- Fields: VAT number (required for EU countries), registration number (optional, localized label), VAT exempt toggle (optional)
- VAT number field shows country-specific format hint (e.g. "BG followed by 9 or 10 digits")
- Registration number label localizes per country: ЕИК (BG), KVK-nummer (NL), SIRET (FR), Handelsregisternummer (DE), CVR-nummer (DK), etc. — reuses existing `country_defaults` config
- VAT exempt toggle shows threshold info when available (e.g. "Bulgaria: up to 100,000 BGN (~51,130 EUR)")
- Non-EU countries skip VAT exempt section (no threshold to display)
- VIES validation runs automatically when an EU VAT number is entered
- For non-EU countries (US, GB, CH, NO, AU, CA): VAT number is optional, no format validation, no exempt toggle

**Step 3 — First Client**
- Fields: client name (required), email (optional), country (required, defaults to company country), currency (auto-fills from country), VAT number (optional)
- Client country defaults to company country (same-country billing is the natural first case)
- Currency auto-fills based on client country
- VAT number placeholder: "Optional — enables reverse charge"

**Step 4 — First Project (skippable)**
- Fields: project name, hourly rate, currency (auto-filled from client, read-only)
- "Skip — I'll set this up later" button advances to step 5
- Linked to the client created in step 3

**Step 5 — Payment Method (skippable)**
- Type selector: Bank Transfer or Cash (visual toggle cards)
- Bank Transfer shows: IBAN (with country-specific length hint), BIC/SWIFT (optional)
- Cash shows: just a label/name field
- "Skip — I'll add this in Settings" button
- Note: "Stripe payments can be connected later in Settings → Payments"
- Step auto-skipped if user already has a Stripe payment method (from prior Stripe Connect)
- Creates a `PaymentMethod` record and sets it as company default

**Step 6 — You're All Set**
- Summary celebration (no form)
- 4 "what to do next" cards in a 2x2 grid:
  - Create Invoice → `/invoices/create`
  - Start Timer → `/timer`
  - Import Document → `/invoices` (import flow)
  - Context-aware 4th card: highlights the most impactful skipped item
    - If payment method was skipped: "Add Payment Method" (amber highlight) → Settings
    - If project was skipped: "Create a Project" → `/projects`
    - If nothing was skipped: "Explore Settings" → `/settings`
- "Go to Dashboard" primary CTA button

### 2.3 Smart Country Defaults

Country selection on step 1 cascades through the entire wizard:

| Target | Default Behavior |
|--------|-----------------|
| Step 2: VAT number format | Country-specific regex hint (e.g. BG = "BG + 9-10 digits") |
| Step 2: Registration label | Localized label from `country_defaults` config (ЕИК, KVK, SIRET, etc.) |
| Step 2: VAT exempt threshold | Shown from `config/vat_exemptions.php` if available for the country |
| Step 3: Client country | Defaults to company country |
| Step 3: Currency | Auto-fills from client country |
| Step 5: IBAN length hint | Country-specific (BG = 22, DE = 22, NL = 18, etc.) |

### 2.4 Validation Rules

| Field | Rule |
|-------|------|
| companyName | required, string, max:255 |
| companyCountry | required, string, size:2 |
| companyAddress | nullable, string, max:500 |
| companyPhone | nullable, string, max:50 |
| vatNumber | required_if company is EU country, nullable otherwise, string, max:20, country-specific format regex |
| registrationNumber | nullable, string, max:50 |
| vatExempt | boolean |
| clientName | required, string, max:255 |
| clientEmail | nullable, email, max:255 |
| clientCountry | required, string, size:2 |
| clientCurrency | required, in:EUR,USD,BGN,RON,PLN,CZK,HUF |
| clientVatNumber | nullable, string, max:20 |
| projectName | required_unless:skipProject,true, nullable, string, max:255 |
| hourlyRate | required_unless:skipProject,true, nullable, numeric, min:0 |
| paymentMethodType | required_unless:skipPayment,true, in:bank_transfer,cash |
| bankIban | required_if:paymentMethodType,bank_transfer, nullable, string, max:34 |
| bankBic | nullable, string, max:11 |

### 2.5 Database Changes

The `complete()` method transaction expands to also:
- Store VAT number and registration number on the `Company` model
- Set `vat_exempt` and related fields on the `Company` model if toggled
- Store client VAT number on the `Client` model
- Create a `PaymentMethod` record if not skipped, set as company default

No new migrations needed — all fields already exist on their respective models.

### 2.6 Dashboard Completeness Component

Update the existing setup completeness component to check:

**Triggers nudge (incomplete)**:
- No payment method configured
- No VAT number (for EU countries)
- No company address

**Does not trigger nudge (intentional omissions)**:
- VAT exempt not toggled
- No project created
- No hourly rate set

Each incomplete item renders as a link to the relevant settings section. Existing dismissal/reappearance behavior preserved.

---

## 3. Files Affected

### Landing Page
- `resources/views/welcome.blade.php` — restructure to themed groups, add compliance spotlight, remove VAT detail + rates table
- `resources/lang/landing/EN.md` — full rewrite with new keys
- `resources/lang/landing/{BG,CS,DA,DE,EL,ES,ET,FI,FR,GA,HR,HU,IT,LT,LV,MT,NL,PL,PT,RO,SK,SL,SV}.md` — full rewrite (23 files)

### Onboarding Wizard
- `app/Livewire/OnboardingWizard.php` — rebuild: 6 steps, new fields, smart defaults, expanded validation, payment method creation
- `resources/views/livewire/onboarding-wizard.blade.php` — rebuild: 6-step UI with progress indicator, country-aware fields, skip buttons

### Dashboard
- Existing setup completeness component — update completeness checks (add: no payment method, no VAT number for EU, no address)

### No new files needed
- No new migrations (all fields exist)
- No new models or services
- No new routes

---

## 4. Out of Scope

- Translations for non-landing-page UI strings (these are separate from landing page markdown files)
- New plan-gating logic (all features are already gated correctly)
- Stripe Connect onboarding during the wizard (deferred to Settings)
- Additional country compliance beyond BG (future work)
- Hero section redesign (copy refresh only, not a visual overhaul)
