# InvoiceKit — Build Plan

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

## Roadmap — All v1.x Milestones Complete ✓

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
