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

#### Remaining Work

**Settings UI**
- [ ] Legal basis text field in the VAT section: pre-populated from `VatExemptionService`, editable for edge cases
- [ ] Prominent warning box: "Enabling this disables VAT on all invoices. Ensure you meet your country's eligibility criteria."

**Invoice Behaviour**
- [ ] Per-invoice override (Pro only): checkbox "Override VAT exemption for this invoice" — allows charging VAT on a one-off basis (e.g. cross-border supply that falls outside the exemption)

**Tests**
- [ ] Feature: PDF blade for exempt invoice contains the correct notice text and no VAT row

---

### v1.3 — Freelancer Profile & Business Settings
**Goal:** Give freelancers a complete professional profile. This data drives the invoice PDF header, onboarding, and future public-facing features.

#### Remaining Work

**Settings UI**
- [ ] **Invoicing** tab: invoice number prefix/format field (currency, terms, notes, logo already done)
- [ ] **Billing** tab: subscription plan, payment method, billing history (v1.4)
- [ ] **Notifications** tab: reminder email toggles (before due / on due / overdue intervals)

**Invoice PDF**
- [ ] Wire up `invoice_logo` in PDF header (currently hardcoded brand name); add `bank_name` and `bank_bic` to payment details block
- [ ] Onboarding wizard: collect extended profile fields (address, bank details, phone) beyond just company name + country

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
