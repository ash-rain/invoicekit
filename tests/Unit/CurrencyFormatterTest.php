<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class CurrencyFormatterTest extends TestCase
{
    public function test_format_currency_eur(): void
    {
        $this->assertSame('€1,234.56', formatCurrency('EUR', 1234.56));
    }

    public function test_format_currency_usd(): void
    {
        $this->assertSame('$1,234.56', formatCurrency('USD', 1234.56));
    }

    public function test_format_currency_bgn(): void
    {
        $this->assertSame('1,234.56 лв.', formatCurrency('BGN', 1234.56));
    }

    public function test_format_currency_ron(): void
    {
        $this->assertSame('1,234.56 RON', formatCurrency('RON', 1234.56));
    }

    public function test_format_currency_pln(): void
    {
        $this->assertSame('1,234.56 zł', formatCurrency('PLN', 1234.56));
    }

    public function test_format_currency_czk(): void
    {
        $this->assertSame('1,234.56 Kč', formatCurrency('CZK', 1234.56));
    }

    public function test_format_currency_huf(): void
    {
        $this->assertSame('1,234.56 Ft', formatCurrency('HUF', 1234.56));
    }

    public function test_format_currency_unknown_falls_back(): void
    {
        $this->assertSame('XYZ 50.00', formatCurrency('XYZ', 50.00));
    }

    public function test_format_currency_negative_amount(): void
    {
        $this->assertSame('-€50.00', formatCurrency('EUR', -50.00));
    }

    public function test_format_currency_zero(): void
    {
        $this->assertSame('€0.00', formatCurrency('EUR', 0.0));
    }
}
