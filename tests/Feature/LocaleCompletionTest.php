<?php

namespace Tests\Feature;

use Tests\TestCase;

class LocaleCompletionTest extends TestCase
{
    /** @return array<string, array{0: string}> */
    public static function localeProvider(): array
    {
        $config = require __DIR__.'/../../config/invoicekit.php';
        $locales = $config['supported_languages'] ?? [];

        return collect($locales)
            ->filter(fn ($code) => $code !== 'en')
            ->mapWithKeys(fn ($code) => [$code => [$code]])
            ->all();
    }

    public function test_all_locales_have_json_files(): void
    {
        $locales = config('invoicekit.supported_languages', []);

        foreach ($locales as $locale) {
            $this->assertFileExists(
                resource_path("lang/{$locale}.json"),
                "Missing locale file: {$locale}.json"
            );
        }
    }

    /**
     * @dataProvider localeProvider
     */
    public function test_locale_file_contains_all_english_keys(string $locale): void
    {
        $enPath = resource_path('lang/en.json');
        $localePath = resource_path("lang/{$locale}.json");

        $enKeys = array_keys(json_decode(file_get_contents($enPath), true));
        $localeKeys = array_keys(json_decode(file_get_contents($localePath), true));

        $missing = array_diff($enKeys, $localeKeys);

        $this->assertEmpty(
            $missing,
            "Locale [{$locale}] is missing ".count($missing).' key(s): '.implode(', ', array_slice($missing, 0, 5))
        );
    }
}
