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

    public function test_it_resolves_two_pdf_export_formats_a4_and_80mm(): void
    {
        $a4 = StockJournalierController::resolvePdfExportConfig('a4');
        $this->assertSame('stock_journalier.pdf', $a4['view']);
        $this->assertSame('a4', $a4['paper']);

        $mm80 = StockJournalierController::resolvePdfExportConfig('80mm');
        $this->assertSame('stock_journalier.pdf_80mm', $mm80['view']);
        $this->assertSame([0, 0, 226.77, 1000], $mm80['paper']);
    }

    public function test_it_normalizes_selected_categories_for_export_requests(): void
    {
        $this->assertSame([1, 2], StockJournalierController::normalizeSelectedCategoryIds(['1', '2']));
        $this->assertSame([], StockJournalierController::normalizeSelectedCategoryIds(['__NONE__']));
        $this->assertSame(null, StockJournalierController::normalizeSelectedCategoryIds(null));
    }
}
