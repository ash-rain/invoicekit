<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\BlogCategory;
use App\Models\BlogPost;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CountryComplianceBlogPostSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Seeds one BlogPost per supported country summarizing InvoiceKit's own
     * VAT/e-invoicing compliance-checklist status. Entries are split into two
     * buckets:
     *
     * - "Fully compliant" countries: no outstanding item on InvoiceKit's own
     *   compliance checklist today.
     * - "Partial compliance" countries: a percentage of InvoiceKit's own
     *   tracked checklist items completed, with the remaining gaps named
     *   explicitly. This percentage is an internal engineering-progress
     *   metric only, never a government compliance certification.
     */
    public function run(): void
    {
        $this->call(BlogCategorySeeder::class);

        $category = BlogCategory::where('slug', 'compliance')->firstOrFail();

        $admin = Admin::firstOrCreate(
            ['email' => 'compliance@invoicekit.app'],
            [
                'name' => 'InvoiceKit Compliance Desk',
                'password' => Hash::make(Str::random(40)),
            ]
        );

        foreach ($this->entries() as $entry) {
            BlogPost::updateOrCreate(
                ['slug' => $entry['slug']],
                [
                    'admin_id' => $admin->id,
                    'category_id' => $category->id,
                    'title' => $entry['title'],
                    'body' => $entry['body'],
                    'meta_title' => $entry['meta_title'],
                    'meta_description' => $entry['meta_description'],
                    'thumbnail_emoji' => BlogPost::flagEmoji($entry['iso']),
                    'published_at' => now(),
                ]
            );
        }
    }

    /**
     * @return array<int, array{slug: string, title: string, meta_title: string, meta_description: string, body: string, country: string, iso: string, percentage: int|null}>
     */
    private function entries(): array
    {
        return [
            // Bucket A - fully compliant (no outstanding item on InvoiceKit's own checklist).
            [
                'slug' => 'austria-compliance-status',
                'country' => 'Austria',
                'iso' => 'AT',
                'percentage' => null,
                'title' => 'Austria: Fully Compliant',
                'meta_title' => 'Austria VAT Rates and Peppol E-Invoicing Compliance',
                'meta_description' => 'How InvoiceKit tracks Austria VAT compliance: current standard, reduced and 0% rate options plus UBL 2.1 / Peppol BIS 3.0 e-invoicing export, with no outstanding items on our internal checklist.',
                'body' => <<<'HTML'
<p>Austria's VAT rate options in InvoiceKit are current, including a 0% zero-rated line item for qualifying hygiene and essential goods, selectable directly when creating or editing an Austrian invoice line — no setup required. Every Austrian invoice can also be exported as standards-based UBL 2.1 / Peppol BIS Billing 3.0 XML.</p>
<p>Whether a specific product qualifies for the zero rate is a classification question for your accountant; InvoiceKit's job is keeping the right rate options available and applying them cleanly. On InvoiceKit's own compliance checklist, Austria has no outstanding items today.</p>
HTML,
            ],
            [
                'slug' => 'cyprus-compliance-status',
                'country' => 'Cyprus',
                'iso' => 'CY',
                'percentage' => null,
                'title' => 'Cyprus: Fully Compliant',
                'meta_title' => 'Cyprus VAT Rates and Peppol E-Invoicing Compliance',
                'meta_description' => 'Cyprus VAT compliance in InvoiceKit: the 19% standard rate plus reduced and zero rates, with UBL 2.1 / Peppol BIS 3.0 export available for every invoice and no outstanding checklist items.',
                'body' => <<<'HTML'
<p>InvoiceKit's VAT configuration for Cyprus is current, with the 19% standard rate correctly set as the default on invoice lines, alongside the country's reduced and zero rates. Every Cyprus invoice can be exported as UBL 2.1 / Peppol BIS Billing 3.0 XML, ready to hand off to your accountant or downstream systems.</p>
<p>Which rate category fits a given sale is a question for your accountant, not something InvoiceKit determines automatically. On InvoiceKit's own compliance checklist, Cyprus has no outstanding items today.</p>
HTML,
            ],
            [
                'slug' => 'czech-republic-compliance-status',
                'country' => 'Czech Republic',
                'iso' => 'CZ',
                'percentage' => null,
                'title' => 'Czech Republic: Fully Compliant',
                'meta_title' => 'Czech Republic VAT Rates and Peppol E-Invoicing',
                'meta_description' => 'See how InvoiceKit keeps Czech VAT current, including the unified 12% restaurant and catering rate and 0% prescription medicines, plus UBL 2.1 / Peppol BIS 3.0 e-invoicing export.',
                'body' => <<<'HTML'
<p>Czech VAT rates in InvoiceKit are current: restaurant and catering services now use a single 12% reduced rate, and prescription medicines can be invoiced at 0%, both selectable directly on invoice lines with no setup needed. Every Czech invoice can also be exported as UBL 2.1 / Peppol BIS Billing 3.0 XML.</p>
<p>Whether a specific item counts as "restaurant and catering" or "prescription medicines" is a classification question for your accountant. On InvoiceKit's own compliance checklist, the Czech Republic has no outstanding items today.</p>
HTML,
            ],
            [
                'slug' => 'denmark-compliance-status',
                'country' => 'Denmark',
                'iso' => 'DK',
                'percentage' => null,
                'title' => 'Denmark: Fully Compliant',
                'meta_title' => 'Denmark VAT Rates and Peppol E-Invoicing Compliance',
                'meta_description' => 'Denmark VAT compliance in InvoiceKit: the flat 25% standard rate with no general reduced rate, plus UBL 2.1 / Peppol BIS 3.0 export for invoicing Danish public-sector buyers.',
                'body' => <<<'HTML'
<p>Denmark's VAT setup in InvoiceKit is current: the flat 25% standard rate applies, and — as expected for Denmark — there's no general reduced rate to configure, only a narrow set of exemptions handled outside the standard mechanism. Every Danish invoice can be exported as UBL 2.1 / Peppol BIS Billing 3.0 XML, useful if you sell to public-sector buyers.</p>
<p>Whether a specific sale is exempt is a question for your accountant. On InvoiceKit's own compliance checklist, Denmark has no outstanding items today.</p>
HTML,
            ],
            [
                'slug' => 'estonia-compliance-status',
                'country' => 'Estonia',
                'iso' => 'EE',
                'percentage' => null,
                'title' => 'Estonia: Fully Compliant',
                'meta_title' => 'Estonia VAT Rates and Peppol E-Invoicing Compliance',
                'meta_description' => 'Estonia VAT compliance in InvoiceKit: the current 24% standard rate, updated from 22%, plus UBL 2.1 / Peppol BIS 3.0 e-invoicing export available for every Estonian invoice.',
                'body' => <<<'HTML'
<p>Estonia's standard VAT rate in InvoiceKit is correctly set at 24%, updated from the previous 22% and live across the account for any new invoice. Every Estonian invoice can also be exported as UBL 2.1 / Peppol BIS Billing 3.0 XML.</p>
<p>Older drafts created before the update won't change automatically, so it's worth checking any that are still open. On InvoiceKit's own compliance checklist, Estonia has no outstanding items today.</p>
HTML,
            ],
            [
                'slug' => 'finland-compliance-status',
                'country' => 'Finland',
                'iso' => 'FI',
                'percentage' => null,
                'title' => 'Finland: Fully Compliant',
                'meta_title' => 'Finland VAT Rates and Peppol E-Invoicing Compliance',
                'meta_description' => 'Finland VAT compliance in InvoiceKit: the 13.5% reduced rate effective 1 January 2026 for food, transport, accommodation, books and culture, plus UBL 2.1 / Peppol BIS 3.0 export.',
                'body' => <<<'HTML'
<p>Finland's reduced VAT rate in InvoiceKit is correctly set at 13.5% (down from 14%) for invoices dated from 1 January 2026, covering food, catering, transport, accommodation, books, culture, and medicines; earlier invoices correctly keep the prior 14% rate. Every Finnish invoice can also be exported as UBL 2.1 / Peppol BIS Billing 3.0 XML.</p>
<p>Recurring templates set up with the old rate are worth checking before the cutover. On InvoiceKit's own compliance checklist, Finland has no outstanding items today.</p>
HTML,
            ],
            [
                'slug' => 'germany-compliance-status',
                'country' => 'Germany',
                'iso' => 'DE',
                'percentage' => null,
                'title' => 'Germany: Fully Compliant',
                'meta_title' => 'Germany VAT Rates and Peppol E-Invoicing Compliance',
                'meta_description' => 'Germany VAT compliance in InvoiceKit: the 7% reduced rate returning for restaurant and catering services from 1 January 2026, plus UBL 2.1 / Peppol BIS 3.0 e-invoicing export.',
                'body' => <<<'HTML'
<p>Germany's VAT rate options in InvoiceKit are current: the 7% reduced rate for restaurant and catering services returns for invoices dated from 1 January 2026, selectable per line so a single invoice can mix standard and 7% items. Every German invoice can also be exported as UBL 2.1 / Peppol BIS Billing 3.0 XML.</p>
<p>Whether a service genuinely qualifies as restaurant or catering is a classification question for your accountant. On InvoiceKit's own compliance checklist, Germany has no outstanding items today.</p>
HTML,
            ],
            [
                'slug' => 'hungary-compliance-status',
                'country' => 'Hungary',
                'iso' => 'HU',
                'percentage' => null,
                'title' => 'Hungary: Fully Compliant',
                'meta_title' => 'Hungary VAT Rates and Peppol E-Invoicing Compliance',
                'meta_description' => 'Hungary VAT compliance in InvoiceKit: the 27% standard rate, the highest in the EU, alongside reduced and zero rates, plus UBL 2.1 / Peppol BIS 3.0 e-invoicing export.',
                'body' => <<<'HTML'
<p>InvoiceKit's VAT configuration for Hungary is current, with the 27% standard rate — the EU's highest — correctly set as the default, alongside the applicable reduced and zero rates. Every Hungarian invoice can also be exported as UBL 2.1 / Peppol BIS Billing 3.0 XML.</p>
<p>Given how costly a misapplied rate can be at 27%, it's worth confirming with your accountant which category fits a given sale. On InvoiceKit's own compliance checklist, Hungary has no outstanding items today.</p>
HTML,
            ],
            [
                'slug' => 'ireland-compliance-status',
                'country' => 'Ireland',
                'iso' => 'IE',
                'percentage' => null,
                'title' => 'Ireland: Fully Compliant',
                'meta_title' => 'Ireland VAT Rates and Peppol E-Invoicing Compliance',
                'meta_description' => 'Ireland VAT compliance in InvoiceKit: the new 9% reduced rate for meals and catering from 1 July 2026 alongside the 23% standard rate, plus UBL 2.1 / Peppol BIS 3.0 export.',
                'body' => <<<'HTML'
<p>Ireland's VAT rate options in InvoiceKit are current, with a new 9% reduced rate for meals and restaurant/catering services available for invoices dated from 1 July 2026 onward, alongside the existing 23% standard rate — selectable per line. Every Irish invoice can also be exported as UBL 2.1 / Peppol BIS Billing 3.0 XML.</p>
<p>Which items qualify for 9% is a judgment call best confirmed with your accountant. On InvoiceKit's own compliance checklist, Ireland has no outstanding items today.</p>
HTML,
            ],
            [
                'slug' => 'lithuania-compliance-status',
                'country' => 'Lithuania',
                'iso' => 'LT',
                'percentage' => null,
                'title' => 'Lithuania: Fully Compliant',
                'meta_title' => 'Lithuania VAT Rates and Peppol E-Invoicing',
                'meta_description' => 'Lithuania VAT compliance in InvoiceKit: accommodation, transport and culture moved from 9% to 12% and books dropped to 5%, plus UBL 2.1 / Peppol BIS 3.0 e-invoicing export.',
                'body' => <<<'HTML'
<p>Lithuania's reduced VAT rates in InvoiceKit are current: accommodation, transport, and culture moved from 9% to 12%, and books dropped from 9% to 5%, both live in the invoice-line dropdown. Every Lithuanian invoice can also be exported as UBL 2.1 / Peppol BIS Billing 3.0 XML.</p>
<p>Existing drafts with the old rates won't update automatically, so it's worth a quick review before sending. On InvoiceKit's own compliance checklist, Lithuania has no outstanding items today.</p>
HTML,
            ],
            [
                'slug' => 'luxembourg-compliance-status',
                'country' => 'Luxembourg',
                'iso' => 'LU',
                'percentage' => null,
                'title' => 'Luxembourg: Fully Compliant',
                'meta_title' => 'Luxembourg VAT Rates and Peppol E-Invoicing',
                'meta_description' => 'Luxembourg VAT compliance in InvoiceKit: the 17% standard rate, the lowest in the EU, plus UBL 2.1 / Peppol BIS 3.0 export for public-sector and corporate buyers.',
                'body' => <<<'HTML'
<p>InvoiceKit's VAT configuration for Luxembourg is current, with the 17% standard rate — the EU's lowest — correctly set as the default, alongside applicable reduced and zero rates. Every Luxembourg invoice can also be exported as UBL 2.1 / Peppol BIS Billing 3.0 XML, useful for public-sector or larger corporate buyers who expect structured e-invoices.</p>
<p>Given how low the rate is compared to other countries you may invoice in, it's worth confirming with your accountant that 17% is genuinely correct for a given sale. On InvoiceKit's own compliance checklist, Luxembourg has no outstanding items today.</p>
HTML,
            ],
            [
                'slug' => 'latvia-compliance-status',
                'country' => 'Latvia',
                'iso' => 'LV',
                'percentage' => null,
                'title' => 'Latvia: Fully Compliant',
                'meta_title' => 'Latvia VAT Rates and Peppol E-Invoicing Compliance',
                'meta_description' => 'Latvia VAT compliance in InvoiceKit: the temporary 12% rate on essential foods and the language-dependent 5% book rate, plus UBL 2.1 / Peppol BIS 3.0 e-invoicing export.',
                'body' => <<<'HTML'
<p>Latvia's VAT rate options in InvoiceKit are current: a temporary 12% rate applies to essential foods (bread, fresh milk, poultry, eggs) for invoices dated between 1 July 2026 and 30 June 2027, and the 5% book rate now depends on publication language per a January 2026 law change. Every Latvian invoice can also be exported as UBL 2.1 / Peppol BIS Billing 3.0 XML.</p>
<p>Confirm book-language eligibility with your accountant. On InvoiceKit's own compliance checklist, Latvia has no outstanding items today.</p>
HTML,
            ],
            [
                'slug' => 'malta-compliance-status',
                'country' => 'Malta',
                'iso' => 'MT',
                'percentage' => null,
                'title' => 'Malta: Fully Compliant',
                'meta_title' => 'Malta VAT Rates and Peppol E-Invoicing Compliance',
                'meta_description' => 'Malta VAT compliance in InvoiceKit: the 18% standard rate alongside applicable reduced and zero rates, plus UBL 2.1 / Peppol BIS 3.0 e-invoicing export for every invoice.',
                'body' => <<<'HTML'
<p>InvoiceKit's VAT configuration for Malta is current, with the 18% standard rate correctly set as the default, alongside applicable reduced and zero rates. Every Malta invoice can also be exported as UBL 2.1 / Peppol BIS Billing 3.0 XML, ready to hand off to your accountant or downstream systems.</p>
<p>Which category a given sale falls into is a matter for your accountant to confirm, not something InvoiceKit determines automatically. On InvoiceKit's own compliance checklist, Malta has no outstanding items today.</p>
HTML,
            ],
            [
                'slug' => 'netherlands-compliance-status',
                'country' => 'Netherlands',
                'iso' => 'NL',
                'percentage' => null,
                'title' => 'Netherlands: Fully Compliant',
                'meta_title' => 'Netherlands VAT Rates and Peppol E-Invoicing',
                'meta_description' => 'Netherlands VAT compliance in InvoiceKit: the 21% standard rate and a wide set of reduced and zero rates, plus UBL 2.1 / Peppol export for Dutch public-sector buyers.',
                'body' => <<<'HTML'
<p>InvoiceKit's VAT configuration for the Netherlands is current, with the 21% standard rate correctly set as the default, alongside the country's wide set of reduced and zero rates. Every Dutch invoice can also be exported as UBL 2.1 / Peppol BIS Billing 3.0 XML — a head start if you sell to Dutch public-sector buyers, who have long required Peppol-based e-invoicing.</p>
<p>Which rate category applies to a specific sale is a question for your accountant. On InvoiceKit's own compliance checklist, the Netherlands has no outstanding items today.</p>
HTML,
            ],
            [
                'slug' => 'portugal-compliance-status',
                'country' => 'Portugal',
                'iso' => 'PT',
                'percentage' => null,
                'title' => 'Portugal: Fully Compliant',
                'meta_title' => 'Portugal VAT Rates and Peppol E-Invoicing Compliance',
                'meta_description' => 'Portugal VAT compliance in InvoiceKit: the 23% standard rate alongside applicable reduced and zero rates, plus UBL 2.1 / Peppol BIS 3.0 e-invoicing export for every invoice.',
                'body' => <<<'HTML'
<p>InvoiceKit's VAT configuration for Portugal is current, with the 23% standard rate correctly set as the default, alongside applicable reduced and zero rates. Every Portuguese invoice can also be exported as UBL 2.1 / Peppol BIS Billing 3.0 XML, a useful standards-based starting point regardless of which specific electronic invoicing requirements apply to your business.</p>
<p>Which category a given sale falls into is a matter for your accountant. On InvoiceKit's own compliance checklist, Portugal has no outstanding items today.</p>
HTML,
            ],
            [
                'slug' => 'sweden-compliance-status',
                'country' => 'Sweden',
                'iso' => 'SE',
                'percentage' => null,
                'title' => 'Sweden: Fully Compliant',
                'meta_title' => 'Sweden VAT Rates and Peppol E-Invoicing Compliance',
                'meta_description' => 'Sweden VAT compliance in InvoiceKit: the temporary 6% rate on food, beverages and takeaway through 31 December 2027, plus UBL 2.1 / Peppol BIS 3.0 e-invoicing export.',
                'body' => <<<'HTML'
<p>Sweden's VAT rate options in InvoiceKit are current: a temporary 6% rate on food, beverages, and takeaway applies to invoices dated between 1 April 2026 and 31 December 2027, appearing and disappearing automatically at the window's edges. Every Swedish invoice can also be exported as UBL 2.1 / Peppol BIS Billing 3.0 XML.</p>
<p>Whether a specific product qualifies for the temporary rate is worth confirming with your accountant. On InvoiceKit's own compliance checklist, Sweden has no outstanding items today.</p>
HTML,
            ],
            [
                'slug' => 'slovenia-compliance-status',
                'country' => 'Slovenia',
                'iso' => 'SI',
                'percentage' => null,
                'title' => 'Slovenia: Fully Compliant',
                'meta_title' => 'Slovenia VAT Rates and Peppol E-Invoicing Compliance',
                'meta_description' => 'Slovenia VAT compliance in InvoiceKit: the 22% standard rate alongside applicable reduced and zero rates, plus UBL 2.1 / Peppol BIS 3.0 e-invoicing export for every invoice.',
                'body' => <<<'HTML'
<p>InvoiceKit's VAT configuration for Slovenia is current, with the 22% standard rate correctly set as the default, alongside applicable reduced and zero rates. Every Slovenian invoice can also be exported as UBL 2.1 / Peppol BIS Billing 3.0 XML, ready to hand off to your accountant or any downstream platform.</p>
<p>Which category a given sale falls into is worth confirming with your accountant. On InvoiceKit's own compliance checklist, Slovenia has no outstanding items today.</p>
HTML,
            ],
            [
                'slug' => 'slovakia-compliance-status',
                'country' => 'Slovakia',
                'iso' => 'SK',
                'percentage' => null,
                'title' => 'Slovakia: Fully Compliant',
                'meta_title' => 'Slovakia VAT Rates and Peppol E-Invoicing',
                'meta_description' => 'Slovakia VAT compliance in InvoiceKit: sugary and salty processed foods moved from 19% to 23% from 1 January 2026, plus UBL 2.1 / Peppol BIS 3.0 e-invoicing export.',
                'body' => <<<'HTML'
<p>Slovakia's VAT rate options in InvoiceKit are current: processed foods high in sugar or salt — confectionery, sweetened drinks, salty snacks — moved from the 19% reduced rate to the 23% standard rate for invoices dated from 1 January 2026, while other reduced-rate foodstuffs are unaffected. Every Slovak invoice can also be exported as UBL 2.1 / Peppol BIS Billing 3.0 XML.</p>
<p>Confirm product classification with your accountant if unsure. On InvoiceKit's own compliance checklist, Slovakia has no outstanding items today.</p>
HTML,
            ],

            // Bucket B - partial compliance, "path to success". Percentage is InvoiceKit's
            // own tracked engineering-checklist progress, never a government certification.
            [
                'slug' => 'belgium-compliance-status',
                'country' => 'Belgium',
                'iso' => 'BE',
                'percentage' => 75,
                'title' => 'Belgium: 75% of the Way to Full E-Invoicing Compliance',
                'meta_title' => 'Belgium E-Invoicing Compliance: 75% Complete',
                'meta_description' => 'InvoiceKit is 75% of the way to full Belgian e-invoicing compliance: UBL 2.1 / Peppol BIS 3.0 export and IBAN support are live, with Peppol network transmission still ahead.',
                'body' => <<<'HTML'
<p>Belgium's UBL 2.1 / Peppol BIS 3.0 export is fully built, and Peppol invoices can now include your IBAN automatically — required for BIS 3.0 credit-transfer payment details — once it's set on your company profile. InvoiceKit also blocks sending an invoice in the UI until that structured export exists, so nothing goes out without it.</p>
<p>What's not done: actual network transmission over the Peppol network itself, which needs a business decision on an access-point partner rather than more engineering work. This 75% reflects InvoiceKit's own internal engineering checklist for Belgium — it is not a government compliance certification. We'll share progress here as the access-point decision moves forward.</p>
HTML,
            ],
            [
                'slug' => 'croatia-compliance-status',
                'country' => 'Croatia',
                'iso' => 'HR',
                'percentage' => 75,
                'title' => 'Croatia: 75% Down the Compliance Checklist',
                'meta_title' => 'Croatia E-Invoicing Compliance: 75% Complete',
                'meta_description' => 'InvoiceKit is 75% of the way to full Croatian e-invoicing compliance: UBL 2.1 / Peppol BIS 3.0 export is live, with Peppol network transmission and Fiscalization still ahead.',
                'body' => <<<'HTML'
<p>Croatia's structured UBL 2.1 / Peppol BIS 3.0 export is fully built, matching the same format work completed for Belgium, and both issuing and receiving e-invoices are confirmed mandatory for in-scope Croatian businesses. VAT rates are current, with reduced and zero rates available alongside the 25% standard.</p>
<p>What remains is the same gap as Belgium's: actual transmission over the Peppol network, which needs a business decision on an access-point partner, not further code. Croatia's Fiscalization system itself also isn't connected. This 75% reflects InvoiceKit's own internal engineering checklist for Croatia — it is not a government compliance certification.</p>
HTML,
            ],
            [
                'slug' => 'bulgaria-compliance-status',
                'country' => 'Bulgaria',
                'iso' => 'BG',
                'percentage' => 67,
                'title' => 'Bulgaria: 67% Complete — Our Most Detailed VAT Setup Yet',
                'meta_title' => 'Bulgaria E-Invoicing Compliance: 67% Complete',
                'meta_description' => 'InvoiceKit is 67% toward full Bulgarian compliance, with the most detailed VAT rate setup of any supported country and UBL 2.1 / Peppol export live; a SAF-T export is still ahead.',
                'body' => <<<'HTML'
<p>Bulgaria has InvoiceKit's most complete VAT configuration of any supported country: the 20% standard rate, a 9% tourism rate, and separate 0% options for exports, intra-EU supplies, financial and insurance services, education, healthcare, real estate, postal, cultural, and social services. UBL 2.1 / Peppol export is available for every invoice.</p>
<p>The remaining gap is a SAF-T audit-file export for large taxpayers, which we're holding off building until we can confirm it against the real Bulgarian NRA schema rather than guess at it. This 67% reflects InvoiceKit's own internal engineering checklist for Bulgaria — it is not a government compliance certification.</p>
HTML,
            ],
            [
                'slug' => 'greece-compliance-status',
                'country' => 'Greece',
                'iso' => 'GR',
                'percentage' => 67,
                'title' => 'Greece: 67% of the Way, With myDATA Still Ahead',
                'meta_title' => 'Greece E-Invoicing Compliance: 67% Complete',
                'meta_description' => 'InvoiceKit is 67% toward full Greek compliance: VAT rates and UBL 2.1 / Peppol export are current, with myDATA electronic bookkeeping integration still ahead.',
                'body' => <<<'HTML'
<p>Greece's 24% standard VAT rate is correct and current, and every Greek invoice can be exported as UBL 2.1 / Peppol BIS Billing 3.0 XML. The remaining gap is myDATA, Greece's electronic bookkeeping system: InvoiceKit has no live integration transmitting invoice data to the Greek tax authority.</p>
<p>Even once that integration is built, going live requires AADE-issued credentials that only a registered business can obtain. Until then, a separate connection or provider is needed for myDATA reporting. This 67% reflects InvoiceKit's own internal engineering checklist for Greece — it is not a government compliance certification.</p>
HTML,
            ],
            [
                'slug' => 'italy-compliance-status',
                'country' => 'Italy',
                'iso' => 'IT',
                'percentage' => 50,
                'title' => 'Italy: 50% of the Way — SdI Is the Big One Left',
                'meta_title' => 'Italy E-Invoicing Compliance: 50% Complete',
                'meta_description' => 'InvoiceKit is 50% toward full Italian compliance: VAT rates and UBL 2.1 / Peppol export are current, but Sistema di Interscambio (SdI) integration, mandatory since 2019, is not yet built.',
                'body' => <<<'HTML'
<p>Italy's 22% standard VAT rate is correct and current, with UBL 2.1 / Peppol export available for every invoice. We want to be direct about the other half: Italy's Sistema di Interscambio has been the mandatory B2B clearance channel since 2019, and InvoiceKit has no FatturaPA/SdI integration at all.</p>
<p>This is a bigger, older gap than some other countries' remaining work, not a small finishing touch. If SdI applies to you, you need a dedicated FatturaPA solution today. This 50% reflects InvoiceKit's own internal engineering checklist for Italy — it is not a government compliance certification.</p>
HTML,
            ],
            [
                'slug' => 'poland-compliance-status',
                'country' => 'Poland',
                'iso' => 'PL',
                'percentage' => 42,
                'title' => 'Poland: 42% Along the Path to KSeF Readiness',
                'meta_title' => 'Poland E-Invoicing Compliance: 42% Complete',
                'meta_description' => 'InvoiceKit is 42% toward full Polish KSeF compliance: VAT rates and a KSeF reference field are live, with FA(3) XML generation and live KSeF submission still ahead.',
                'body' => <<<'HTML'
<p>Poland's 23% standard VAT rate is correct and current, UBL 2.1 / Peppol export is available for every invoice, and you can now record a KSeF reference number on an invoice once it's been issued elsewhere through KSeF — that number then shows automatically on the invoice's bank-transfer payment reference.</p>
<p>What's still missing is the real work: generating KSeF's own FA(3) XML format and submitting live to KSeF, both of which need the actual Polish Ministry of Finance schema and API credentials before they can be built correctly. This 42% reflects InvoiceKit's own internal engineering checklist for Poland — it is not a government compliance certification.</p>
HTML,
            ],
            [
                'slug' => 'spain-compliance-status',
                'country' => 'Spain',
                'iso' => 'ES',
                'percentage' => 40,
                'title' => 'Spain: 40% of the Way — Here\'s What\'s Left',
                'meta_title' => 'Spain E-Invoicing Compliance: 40% Complete',
                'meta_description' => 'InvoiceKit is 40% toward full Spanish compliance. VAT rates and UBL 2.1 / Peppol export are current, but VeriFactu certification, required under penalty since July 2025, is not yet built.',
                'body' => <<<'HTML'
<p>Spain's 21% standard VAT rate is correct and current, with UBL 2.1 / Peppol export available for every invoice. The honest number for what remains is VeriFactu, and we want to be direct rather than upbeat about it: Spain has attached penalties of up to €150,000 per year, since 29 July 2025, to billing software that misrepresents its compliance status.</p>
<p>InvoiceKit is not VeriFactu-certified and does not connect to the Spanish tax authority. If VeriFactu applies to your business, you need software built specifically for it. This 40% reflects InvoiceKit's own internal engineering checklist for Spain — it is not a government compliance certification.</p>
HTML,
            ],
            [
                'slug' => 'france-compliance-status',
                'country' => 'France',
                'iso' => 'FR',
                'percentage' => 40,
                'title' => 'France: 40% Complete Ahead of the September 2026 Mandate',
                'meta_title' => 'France E-Invoicing Compliance: 40% Complete',
                'meta_description' => 'InvoiceKit is 40% toward full French e-invoicing compliance ahead of the September 2026 mandate. Factur-X generation, inbound reception and Plateforme Agréée connectivity are still ahead.',
                'body' => <<<'HTML'
<p>France's 20% standard VAT rate is correct and current, with UBL 2.1 / Peppol export available for every invoice. What's genuinely urgent: France's e-invoicing reform starts 1 September 2026, when receiving becomes universal and larger and mid-size businesses must also issue through it — that's imminent, not distant.</p>
<p>The remaining gaps are real: Factur-X 1.09 generation, an inbound reception path, and connectivity to a Plateforme Agréée are all still ahead of us. This 40% reflects InvoiceKit's own internal engineering checklist for France — it is not a government compliance certification. We'll have more to share as the date approaches.</p>
HTML,
            ],
            [
                'slug' => 'romania-compliance-status',
                'country' => 'Romania',
                'iso' => 'RO',
                'percentage' => 20,
                'title' => 'Romania: 20% of the Way, With Most of the Work Still Ahead',
                'meta_title' => 'Romania E-Invoicing Compliance: 20% Complete',
                'meta_description' => 'InvoiceKit is 20% toward full Romanian e-Factura compliance. VAT rates and UBL 2.1 / Peppol export are current, with ANAF transmission and digital seals still on the roadmap.',
                'body' => <<<'HTML'
<p>Romania's VAT rates are correct and current in InvoiceKit — 21% standard, 11% consolidated reduced — and UBL 2.1 / Peppol export is available for every invoice. Beyond that, most of what RO e-Factura compliance actually requires is still on the roadmap: live transmission to ANAF, the 5-working-day submission window, digital seals, and 10-year archiving.</p>
<p>None of this can go live without ANAF-issued credentials, which gates real progress regardless of how much we build in the meantime. This 20% reflects InvoiceKit's own internal engineering checklist for Romania — it is not a government compliance certification.</p>
HTML,
            ],
        ];
    }
}
