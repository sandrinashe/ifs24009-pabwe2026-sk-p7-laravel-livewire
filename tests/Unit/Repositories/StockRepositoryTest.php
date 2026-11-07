<?php

namespace Tests\Unit\Repositories;

use App\Features\Stock\Models\Stock;
use App\Features\Stock\Repositories\StockRepository;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StockRepositoryTest extends TestCase
{
    use RefreshDatabase;

    protected StockRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new StockRepository();
    }

    public function test_create_and_find(): void
    {
        $user = User::factory()->create();
        $stock = $this->repository->create([
            'user_id' => $user->id,
            'name' => 'Test Stock',
            'description' => 'Test Desc',
            'status' => 'available',
        ]);

        $this->assertInstanceOf(Stock::class, $stock);
        $this->assertDatabaseHas('stocks', ['id' => $stock->id, 'name' => 'Test Stock']);

        $found = $this->repository->find($stock->id);
        $this->assertNotNull($found);
        $this->assertEquals($stock->id, $found->id);

        $foundOrFail = $this->repository->findOrFail($stock->id);
        $this->assertEquals($stock->id, $foundOrFail->id);
    }

    public function test_find_or_fail_throws_exception_when_not_found(): void
    {
        $this->expectException(ModelNotFoundException::class);
        $this->repository->findOrFail(9999);
    }

    public function test_update_and_delete(): void
    {
        $user = User::factory()->create();
        $stock = $this->repository->create([
            'user_id' => $user->id,
            'name' => 'Original',
        ]);

        $updated = $this->repository->update($stock, ['name' => 'Updated Title']);
        $this->assertTrue($updated);
        $this->assertDatabaseHas('stocks', ['id' => $stock->id, 'name' => 'Updated Title']);

        $deleted = $this->repository->delete($stock);
        $this->assertTrue($deleted);
        $this->assertDatabaseMissing('stocks', ['id' => $stock->id]);
    }

    public function test_all_for_user(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        Stock::factory()->count(3)->create(['user_id' => $user->id]);
        Stock::factory()->count(2)->create(['user_id' => $otherUser->id]);

        $stocks = $this->repository->allForUser($user->id);
        $this->assertCount(3, $stocks);
    }

    public function test_for_user_with_filters(): void
    {
        $user = User::factory()->create();

        Stock::factory()->create(['user_id' => $user->id, 'name' => 'Belajar Laravel', 'status' => 'available']);
        Stock::factory()->create(['user_id' => $user->id, 'name' => 'Belajar Vue', 'status' => 'out_of_stock']);
        Stock::factory()->create(['user_id' => $user->id, 'name' => 'Tidur Nyenyak', 'sku' => 'SKU-REST', 'description' => 'Laravel rest', 'status' => 'out_of_stock']);

        // Test without filter
        $all = $this->repository->forUser($user->id);
        $this->assertCount(3, $all);

        // Test status filter
        $available = $this->repository->forUser($user->id, status: 'available');
        $this->assertCount(1, $available);

        // Test search filter on name
        $searchTitle = $this->repository->forUser($user->id, search: 'Laravel');
        $this->assertCount(2, $searchTitle);

        // Test search filter on description
        $searchDesc = $this->repository->forUser($user->id, search: 'rest');
        $this->assertCount(1, $searchDesc);

        // Test search filter on SKU
        $searchSku = $this->repository->forUser($user->id, search: 'SKU-REST');
        $this->assertCount(1, $searchSku);
    }

    public function test_insert_many(): void
    {
        $user = User::factory()->create();

        $emptyResult = $this->repository->insertMany([]);
        $this->assertTrue($emptyResult);

        $records = [
            ['user_id' => $user->id, 'name' => 'Batch 1', 'status' => 'available', 'quantity' => 10, 'unit' => 'pcs', 'min_stock' => 5, 'created_at' => now(), 'updated_at' => now()],
            ['user_id' => $user->id, 'name' => 'Batch 2', 'status' => 'available', 'quantity' => 10, 'unit' => 'pcs', 'min_stock' => 5, 'created_at' => now(), 'updated_at' => now()],
        ];

        $result = $this->repository->insertMany($records);
        $this->assertTrue($result);
        $this->assertDatabaseCount('stocks', 2);
    }

    public function test_summary_for_user(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();

        Stock::factory()->create(['user_id' => $user->id, 'quantity' => 10, 'price' => 1000, 'status' => 'available']);
        Stock::factory()->create(['user_id' => $user->id, 'quantity' => 2, 'price' => 500, 'status' => 'low_stock']);
        Stock::factory()->create(['user_id' => $user->id, 'quantity' => 0, 'price' => null, 'status' => 'out_of_stock']);
        Stock::factory()->create(['user_id' => $other->id, 'quantity' => 99, 'price' => 1, 'status' => 'available']);

        $summary = $this->repository->summaryForUser($user->id);

        $this->assertSame(3, $summary['items']);
        $this->assertSame(12, $summary['units']);
        $this->assertSame(1, $summary['low']);
        $this->assertSame(1, $summary['out']);
        $this->assertEquals(11000.0, $summary['value']);
    }

    public function test_summary_for_user_without_stock_is_all_zero(): void
    {
        $user = User::factory()->create();

        $this->assertSame(
            ['items' => 0, 'units' => 0, 'low' => 0, 'out' => 0, 'value' => 0.0],
            $this->repository->summaryForUser($user->id)
        );
    }
}
