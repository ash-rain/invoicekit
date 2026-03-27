# InvoiceKit — Build Plan

## What
EU-compliant invoicing + time tracking SaaS for European freelancers.
One tool: track time → generate legally compliant VAT invoice → get paid.

## Stack
- Laravel 12 + Livewire 4 + Tailwind CSS
- PostgreSQL (single-tenant, one DB for all users)
- DomPDF for invoice PDF generation
- Stripe + PayPal for subscriptions and payments
- Hetzner EU hosting

## Pricing
| Plan | Price | Limits |
|------|-------|--------|
| Free | €0 | 3 clients, 5 invoices/mo |
| Starter | €9/mo | Unlimited clients, 20 invoices/mo |
| Pro | €29/mo | Unlimited everything, recurring invoices, client portal |

---

## Roadmap

---

### v1.1 — VAT-Exempt Mode (Small Business Exemption — All EU Countries)
**Goal:** Support freelancers legally exempt from charging VAT under national small business threshold schemes. This is a common real-world scenario across all 27 EU member states, each with its own threshold, legal citation, and required invoice notice text.

#### Background — EU Legal Basis
EU VAT Directive 2006/112/EC Articles 282–292 permit member states to exempt small businesses below a national threshold. Council Directive 2020/285/EU (effective Jan 2025) introduced an EU-wide harmonized ceiling of €85,000 and allows cross-border exemption for businesses below both their home country threshold and the €85,000 EU cap. When exempt, invoices **must not show VAT** and **must include the specific national legal notice** — failure to do so is a compliance violation.

#### Per-Country Exemption Reference Table

| Country | Threshold (approx.) | Legal Basis | Required Invoice Notice (local language) |
|---------|--------------------|--------------|--------------------------------------------|
| 🇧🇬 Bulgaria | 100,000 BGN (~€51k) | Art. 96 ЗДДС | Не е начислен ДДС на основание чл. 96, ал. 1 от ЗДДС |
| 🇩🇪 Germany | €22,000 prev. year / €50,000 curr. | §19 UStG (Kleinunternehmerregelung) | Gemäß §19 UStG wird keine Umsatzsteuer berechnet. |
| 🇫🇷 France | €91,900 goods / €36,800 services | Art. 293 B CGI (franchise en base de TVA) | TVA non applicable, art. 293 B du CGI |
| 🇦🇹 Austria | €35,000 | §6 Abs. 1 Z 27 UStG (Kleinunternehmerregelung) | Gemäß §6 Abs. 1 Z 27 UStG wird keine Umsatzsteuer berechnet. |
| 🇧🇪 Belgium | €25,000 | Art. 56bis BTW-Wetboek / Code TVA | Vrijgesteld van BTW art. 56bis W.BTW / TVA non applicable art. 56bis |
| 🇭🇷 Croatia | 40,000 EUR (from 2025) | Čl. 90 Zakona o PDV-u | PDV nije obračunat sukladno čl. 90. Zakona o PDV-u |
| 🇨🇿 Czech Rep. | CZK 2,000,000 (~€83k) | §6 ZDPH | Osvobozeno od DPH dle §6 zákona č. 235/2004 Sb. |
| 🇩🇰 Denmark | DKK 50,000 (~€6.7k) | §48 ML (Momsloven) | Fritaget for moms jf. momslovens §48 |
| 🇪🇪 Estonia | €40,000 | §19 KMS (käibemaksuseadus) | Käibemaks ei ole arvestatud vastavalt KMS §19 |
| 🇫🇮 Finland | €15,000 | AVL 3 § / §3 arvonlisäverolaki | ALV:tä ei sovelleta liikevaihdon pienuuden perusteella (AVL 3 §) |
| 🇬🇷 Greece | €10,000 | Art. 39 Κώδικας ΦΠΑ | Απαλλαγή ΦΠΑ βάσει άρθρου 39 Κώδικα ΦΠΑ |
| 🇭🇺 Hungary | HUF 12,000,000 (~€33k) | §188 ÁFA tv. (alanyi adómentesség) | Alanyi adómentes, ÁFA tv. §188. alapján áfa felszámítása nélkül |
| 🇮🇪 Ireland | €37,500 services / €75,000 goods | Section 2(1) VAT Consolidation Act 2010 | VAT not charged — exempt under Section 2(1) VAT Consolidation Act 2010 |
| 🇮🇹 Italy | €85,000 | Art. 1 co. 54-89 L. 190/2014 (Regime Forfettario) | Operazione effettuata in regime forfettario ex art. 1 co. 58 L. 190/2014 – imposta non esposta |
| 🇱🇻 Latvia | €50,000 | Section 64 Pievienotās vērtības nodokļa likums | PVN netiek piemērots saskaņā ar PVN likuma 64. pantu |
| 🇱🇹 Lithuania | €45,000 | Art. 71 PVM įstatymo | PVM netaikomas pagal PVM įstatymo 71 str. |
| 🇱🇺 Luxembourg | €35,000 | Art. 57 Loi TVA | TVA non applicable — Art. 57 de la loi TVA |
| 🇲🇹 Malta | €20,000 services / €35,000 goods | Article 11 VAT Act (Cap. 406) | VAT not charged — exempt under Article 11 of the VAT Act (Cap. 406) |
| 🇳🇱 Netherlands | €20,000 | Art. 25 Wet OB (KOR — Kleineondernemersregeling) | Op grond van artikel 25 Wet OB is geen btw gefactureerd. |
| 🇵🇱 Poland | PLN 200,000 (~€47k) | Art. 113 ustawy o VAT | Zwolnienie z VAT na podstawie art. 113 ust. 1 ustawy z dnia 11 marca 2004 r. o VAT |
| 🇵🇹 Portugal | €14,500 | Art. 53.º CIVA | Isento de IVA nos termos do artigo 53.º do CIVA |
| 🇷🇴 Romania | RON 300,000 (~€60k) | Art. 310 Codul Fiscal | Scutit de TVA conform art. 310 din Legea nr. 227/2015 – Codul Fiscal |
| 🇸🇰 Slovakia | €49,790 | §4 zákona č. 222/2004 Z.z. o DPH | Oslobodené od DPH podľa §4 zákona č. 222/2004 Z.z. |
| 🇸🇮 Slovenia | €50,000 | Art. 94 ZDDV-1 | DDV ni obračunan na podlagi 1. odstavka 94. člena ZDDV-1 |
| 🇪🇸 Spain | No general threshold exemption* | N/A | *Spain has no simple small-business VAT exemption — registration required from first taxable supply in most cases. Special regime (Recargo de Equivalencia) applies to retail resellers only. |
| 🇸🇪 Sweden | SEK 80,000 (~€7k) | 9 d kap. ML (Mervärdesskattelagen) | Mervärdesskatt tas inte ut enligt 9 d kap. ML |
| 🇨🇾 Cyprus | €15,600 | Article 9 VAT Law 95(I)/2000 | VAT not charged — exempt under Article 9 of the VAT Law |

> **Note on Spain:** Spain is the only EU country without a general small-business VAT threshold exemption for service providers. Spanish freelancers must register for VAT from the first euro of taxable income. The app should detect this and show a warning rather than offering the exemption toggle to Spanish users.

#### Implementation Plan

**Config: `config/vat_exemptions.php`**
- [ ] Create `config/vat_exemptions.php` with a top-level array keyed by ISO 2 country code containing all 27 EU countries:
  ```php
  // config/vat_exemptions.php
  return [
    'BG' => [
      'available' => true,
      'threshold_amount' => 100000,
      'threshold_currency' => 'BGN',
      'threshold_eur_approx' => 51000,
      'legal_basis' => 'Art. 96, ал. 1 ЗДДС',
      'invoice_notice_local' => 'Не е начислен ДДС на основание чл. 96, ал. 1 от ЗДДС',
      'invoice_notice_en' => 'VAT not charged pursuant to Art. 96(1) of the Bulgarian VAT Act',
    ],
    'DE' => [ ... ],
    'ES' => [
      'available' => false,
      'unavailable_reason' => 'Spain has no general small-business VAT exemption.',
    ],
    // ... all 27 EU countries
  ];
  ```

**Service: `app/Services/VatExemptionService.php`**
- [ ] `VatExemptionService` reads exclusively from `config('vat_exemptions')` — no hardcoded data in the class
- [ ] Method `getExemptionForCountry(string $isoCode): ?array`
- [ ] Method `isExemptionAvailable(string $isoCode): bool`
- [ ] Method `getInvoiceNotice(string $isoCode, string $language = 'local'): ?string`

**Database**
- [ ] Add `vat_exempt` boolean to `users` (default `false`) — migration already exists
- [ ] Add `vat_exempt_reason` string (nullable) — stores the legal basis text, editable by user
- [ ] Add `vat_exempt_notice_language` enum `local|en` (default `local`) — controls which language the notice prints in on the PDF

**Settings UI**
- [ ] Settings page "Business / VAT" section:
  - Country selector (drives exemption lookup)
  - Toggle: "I operate under a small business VAT exemption" — hidden/disabled for ES with explanation
  - When toggled on: show the threshold for the detected country ("Under Bulgarian law, freelancers with annual turnover below 100,000 BGN may apply this exemption")
  - Legal basis text field: pre-populated from `VatExemptionService`, editable for edge cases
  - Invoice notice language: "Local language" / "English" radio
  - Prominent warning box: "Enabling this disables VAT on all invoices. Ensure you meet your country's eligibility criteria."

**Invoice Behaviour**
- [ ] When `user.vat_exempt = true`: `EuVatService::calculateVat()` short-circuits to return `['rate' => 0, 'amount' => 0, 'type' => 'vat_exempt']`
- [ ] Invoice builder: show a yellow banner "VAT exemption active — no VAT will be applied to this invoice" with a link to settings
- [ ] Per-invoice override (Pro only): checkbox "Override VAT exemption for this invoice" — allows charging VAT on a one-off basis (e.g. cross-border supply that falls outside the exemption)
- [ ] Store `vat_exempt_applied` boolean on `invoices` table to record the state at invoice creation time (important: settings could change later)
- [ ] Store snapshot of `vat_exempt_notice` text on `invoices` table at creation time

**PDF Generation**
- [ ] When `invoice.vat_exempt_applied = true`:
  - Remove VAT row from the totals block entirely
  - Show subtotal = total (no tax line)
  - Print the exemption notice text at the bottom of the invoice, in a visually distinct block (e.g. italics, smaller font, bordered)
  - Example (BG): *"Не е начислен ДДС на основание чл. 96, ал. 1 от ЗДДС"*
  - Example (DE): *"Gemäß §19 UStG wird keine Umsatzsteuer berechnet."*
- [ ] When NOT exempt: no change to current PDF behavior

**Tests**
- [ ] Unit: `VatExemptionService::getExemptionForCountry()` returns correct data for all 26 available countries
- [ ] Unit: `VatExemptionService::isExemptionAvailable('ES')` returns `false`
- [ ] Unit: `EuVatService::calculateVat()` with `vat_exempt=true` returns `exempt` type and 0 amount regardless of country pair
- [ ] Feature: invoice created by VAT-exempt user has correct zero totals and `vat_exempt_applied = true`
- [ ] Feature: PDF blade for exempt invoice contains the correct notice text and no VAT row
- [ ] Feature: invoice created by non-exempt user unaffected

---

### v1.3 — Freelancer Profile & Business Settings
**Goal:** Give freelancers a complete professional profile. This data drives the invoice PDF header, onboarding, and future public-facing features.

**Schema (add to `users` or extract to `profiles` table)**
- [ ] `display_name` — trading / freelancer name (distinct from auth name)
- [ ] `business_name` — optional registered entity name
- [ ] `profile_photo` — avatar stored in `storage/app/public/avatars`
- [ ] `tagline` — one-liner (e.g. "Full-stack developer · Sofia, Bulgaria")
- [ ] `website` — personal/business URL
- [ ] `phone` — contact phone
- [ ] `address_line1`, `address_line2`, `city`, `postal_code`, `country` — full address
- [ ] `vat_number` — user's own VAT registration number (shown on invoices as seller VAT)
- [ ] `registration_number` — company/trade register number
- [ ] `bank_name`, `bank_iban`, `bank_bic` — bank transfer details printed on invoices
- [ ] `default_currency` — default for new invoices/clients
- [ ] `default_payment_terms` — days until due (14 / 30 / 60)
- [ ] `default_invoice_notes` — boilerplate footer (e.g. "Thank you for your business.")
- [ ] `invoice_logo` — custom logo image for PDF header

**Settings UI (tabbed)**
- [ ] **Profile** tab: name, photo, tagline, website, phone
- [ ] **Business** tab: business name, full address, VAT number, registration number, bank details, VAT exemption section (from v1.2)
- [ ] **Invoicing** tab: default currency, payment terms, invoice number prefix/format, default notes, logo upload
- [ ] **Billing** tab: subscription plan, payment method, billing history (v1.4)
- [ ] **Notifications** tab: reminder email toggles (before due / on due / overdue intervals)

**Invoice PDF**
- [ ] Pull seller header from profile: logo, name or business name, address, VAT number, bank details
- [ ] Onboarding wizard step 1 updated to collect profile fields

---

### v1.4 — Full Billing Support (Stripe + PayPal)
**Goal:** Replace placeholder Stripe integration with a working billing system supporting both Stripe and PayPal.

**Stripe (via `laravel/cashier`)**
- [ ] Stripe Checkout for plan upgrades
- [ ] Webhooks: `checkout.session.completed`, `customer.subscription.updated`, `customer.subscription.deleted`, `invoice.payment_failed`
- [ ] Stripe Customer Portal: update card, download receipts, cancel plan
- [ ] Schema: `stripe_customer_id`, `stripe_subscription_id`, `subscription_status`, `trial_ends_at`, `subscribed_until` on `users`
- [ ] Dunning: on `invoice.payment_failed` — email user, 3-day grace before downgrade to Free
- [ ] 14-day Pro trial on signup (no card required)

**PayPal**
- [ ] PayPal Subscriptions API v2 — alternative to Stripe on the upgrade page
- [ ] Schema: `paypal_subscription_id`, `payment_provider` (`stripe|paypal`) on `users`
- [ ] Webhooks: `BILLING.SUBSCRIPTION.ACTIVATED`, `BILLING.SUBSCRIPTION.CANCELLED`, `PAYMENT.SALE.COMPLETED`, `BILLING.SUBSCRIPTION.PAYMENT.FAILED`
- [ ] `PlanService` abstracted over provider: `isSubscribed()`, `currentPlan()`, `cancelSubscription()` work regardless of payment method

**Billing UI**
- [ ] Billing tab: current plan badge, next renewal date, payment method indicator, Change Plan / Cancel buttons
- [ ] Billing history table: date, amount, status, PDF receipt link
- [ ] Plan comparison modal with upgrade CTA
- [ ] Cancellation flow: confirm modal, optional reason, end-of-period vs. immediate toggle
- [ ] Dashboard usage meter: "X of Y invoices this month" with progress bar

---

### v1.5 — Recurring Invoices
- [ ] `recurring_invoices` table: template invoice + schedule (monthly / quarterly / annually)
- [ ] Scheduled job auto-generates + emails invoices from templates
- [ ] UI: mark any existing invoice as a recurring template, set next send date and frequency
- [ ] Pro plan only

---

### v1.6 — Client Portal
- [ ] Signed tokenized URLs for clients to view/download invoices without an account
- [ ] Portal page: invoice list, PDF download, payment status
- [ ] "Pay online" button (Stripe Payment Link per invoice)
- [ ] Optional password protection per client

---

### v1.7 — Expense Tracking
- [ ] `expenses` table: user_id, client_id (nullable), project_id (nullable), description, amount, currency, category, receipt_file, date
- [ ] Categories: software, hardware, travel, hosting, marketing, other
- [ ] Receipt image/PDF upload (S3-compatible storage)
- [ ] Include expenses as invoice line items (optional toggle per expense)
- [ ] Monthly reports by category, CSV export
- [ ] Dashboard widget: monthly expense summary

---

### v1.8 — Peppol / e-Invoicing
- [ ] Generate UBL 2.1 XML alongside PDF (mandatory in DE, IT, FR for B2G and growing B2B)
- [ ] Peppol BIS Billing 3.0 compliance
- [ ] Export as PDF + XML from invoice detail page

---

### v1.9 — Full Internationalisation (App UI + Invoice PDFs)
**Goal:** Make every user-visible string in the app translatable, and ship complete translations for all EU languages. The lang JSON files already exist in `resources/lang/` for all 24 EU languages — they need to be populated and wired up.

**Languages to support** (matching existing `resources/lang/*.json` files):
`bg` Bulgarian · `cs` Czech · `da` Danish · `de` German · `el` Greek · `en` English · `es` Spanish · `et` Estonian · `fi` Finnish · `fr` French · `ga` Irish · `hr` Croatian · `hu` Hungarian · `it` Italian · `lt` Lithuanian · `lv` Latvian · `mt` Maltese · `nl` Dutch · `pl` Polish · `pt` Portuguese · `ro` Romanian · `sk` Slovak · `sl` Slovenian · `sv` Swedish

**App UI Strings**
- [ ] Audit all Blade views and Livewire component templates — every hardcoded user-visible string must be wrapped in `__()` (already a convention to enforce consistently)
- [ ] Audit all Livewire component PHP classes — flash messages, validation messages, and any strings returned to the view must use `__()` or `trans()`
- [ ] Audit email templates (`resources/views/mail/`) — all subject lines and body text wrapped in `__()`
- [ ] User locale preference: add `locale` column to `users` table; set `App::setLocale()` from the authenticated user's preference on each request (via middleware)
- [ ] Language switcher in user settings (dropdown of all supported locales with native language names, e.g. "Български", "Deutsch")
- [ ] Fall back to `en` for any missing translation key

**Invoice PDF Strings**
- [ ] All invoice PDF field labels translated (Invoice, Date, Due Date, Description, Quantity, Unit Price, Subtotal, VAT, Total, etc.)
- [ ] VAT type notices translated: reverse charge, OSS, exempt — per language
- [ ] VAT exemption legal notice: use the `invoice_notice_local` value from `config/vat_exemptions.php` (already per-country, already in the correct language)
- [ ] PDF language follows the per-client default language setting (set on the client record)
- [ ] User can override PDF language per invoice at generation time

**Translation File Conventions**
- [ ] All app UI keys in `resources/lang/{locale}.json` (flat JSON, Laravel default)
- [ ] Invoice-specific keys namespaced: `"invoice.subtotal"`, `"invoice.vat_notice.reverse_charge"`, etc.
- [ ] VAT exemption notices live in `config/vat_exemptions.php`, not in lang files — they are legal text tied to country, not to UI locale
- [ ] Missing translations CI check: add a test that asserts all keys present in `en.json` exist in every other locale file (allows empty string values, but key must exist)



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
