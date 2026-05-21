---
name: bg-compliance-check
description: Audit an invoice (or a batch of invoices) for Bulgarian VAT and invoicing compliance under InvoiceKit's bg-compliance-v1 rules. Invoke when the user asks to "check BG compliance", "audit Bulgarian invoices", "run compliance check", or before generating the weekly compliance digest. Walks the rule set, queries the database for sample invoices, and reports violations.
disable-model-invocation: true
---

# Bulgarian Invoicing Compliance Check (bg-compliance-v1)

This skill encodes the Bulgarian VAT and invoicing rules that InvoiceKit applies via the `bg-compliance-v1` migrations and `EuVatService`. Use it to audit specific invoices, sample a date range, or generate the recurring digest.

## Reference

- Spec: `docs/superpowers/specs/2026-04-11-bg-compliance-v1-design.md`
- Service: `app/Services/EuVatService.php`
- Migrations: `2026_04_11_000001_add_vat_rate_key_and_place_of_supply_to_invoice_items_table.php`, `2026_04_11_000002_add_bg_compliance_v1_to_invoices_table.php`, `2026_04_11_000003_add_custom_invoice_rules_to_companies_table.php`, `2026_04_11_165545_make_vat_rate_nullable_on_invoices_table.php`
- Prior digests: `compliance-digest-2026-04-13.md`, `compliance-digest-2026-04-27.md`, `compliance-digest-2026-05-11.md`, `compliance-digest-2026-05-18.md`

## Rules — check every one

For each invoice in scope:

### A. Identity and numbering
1. Invoice number is sequential per company per year, no gaps.
2. Issued date is on or before today; due date is after issued date.
3. Seller VAT ID present if company is VAT-registered (BG + 9–10 digits).
4. Buyer details: name + address mandatory; VAT ID mandatory for B2B EU reverse-charge.

### B. VAT classification (per-line)
Each `invoice_items` row has a `vat_rate_key` and `place_of_supply`. Valid `vat_rate_key` values:

- `standard` — 20% BG VAT, place of supply = BG.
- `reduced_9` — 9% (hotels, restaurants restricted scope).
- `reverse_charge` — 0% VAT, B2B service to EU VAT-registered buyer outside BG. `place_of_supply` must be the buyer's country code (ISO 3166-1 alpha-2).
- `oss` — One Stop Shop, B2C goods/services to EU consumer. `place_of_supply` = buyer country. VAT rate must match buyer-country rate.
- `exempt` — Article 39 etc. Requires `exempt_reason` text. Place of supply = BG.
- `outside_scope` — Non-EU buyer. 0% VAT, no reverse-charge, no OSS.

Check:
5. `vat_rate_key` is one of the above.
6. The numeric `vat_rate` on the line matches the key's allowed rate.
7. `place_of_supply` is consistent with the key.
8. Reverse-charge invoices include a "Reverse charge — Article 196 Directive 2006/112/EC" note.
9. OSS invoices use the buyer-country rate from the OSS rate table.

### C. Totals and rounding
10. Line subtotal = qty × unit_price, rounded to 2 decimals.
11. Line VAT = subtotal × vat_rate, rounded to 2 decimals.
12. Invoice subtotal = sum(line subtotals).
13. Invoice VAT = sum(line VAT).
14. Invoice total = subtotal + VAT − discount.
15. Currency is consistent across lines; foreign currency invoices include BGN equivalent at the official BNB rate on the issue date.

### D. Storage and audit
16. Invoice PDF is stored (check `invoices.pdf_path` or storage disk).
17. Issued invoices are immutable — flag any `updated_at > issued_at + 1 day` for posted invoices.

## How to run

1. **Scope.** Ask the user: single invoice ID? a date range? a specific company? Default to "all invoices issued in the last 14 days" if not specified.
2. **Pull data.** Use `mcp__laravel-boost__database-query` with read-only SELECTs against `invoices`, `invoice_items`, `companies`, `clients`. Example:
   ```sql
   SELECT i.*, c.vat_id AS seller_vat, cl.country, cl.vat_id AS buyer_vat
   FROM invoices i
   JOIN companies c ON c.id = i.company_id
   JOIN clients cl ON cl.id = i.client_id
   WHERE i.issued_at >= NOW() - INTERVAL '14 days'
   ORDER BY i.issued_at DESC;
   ```
3. **Apply checklist.** For each invoice, walk rules A–D in order. Use `EuVatService` logic as the source of truth where rules are ambiguous — read the service file rather than guessing.
4. **Tabulate.** Group findings by severity (BLOCKER / WARNING / INFO) and by rule number.

## Output format

Emit a digest in the same shape as `compliance-digest-YYYY-MM-DD.md`:

```markdown
# BG Compliance Digest — YYYY-MM-DD

Range: <from> → <to>
Invoices audited: N

## Blockers (N)
- Invoice #INV-2026-0123 (rule B.6): vat_rate=0.09 but vat_rate_key=standard
- ...

## Warnings (N)
- Invoice #INV-2026-0118 (rule A.4): buyer VAT ID missing on reverse-charge invoice
- ...

## Notes
- ...

## Counts by rule
| Rule | Hits |
|------|------|
| B.6  | 3    |
```

Save the digest to `compliance-digest-<today>.md` in the repo root. Do **not** modify invoice data — only audit and report.

## When NOT to use

- Generating new invoices — use the regular Filament resource flow.
- Non-BG companies — this skill is BG-specific. EU-wide rules are partly covered but use `EuVatService` directly for other jurisdictions.
- Tax-filing submissions — out of scope; the digest is internal QA only.
