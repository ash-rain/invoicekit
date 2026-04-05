# InvoiceKit — Roadmap

## What
EU-compliant invoicing + time tracking SaaS for European freelancers.
One tool: track time → generate legally compliant VAT invoice → get paid.

## Stack
- Laravel 12 + Livewire 4 + Tailwind CSS
- PostgreSQL (single-tenant, one DB for all users)
- DomPDF for invoice PDF generation
- Stripe for subscriptions and payments
- Hetzner EU hosting

## Pricing
| Plan | Price | Limits |
|------|-------|--------|
| Free | €0 | 3 clients, 5 invoices/mo |
| Starter | €9/mo | Unlimited clients, 20 invoices/mo |
| Pro | €29/mo | Unlimited everything, recurring invoices, client portal |

---

## Completed — v1.x Milestones

| Version | Feature | Status |
|---------|---------|--------|
| v1.1 | VAT-Exempt Mode (all 27 EU countries, per-country legal notice) | ✅ Done |
| v1.3 | Freelancer Profile & Business Settings (invoicing, billing, notifications tabs) | ✅ Done |
| v1.4 | Recurring Invoices (scheduled job, Pro plan) | ✅ Done |
| v1.5 | Client Portal (tokenized URLs, password protection, Pay Online button) | ✅ Done |
| v1.6 | Expense Tracking (categories, receipt upload, reports, dashboard widget) | ✅ Done |
| v1.7 | Peppol / e-Invoicing (UBL 2.1 XML, Peppol BIS 3.0) | ✅ Done |
| v1.8 | Full Billing (Stripe checkout, webhooks, portal, cancellation, billing history, usage meter, payment links) | ✅ Done |

---

## Completed — v2.0 Invoice Customisation

| Feature | Status |
|---------|--------|
| 6 PDF invoice templates (classic, modern, bold, elegant, compact, stripe) | ✅ Done |
| Default template per company (Settings → Invoicing) | ✅ Done |
| Per-invoice template override (Create/Edit Invoice) | ✅ Done |
| Configurable invoice starting number per company | ✅ Done |
| Company invoice prefix used in number generation | ✅ Done |
| DomPDF production fixes (CSS, entity encoding, remote assets, font cache) | ✅ Done |

---

## Upcoming Roadmap

### v2.1 — Advanced Invoice Branding
- Custom brand colour picker (accent colour applied to all templates)
- Custom font selection (from bundled set of DomPDF-compatible fonts)
- Optional watermark text overlay on draft/overdue PDFs
- Template live-preview in settings (rendered thumbnail via headless Chrome or DomPDF snapshot)

### v2.2 — Client Portal Enhancements
- Client-facing overview of all invoices (portal home page)
- Download all invoices as a ZIP from the portal
- Partial payment recording by client
- Portal branding with company logo and accent colour

### v2.3 — Financial Reports & Analytics
- Monthly revenue report (PDF + CSV export)
- Unpaid invoices ageing report
- Expense vs revenue comparison chart on dashboard
- Year-over-year comparison view
- EU VAT summary report (OSS, reverse-charge, exempt breakdown)

### v2.4 — API Access & Integrations
- REST API (v1) with API key authentication
- Webhook delivery for invoice status changes
- Zapier / Make.com integration
- Accounting export: DATEV (Germany), SAF-T (Poland, Romania)

### v2.5 — Team & Multi-Seat
- Invite team members (accountant, virtual assistant roles)
- Role-based permissions: Admin, Editor, Viewer
- Activity audit log per invoice
- Multiple companies per user (switch via company selector)

### v2.6 — Mobile & Offline
- Progressive Web App with offline draft support
- Mobile-optimised invoice creation flow
- Swipe-to-mark-paid gesture on invoice list
- Push notifications for payment received

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
