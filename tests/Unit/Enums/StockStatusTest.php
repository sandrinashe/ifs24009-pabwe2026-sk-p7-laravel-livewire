<?php

namespace Tests\Unit\Enums;

use App\Features\Stock\Enums\StockStatus;
use PHPUnit\Framework\TestCase;

class StockStatusTest extends TestCase
{
    public function test_stock_status_labels(): void
    {
        $this->assertEquals('Tersedia', StockStatus::AVAILABLE->label());
        $this->assertEquals('Stok Menipis', StockStatus::LOW_STOCK->label());
        $this->assertEquals('Stok Habis', StockStatus::OUT_OF_STOCK->label());
    }

    public function test_stock_status_badge_classes(): void
    {
        $this->assertStringContainsString('sage', StockStatus::AVAILABLE->badgeClass());
        $this->assertStringContainsString('amber', StockStatus::LOW_STOCK->badgeClass());
        $this->assertStringContainsString('rose', StockStatus::OUT_OF_STOCK->badgeClass());
    }

    public function test_stock_status_bar_classes(): void
    {
        $this->assertSame('bg-sage-500', StockStatus::AVAILABLE->barClass());
        $this->assertSame('bg-amber-400', StockStatus::LOW_STOCK->barClass());
        $this->assertSame('bg-rose-400', StockStatus::OUT_OF_STOCK->barClass());
    }

    public function test_from_quantity_resolves_status(): void
    {
        $this->assertSame(StockStatus::OUT_OF_STOCK, StockStatus::fromQuantity(0, 5));
        $this->assertSame(StockStatus::LOW_STOCK, StockStatus::fromQuantity(1, 5));
        $this->assertSame(StockStatus::LOW_STOCK, StockStatus::fromQuantity(5, 5));
        $this->assertSame(StockStatus::AVAILABLE, StockStatus::fromQuantity(6, 5));
    }
}
