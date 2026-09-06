<?php

namespace Tests\Unit;

use App\Models\Entreprise;
use Tests\TestCase;

class EntrepriseCurrencyConversionTest extends TestCase
{
    public function test_it_converts_amounts_to_the_other_currency_based_on_company_currency(): void
    {
        $dollar = new Entreprise([
            'devise' => '$',
            'taux' => 600,
        ]);

        $franc = new Entreprise([
            'devise' => 'F',
            'taux' => 600,
        ]);

        $this->assertSame(6000.0, $dollar->convertToOtherCurrency(10.0));
        $this->assertEqualsWithDelta(0.0166666667, $franc->convertToOtherCurrency(10.0), 0.0000001);
    }
}
