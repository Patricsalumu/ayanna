<?php

namespace Tests\Feature;

use App\Http\Controllers\StockJournalierController;
use Tests\TestCase;

class StockJournalierExportFilterTest extends TestCase
{
    public function test_it_filters_out_zero_sale_products_for_export_when_only_sold_is_enabled(): void
    {
        $produitsByCategory = collect([
            'Boissons' => collect([
                ['nom' => 'Coca', 'q_vendue' => 2, 'total' => 200],
                ['nom' => 'Eau', 'q_vendue' => 0, 'total' => 0],
            ]),
            'Snacks' => collect([
                ['nom' => 'Chips', 'q_vendue' => 0, 'total' => 0],
            ]),
        ]);

        $filtered = StockJournalierController::filterProduitsForExport($produitsByCategory, true);

        $this->assertArrayHasKey('Boissons', $filtered->toArray());
        $this->assertCount(1, $filtered['Boissons']);
        $this->assertSame('Coca', $filtered['Boissons']->first()['nom']);
        $this->assertArrayNotHasKey('Snacks', $filtered->toArray());
        $this->assertSame(200, $filtered->sum(fn ($items) => $items->sum('total')));
    }
}
