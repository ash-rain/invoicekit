<?php

return [
    'BG' => [
        'standard' => [
            'rate' => 20,
            'label' => 'Стандартна ставка 20%',
        ],
        'reduced_tourism' => [
            'rate' => 9,
            'legal_ref' => 'чл. 66, ал. 2 ЗДДС',
            'label' => 'Намалена ставка 9% (туризъм)',
        ],
        'zero_export' => [
            'rate' => 0,
            'legal_ref' => 'чл. 28 ЗДДС',
            'label' => 'Износ извън ЕС',
        ],
        'zero_intra_eu' => [
            'rate' => 0,
            'legal_ref' => 'чл. 53 ЗДДС',
            'label' => 'Вътреобщностна доставка',
        ],
        'exempt_financial' => [
            'rate' => 0,
            'legal_ref' => 'чл. 46 ЗДДС',
            'label' => 'Освободена доставка (финансови услуги)',
        ],
        'exempt_insurance' => [
            'rate' => 0,
            'legal_ref' => 'чл. 47 ЗДДС',
            'label' => 'Освободена доставка (застрахователни услуги)',
        ],
        'exempt_education' => [
            'rate' => 0,
            'legal_ref' => 'чл. 41 ЗДДС',
            'label' => 'Освободена доставка (образование)',
        ],
        'exempt_healthcare' => [
            'rate' => 0,
            'legal_ref' => 'чл. 39 ЗДДС',
            'label' => 'Освободена доставка (здравеопазване)',
        ],
        'exempt_real_estate' => [
            'rate' => 0,
            'legal_ref' => 'чл. 45 ЗДДС',
            'label' => 'Освободена доставка (недвижими имоти)',
        ],
        'exempt_postal' => [
            'rate' => 0,
            'legal_ref' => 'чл. 49 ЗДДС',
            'label' => 'Освободена доставка (пощенски услуги)',
        ],
        'exempt_cultural' => [
            'rate' => 0,
            'legal_ref' => 'чл. 42 ЗДДС',
            'label' => 'Освободена доставка (култура и спорт)',
        ],
        'exempt_social' => [
            'rate' => 0,
            'legal_ref' => 'чл. 40 ЗДДС',
            'label' => 'Освободена доставка (социални услуги)',
        ],
    ],

    'AT' => [
        'standard' => ['rate' => 20, 'label' => 'Standardsteuersatz 20%'],
        'zero_hygiene_essential' => [
            'rate' => 0,
            'legal_ref' => 'Umsatzsteuergesetz (UStG 1994) — selected hygiene/essential goods zero-rated',
            'label' => 'Nullsatz 0% (Hygieneartikel und Grundbedarfsgüter)',
        ],
    ],
    'BE' => [
        'standard' => ['rate' => 21, 'label' => 'Tarif normal 21%'],
        'reduced_hospitality' => [
            'rate' => 12,
            'legal_ref' => 'Code de la TVA / BTW-Wetboek — hotels, takeaway food and leisure services, raised from 6% effective 1 Mar 2026',
            'label' => 'Tarif réduit 12% (hôtellerie, à emporter, loisirs)',
        ],
    ],
    'CY' => [
        'standard' => ['rate' => 19, 'label' => 'Κανονικός συντελεστής 19%'],
    ],
    'CZ' => [
        'standard' => ['rate' => 21, 'label' => 'Základní sazba 21%'],
        'reduced_restaurant' => [
            'rate' => 12,
            'legal_ref' => 'Zákon č. 235/2004 Sb., o dani z přidané hodnoty — restaurant/catering services, single consolidated reduced rate',
            'label' => 'Snížená sazba 12% (stravovací služby)',
        ],
        'zero_prescription_medicine' => [
            'rate' => 0,
            'legal_ref' => 'Zákon č. 235/2004 Sb., o dani z přidané hodnoty — prescription medicines',
            'label' => 'Nulová sazba 0% (léky na předpis)',
        ],
    ],
    'DE' => [
        'standard' => ['rate' => 19, 'label' => 'Regelsteuersatz 19%'],
        'reduced_hospitality' => [
            'rate' => 7,
            'legal_ref' => 'Umsatzsteuergesetz (UStG) — restaurant & catering services, reintroduced 1 Jan 2026',
            'label' => 'Ermäßigter Steuersatz 7% (Gastronomie)',
        ],
    ],
    'DK' => [
        'standard' => ['rate' => 25, 'label' => 'Normalsats 25%'],
    ],
    'EE' => [
        'standard' => ['rate' => 24, 'label' => 'Tavamäär 24%'],
    ],
    'EL' => [
        'standard' => ['rate' => 24, 'label' => 'Κανονικός συντελεστής 24%'],
    ],
    'ES' => [
        'standard' => ['rate' => 21, 'label' => 'Tipo general 21%'],
    ],
    'FI' => [
        'standard' => ['rate' => 25.5, 'label' => 'Yleinen verokanta 25,5%'],
        'reduced' => [
            'rate' => 13.5,
            'legal_ref' => 'Arvonlisäverolaki (1501/1993) — reduced rate lowered from 14% to 13.5%, effective 1 Jan 2026',
            'label' => 'Alennettu verokanta 13,5% (elintarvikkeet, ravintolat, majoitus, kirjat, kulttuuri, lääkkeet)',
        ],
    ],
    'FR' => [
        'standard' => ['rate' => 20, 'label' => 'Taux normal 20%'],
    ],
    'HR' => [
        'standard' => ['rate' => 25, 'label' => 'Opća stopa 25%'],
    ],
    'HU' => [
        'standard' => ['rate' => 27, 'label' => 'Általános adókulcs 27%'],
    ],
    'IE' => [
        'standard' => ['rate' => 23, 'label' => 'Standard rate 23%'],
        'reduced_hospitality' => [
            'rate' => 9,
            'legal_ref' => 'Value-Added Tax Consolidation Act 2010 — meals/restaurant services, effective 1 Jul 2026',
            'label' => 'Reduced rate 9% (food & catering services)',
        ],
    ],
    'IT' => [
        'standard' => ['rate' => 22, 'label' => 'Aliquota ordinaria 22%'],
    ],
    'LT' => [
        'standard' => ['rate' => 21, 'label' => 'Standartinis tarifas 21%'],
        'reduced_accommodation_transport_culture' => [
            'rate' => 12,
            'legal_ref' => 'Pridėtinės vertės mokesčio įstatymas — accommodation/transport/culture, raised from 9% to 12%',
            'label' => 'Lengvatinis tarifas 12% (apgyvendinimas, transportas, kultūra)',
        ],
        'reduced_books' => [
            'rate' => 5,
            'legal_ref' => 'Pridėtinės vertės mokesčio įstatymas — books, lowered from 9% to 5%',
            'label' => 'Lengvatinis tarifas 5% (knygos)',
        ],
    ],
    'LU' => [
        'standard' => ['rate' => 17, 'label' => 'Taux normal 17%'],
    ],
    'LV' => [
        'standard' => ['rate' => 21, 'label' => 'Standarta likme 21%'],
        'reduced_essential_food' => [
            'rate' => 12,
            'legal_ref' => 'Pievienotās vērtības nodokļa likums — bread, fresh milk, fresh poultry meat and eggs, temporary window',
            'label' => 'Samazinātā likme 12% (maize, svaigs piens, svaiga mājputnu gaļa un olas)',
            'valid_from' => '2026-07-01',
            'valid_until' => '2027-06-30',
        ],
        'reduced_books' => [
            'rate' => 5,
            'legal_ref' => 'Pievienotās vērtības nodokļa likums — books/press restricted to Latvian, Latgalian, Livonian and specified official languages from Jan 2026; other-language publications revert to the 21% standard rate',
            'label' => 'Samazinātā likme 5% (grāmatas noteiktās valodās)',
        ],
    ],
    'MT' => [
        'standard' => ['rate' => 18, 'label' => 'Rata standard 18%'],
    ],
    'NL' => [
        'standard' => ['rate' => 21, 'label' => 'Standaardtarief 21%'],
    ],
    'PL' => [
        'standard' => ['rate' => 23, 'label' => 'Stawka podstawowa 23%'],
    ],
    'PT' => [
        'standard' => ['rate' => 23, 'label' => 'Taxa normal 23%'],
    ],
    'RO' => [
        'standard' => ['rate' => 21, 'label' => 'Cota standard 21%'],
        'reduced' => [
            'rate' => 11,
            'legal_ref' => 'Codul Fiscal (Legea nr. 227/2015) — former 5%/9% reduced rates consolidated into a single 11% rate',
            'label' => 'Cota redusă 11%',
        ],
    ],
    'SE' => [
        'standard' => ['rate' => 25, 'label' => 'Normalskattesats 25%'],
        'reduced_food_temporary' => [
            'rate' => 6,
            'legal_ref' => 'Mervärdesskattelag (2023:200) — temporary halving of VAT on food, beverages and takeaway',
            'label' => 'Tillfälligt reducerad skattesats 6% (mat och dryck)',
            'valid_from' => '2026-04-01',
            'valid_until' => '2027-12-31',
        ],
    ],
    'SI' => [
        'standard' => ['rate' => 22, 'label' => 'Splošna stopnja 22%'],
    ],
    'SK' => [
        'standard' => ['rate' => 23, 'label' => 'Základná sadzba 23%'],
        'increased_sugar_salt_products' => [
            'rate' => 23,
            'legal_ref' => 'Zákon č. 222/2004 Z. z. o dani z pridanej hodnoty — high-sugar/high-salt processed foods (confectionery, snacks) raised from 19% to the standard rate, effective 1 Jan 2026',
            'label' => 'Zvýšená sadzba 23% (potraviny s vysokým obsahom cukru/soli)',
        ],
    ],
];
