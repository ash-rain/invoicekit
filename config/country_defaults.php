<?php

/**
 * Country-specific defaults used throughout the application.
 *
 * Keys per entry:
 *   currency                  — Default currency (must be in config('invoicekit.supported_currencies'))
 *   language                  — Default invoice language (must be in config('invoicekit.supported_languages'))
 *   invoice_numbering_format  — 'standard' (INV-YYYY-NNNN) or 'bg_sequential' (10-digit continuous)
 *   vat_exempt_default        — Whether newly created companies in this country default to VAT-exempt
 *   registration_number_label — Localized name for the national company registration identifier
 *   registration_number_hint  — Short example / placeholder for the registration number field
 *   lookup_gemini_context     — One-sentence hint for the Gemini company-lookup prompt
 */

return [

    // ─────────────────────────────────────────
    // European Union
    // ─────────────────────────────────────────

    'AT' => [
        'currency' => 'EUR',
        'language' => 'de',
        'invoice_numbering_format' => 'standard',
        'vat_exempt_default' => false,
        'registration_number_label' => 'Firmenbuchnummer',
        'registration_number_hint' => 'FN 123456a',
        'lookup_gemini_context' => 'Search the Austrian company register (Firmenbuch, firmenbuch.at). The Firmenbuchnummer starts with "FN" followed by digits and a letter.',
    ],

    'BE' => [
        'currency' => 'EUR',
        'language' => 'fr',
        'invoice_numbering_format' => 'standard',
        'vat_exempt_default' => false,
        'registration_number_label' => 'KBO/BCE-nummer',
        'registration_number_hint' => '0123.456.789',
        'lookup_gemini_context' => 'Search the Belgian Crossroads Bank for Enterprises (KBO/BCE, kbo.fgov.be). The enterprise number is a 10-digit number in the format 0123.456.789.',
    ],

    'BG' => [
        'currency' => 'EUR',
        'language' => 'bg',
        'invoice_numbering_format' => 'bg_sequential',
        'vat_exempt_default' => true,
        'registration_number_label' => 'ЕИК',
        'registration_number_hint' => '123456789',
        'lookup_gemini_context' => 'Search the Bulgarian Commercial Register (Търговски регистър) at registry.brra.bg or sova.bg. The EIK (ЕИК/БУЛСТАТ) is a 9 or 10-digit national company identifier — it is NOT a VAT number. A BG company may have a VAT number like BG{digits} that coincidentally contains the same digits as an EIK, but that VAT registration belongs to a different legal entity. Always match by EIK, not by VAT number.',
    ],

    'HR' => [
        'currency' => 'EUR',
        'language' => 'hr',
        'invoice_numbering_format' => 'standard',
        'vat_exempt_default' => false,
        'registration_number_label' => 'OIB',
        'registration_number_hint' => '12345678901',
        'lookup_gemini_context' => 'Search the Croatian Personal Identification Number system (OIB). The OIB is an 11-digit national identification number for both persons and legal entities.',
    ],

    'CY' => [
        'currency' => 'EUR',
        'language' => 'el',
        'invoice_numbering_format' => 'standard',
        'vat_exempt_default' => false,
        'registration_number_label' => 'Registration Number',
        'registration_number_hint' => 'HE 123456',
        'lookup_gemini_context' => 'Search the Cyprus Registrar of Companies (efiling.drcor.mcit.gov.cy). The company registration number typically starts with "HE" for limited liability companies.',
    ],

    'CZ' => [
        'currency' => 'CZK',
        'language' => 'cs',
        'invoice_numbering_format' => 'standard',
        'vat_exempt_default' => false,
        'registration_number_label' => 'IČO',
        'registration_number_hint' => '12345678',
        'lookup_gemini_context' => 'Search the Czech Business Register (Obchodní rejstřík, or.justice.cz). The IČO (Identifikační číslo osoby) is an 8-digit company identifier.',
    ],

    'DK' => [
        'currency' => 'EUR',
        'language' => 'da',
        'invoice_numbering_format' => 'standard',
        'vat_exempt_default' => false,
        'registration_number_label' => 'CVR-nummer',
        'registration_number_hint' => '12345678',
        'lookup_gemini_context' => 'Search the Danish Central Business Register (CVR, cvr.dk). The CVR-nummer is an 8-digit unique company identifier.',
    ],

    'EE' => [
        'currency' => 'EUR',
        'language' => 'et',
        'invoice_numbering_format' => 'standard',
        'vat_exempt_default' => false,
        'registration_number_label' => 'Registrikood',
        'registration_number_hint' => '12345678',
        'lookup_gemini_context' => 'Search the Estonian Business Register (e-äriregister, ariregister.rik.ee). The registrikood is an 8-digit unique company identifier.',
    ],

    'FI' => [
        'currency' => 'EUR',
        'language' => 'fi',
        'invoice_numbering_format' => 'standard',
        'vat_exempt_default' => false,
        'registration_number_label' => 'Y-tunnus',
        'registration_number_hint' => '1234567-8',
        'lookup_gemini_context' => 'Search the Finnish Business Information System (YTJ, ytj.fi). The Y-tunnus (business ID) is a 7-digit number followed by a hyphen and a check digit.',
    ],

    'FR' => [
        'currency' => 'EUR',
        'language' => 'fr',
        'invoice_numbering_format' => 'standard',
        'vat_exempt_default' => false,
        'registration_number_label' => 'SIRET',
        'registration_number_hint' => '12345678901234',
        'lookup_gemini_context' => 'Search the French company registry (INSEE/SIRENE or Infogreffe). The SIRET is a 14-digit number; the first 9 digits are the SIREN which identifies the company.',
    ],

    'DE' => [
        'currency' => 'EUR',
        'language' => 'de',
        'invoice_numbering_format' => 'standard',
        'vat_exempt_default' => false,
        'registration_number_label' => 'Handelsregisternummer',
        'registration_number_hint' => 'HRB 12345',
        'lookup_gemini_context' => 'Search the German commercial register (Handelsregister, handelsregister.de). The Handelsregisternummer is formatted as HRA or HRB followed by digits, per local court (Amtsgericht).',
    ],

    'GR' => [
        'currency' => 'EUR',
        'language' => 'el',
        'invoice_numbering_format' => 'standard',
        'vat_exempt_default' => false,
        'registration_number_label' => 'ΓΕΜΗ / ΑΦΜ',
        'registration_number_hint' => '123456789012',
        'lookup_gemini_context' => 'Search the Greek General Commercial Registry (ΓΕΜΗ, businessregistry.gr) or the Greek tax authority (AADE). The ΓΕΜΗ number is a 12-digit registration number.',
    ],

    'HU' => [
        'currency' => 'HUF',
        'language' => 'hu',
        'invoice_numbering_format' => 'standard',
        'vat_exempt_default' => false,
        'registration_number_label' => 'Cégjegyzékszám',
        'registration_number_hint' => '01-09-123456',
        'lookup_gemini_context' => 'Search the Hungarian Company Court (e-IgazságügyCégbíróság, ceginformacio.im.gov.hu). The Cégjegyzékszám is formatted as XX-YY-NNNNNN.',
    ],

    'IE' => [
        'currency' => 'EUR',
        'language' => 'en',
        'invoice_numbering_format' => 'standard',
        'vat_exempt_default' => false,
        'registration_number_label' => 'CRO Number',
        'registration_number_hint' => '123456',
        'lookup_gemini_context' => 'Search the Irish Companies Registration Office (CRO, cro.ie). The CRO number is a 6-digit company registration number.',
    ],

    'IT' => [
        'currency' => 'EUR',
        'language' => 'it',
        'invoice_numbering_format' => 'standard',
        'vat_exempt_default' => false,
        'registration_number_label' => 'Codice Fiscale',
        'registration_number_hint' => '12345678901',
        'lookup_gemini_context' => 'Search the Italian business registry (Registro Imprese, registroimprese.it). The Codice Fiscale for companies is an 11-digit tax code; the CCIAA number identifies the chamber of commerce.',
    ],

    'LV' => [
        'currency' => 'EUR',
        'language' => 'lv',
        'invoice_numbering_format' => 'standard',
        'vat_exempt_default' => false,
        'registration_number_label' => 'Reģistrācijas numurs',
        'registration_number_hint' => '40003012345',
        'lookup_gemini_context' => 'Search the Latvian Company Register (Uzņēmumu reģistrs, ur.gov.lv). The reģistrācijas numurs is an 11-digit number.',
    ],

    'LT' => [
        'currency' => 'EUR',
        'language' => 'lt',
        'invoice_numbering_format' => 'standard',
        'vat_exempt_default' => false,
        'registration_number_label' => 'Įmonės kodas',
        'registration_number_hint' => '123456789',
        'lookup_gemini_context' => 'Search the Lithuanian Register of Legal Entities (JAR, rekvizitai.lt or registrucentras.lt). The Įmonės kodas is a 9-digit company code.',
    ],

    'LU' => [
        'currency' => 'EUR',
        'language' => 'fr',
        'invoice_numbering_format' => 'standard',
        'vat_exempt_default' => false,
        'registration_number_label' => 'Numéro RCS',
        'registration_number_hint' => 'B123456',
        'lookup_gemini_context' => 'Search the Luxembourg Trade and Companies Register (Registre de Commerce et des Sociétés, rcs.lu). The RCS number starts with a letter (A, B, C, D) followed by digits.',
    ],

    'MT' => [
        'currency' => 'EUR',
        'language' => 'mt',
        'invoice_numbering_format' => 'standard',
        'vat_exempt_default' => false,
        'registration_number_label' => 'Registration Number',
        'registration_number_hint' => 'C 12345',
        'lookup_gemini_context' => 'Search the Malta Business Registry (mbr.mt). Company registration numbers typically start with "C" for companies.',
    ],

    'NL' => [
        'currency' => 'EUR',
        'language' => 'nl',
        'invoice_numbering_format' => 'standard',
        'vat_exempt_default' => false,
        'registration_number_label' => 'KVK-nummer',
        'registration_number_hint' => '12345678',
        'lookup_gemini_context' => 'Search the Dutch Chamber of Commerce register (KvK, kvk.nl). The KVK-nummer is an 8-digit unique company identifier.',
    ],

    'PL' => [
        'currency' => 'PLN',
        'language' => 'pl',
        'invoice_numbering_format' => 'standard',
        'vat_exempt_default' => false,
        'registration_number_label' => 'KRS / NIP',
        'registration_number_hint' => '0000123456',
        'lookup_gemini_context' => 'Search the Polish National Court Register (KRS, ekrs.ms.gov.pl). The KRS number is a 10-digit company identifier; the NIP (tax ID) is a 10-digit tax number.',
    ],

    'PT' => [
        'currency' => 'EUR',
        'language' => 'pt',
        'invoice_numbering_format' => 'standard',
        'vat_exempt_default' => false,
        'registration_number_label' => 'NIPC',
        'registration_number_hint' => '123456789',
        'lookup_gemini_context' => 'Search the Portuguese company registry (Conservatória do Registo Comercial, rcbe.mj.pt). The NIPC (Número de Identificação de Pessoa Coletiva) is a 9-digit number.',
    ],

    'RO' => [
        'currency' => 'RON',
        'language' => 'ro',
        'invoice_numbering_format' => 'standard',
        'vat_exempt_default' => false,
        'registration_number_label' => 'CUI / CIF',
        'registration_number_hint' => 'RO12345678',
        'lookup_gemini_context' => 'Search the Romanian Trade Register (ONRC, onrc.ro). The CUI (Cod Unic de Înregistrare) / CIF is a numeric tax identification code.',
    ],

    'SK' => [
        'currency' => 'EUR',
        'language' => 'sk',
        'invoice_numbering_format' => 'standard',
        'vat_exempt_default' => false,
        'registration_number_label' => 'IČO',
        'registration_number_hint' => '12345678',
        'lookup_gemini_context' => 'Search the Slovak Business Register (Obchodný register SR, orsr.sk). The IČO is an 8-digit company identifier.',
    ],

    'SI' => [
        'currency' => 'EUR',
        'language' => 'sl',
        'invoice_numbering_format' => 'standard',
        'vat_exempt_default' => false,
        'registration_number_label' => 'Matična številka',
        'registration_number_hint' => '1234567000',
        'lookup_gemini_context' => 'Search the Slovenian Business Register (AJPES, ajpes.si). The matična številka is a 10-digit company registration number.',
    ],

    'ES' => [
        'currency' => 'EUR',
        'language' => 'es',
        'invoice_numbering_format' => 'standard',
        'vat_exempt_default' => false,
        'registration_number_label' => 'CIF / NIF',
        'registration_number_hint' => 'B12345678',
        'lookup_gemini_context' => 'Search the Spanish Mercantile Registry (Registro Mercantil, registradores.org). Company CIF starts with a letter (A=SA, B=SL, etc.) followed by 8 digits.',
    ],

    'SE' => [
        'currency' => 'EUR',
        'language' => 'sv',
        'invoice_numbering_format' => 'standard',
        'vat_exempt_default' => false,
        'registration_number_label' => 'Organisationsnummer',
        'registration_number_hint' => '556123-4567',
        'lookup_gemini_context' => 'Search the Swedish Companies Registration Office (Bolagsverket, bolagsverket.se). The organisationsnummer is a 10-digit number formatted as NNNNNN-NNNN.',
    ],

    // ─────────────────────────────────────────
    // Non-EU European countries
    // ─────────────────────────────────────────

    'GB' => [
        'currency' => 'EUR',
        'language' => 'en',
        'invoice_numbering_format' => 'standard',
        'vat_exempt_default' => false,
        'registration_number_label' => 'Company Number',
        'registration_number_hint' => '12345678',
        'lookup_gemini_context' => 'Search Companies House (companies house.gov.uk). The UK company number is an 8-character code (digits or starting with SC for Scotland, NI for Northern Ireland).',
    ],

    'CH' => [
        'currency' => 'EUR',
        'language' => 'de',
        'invoice_numbering_format' => 'standard',
        'vat_exempt_default' => false,
        'registration_number_label' => 'UID',
        'registration_number_hint' => 'CHE-123.456.789',
        'lookup_gemini_context' => 'Search the Swiss Central Business Name Index (Zefix, zefix.ch). The UID (Unternehmens-Identifikationsnummer) is formatted as CHE-NNN.NNN.NNN.',
    ],

    'NO' => [
        'currency' => 'EUR',
        'language' => 'en',
        'invoice_numbering_format' => 'standard',
        'vat_exempt_default' => false,
        'registration_number_label' => 'Organisasjonsnummer',
        'registration_number_hint' => '123456789',
        'lookup_gemini_context' => 'Search the Norwegian Register of Business Enterprises (Brønnøysundregistrene, brreg.no). The organisasjonsnummer is a 9-digit number.',
    ],

    // ─────────────────────────────────────────
    // Rest of the world
    // ─────────────────────────────────────────

    'US' => [
        'currency' => 'USD',
        'language' => 'en',
        'invoice_numbering_format' => 'standard',
        'vat_exempt_default' => false,
        'registration_number_label' => 'EIN',
        'registration_number_hint' => '12-3456789',
        'lookup_gemini_context' => 'Search the US IRS or SEC EDGAR (sec.gov) for the company. The EIN (Employer Identification Number) is a 9-digit number formatted as XX-XXXXXXX.',
    ],

    'AU' => [
        'currency' => 'USD',
        'language' => 'en',
        'invoice_numbering_format' => 'standard',
        'vat_exempt_default' => false,
        'registration_number_label' => 'ABN',
        'registration_number_hint' => '12 345 678 901',
        'lookup_gemini_context' => 'Search the Australian Business Register (ABR, abr.gov.au). The ABN (Australian Business Number) is an 11-digit number.',
    ],

    'CA' => [
        'currency' => 'USD',
        'language' => 'en',
        'invoice_numbering_format' => 'standard',
        'vat_exempt_default' => false,
        'registration_number_label' => 'Business Number',
        'registration_number_hint' => '123456789',
        'lookup_gemini_context' => 'Search the Canada Revenue Agency (CRA) or provincial registry. The BN (Business Number) is a 9-digit number assigned by the CRA.',
    ],

    'JP' => [
        'currency' => 'USD',
        'language' => 'en',
        'invoice_numbering_format' => 'standard',
        'vat_exempt_default' => false,
        'registration_number_label' => '法人番号',
        'registration_number_hint' => '1234567890123',
        'lookup_gemini_context' => 'Search the Japanese Corporate Number Publication Site (法人番号公表サイト, houjin-bangou.nta.go.jp). The corporate number (法人番号) is a 13-digit identifier.',
    ],

    'CN' => [
        'currency' => 'USD',
        'language' => 'en',
        'invoice_numbering_format' => 'standard',
        'vat_exempt_default' => false,
        'registration_number_label' => '统一社会信用代码',
        'registration_number_hint' => '91110000000000000X',
        'lookup_gemini_context' => 'Search the Chinese National Enterprise Credit Information Publicity System (国家企业信用信息公示系统, gsxt.gov.cn). The 统一社会信用代码 (Unified Social Credit Code) is an 18-character identifier.',
    ],

    'IN' => [
        'currency' => 'USD',
        'language' => 'en',
        'invoice_numbering_format' => 'standard',
        'vat_exempt_default' => false,
        'registration_number_label' => 'CIN / GSTIN',
        'registration_number_hint' => 'U12345MH2020PTC123456',
        'lookup_gemini_context' => 'Search the Indian Ministry of Corporate Affairs (MCA21, mca.gov.in). The CIN (Corporate Identification Number) is a 21-character alphanumeric code.',
    ],

    'BR' => [
        'currency' => 'USD',
        'language' => 'pt',
        'invoice_numbering_format' => 'standard',
        'vat_exempt_default' => false,
        'registration_number_label' => 'CNPJ',
        'registration_number_hint' => '12.345.678/0001-90',
        'lookup_gemini_context' => 'Search the Brazilian Federal Revenue (Receita Federal, consulta.cnpj.net or receitafederal.gov.br). The CNPJ is a 14-digit number formatted as XX.XXX.XXX/XXXX-XX.',
    ],

    'ZA' => [
        'currency' => 'USD',
        'language' => 'en',
        'invoice_numbering_format' => 'standard',
        'vat_exempt_default' => false,
        'registration_number_label' => 'Company Reg. Number',
        'registration_number_hint' => '2020/123456/07',
        'lookup_gemini_context' => 'Search the Companies and Intellectual Property Commission (cipc.co.za). The South African company registration number is formatted as YYYY/NNNNNN/NN.',
    ],

    'SG' => [
        'currency' => 'USD',
        'language' => 'en',
        'invoice_numbering_format' => 'standard',
        'vat_exempt_default' => false,
        'registration_number_label' => 'UEN',
        'registration_number_hint' => '202012345A',
        'lookup_gemini_context' => 'Search the Singapore Accounting and Corporate Regulatory Authority (ACRA, bizfile.gov.sg). The UEN (Unique Entity Number) is a 9 or 10-character alphanumeric identifier.',
    ],

    'AE' => [
        'currency' => 'USD',
        'language' => 'en',
        'invoice_numbering_format' => 'standard',
        'vat_exempt_default' => false,
        'registration_number_label' => 'Trade Licence Number',
        'registration_number_hint' => '123456',
        'lookup_gemini_context' => 'Search the UAE trade licence database for the relevant emirate (e.g. DED for Dubai, ABUDHABI for Abu Dhabi). The trade licence number format varies by emirate.',
    ],

];
