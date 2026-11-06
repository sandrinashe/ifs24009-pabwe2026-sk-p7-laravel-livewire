<?php

namespace Tests\Unit\DTO;

use App\Features\Ai\DTO\GeneratedStockItem;
use PHPUnit\Framework\TestCase;

class GeneratedStockItemTest extends TestCase
{
    public function test_dto_properties_and_to_array(): void
    {
        $dto = new GeneratedStockItem(
            name: 'Kertas HVS A4',
            sku: 'ATK-001',
            description: 'Kertas 80gsm',
            quantity: 20,
            unit: 'rim',
            price: 52000.0,
            minStock: 4,
        );

        $this->assertEquals('Kertas HVS A4', $dto->name);
        $this->assertEquals('ATK-001', $dto->sku);
        $this->assertEquals('Kertas 80gsm', $dto->description);
        $this->assertEquals(20, $dto->quantity);
        $this->assertEquals('rim', $dto->unit);
        $this->assertEquals(52000.0, $dto->price);
        $this->assertEquals(4, $dto->minStock);

        $this->assertEquals([
            'name' => 'Kertas HVS A4',
            'sku' => 'ATK-001',
            'description' => 'Kertas 80gsm',
            'quantity' => 20,
            'unit' => 'rim',
            'price' => 52000.0,
            'min_stock' => 4,
        ], $dto->toArray());
    }

    public function test_dto_defaults(): void
    {
        $dto = new GeneratedStockItem(name: 'Barang');

        $this->assertNull($dto->sku);
        $this->assertNull($dto->description);
        $this->assertSame(0, $dto->quantity);
        $this->assertSame('pcs', $dto->unit);
        $this->assertNull($dto->price);
        $this->assertSame(5, $dto->minStock);
    }
}
