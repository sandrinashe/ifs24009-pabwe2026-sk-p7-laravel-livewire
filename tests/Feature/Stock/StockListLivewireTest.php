<?php

namespace Tests\Feature\Stock;

use App\Features\Ai\Contracts\AiClientInterface;
use App\Features\Ai\DTO\GeneratedStockItem;
use App\Features\Stock\Livewire\StockList;
use App\Features\Stock\Models\Stock;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Livewire\Livewire;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Tests\TestCase;

class StockListLivewireTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_stocks(): void
    {
        $this->get('/stocks')->assertRedirect(route('login'));
    }

    public function test_member_can_view_own_stocks(): void
    {
        $user = User::factory()->member()->create();
        Stock::factory()->create(['user_id' => $user->id, 'name' => 'Barang Spesifik Member']);

        $otherUser = User::factory()->member()->create();
        Stock::factory()->create(['user_id' => $otherUser->id, 'name' => 'Barang Rahasia Orang Lain']);

        $this->actingAs($user);

        Livewire::test(StockList::class)
            ->assertSee('Barang Spesifik Member')
            ->assertDontSee('Barang Rahasia Orang Lain');
    }

    public function test_create_stock_validation_and_success(): void
    {
        $user = User::factory()->member()->create();
        $this->actingAs($user);

        // Validation error
        Livewire::test(StockList::class)
            ->call('openCreateModal')
            ->set('formName', '')
            ->set('formQuantity', -1)
            ->set('formMinStock', 'abc')
            ->call('saveStock')
            ->assertHasErrors(['formName', 'formQuantity', 'formMinStock']);

        // Success (status is derived automatically: 3 <= min_stock 5 -> low_stock)
        Livewire::test(StockList::class)
            ->call('openCreateModal')
            ->set('formName', 'Stok Baru')
            ->set('formSku', 'SKU-NEW')
            ->set('formDescription', 'Deskripsi **stok**')
            ->set('formQuantity', 3)
            ->set('formUnit', 'box')
            ->set('formPrice', 15000)
            ->set('formMinStock', 5)
            ->call('saveStock')
            ->assertHasNoErrors()
            ->assertSet('showFormModal', false);

        $this->assertDatabaseHas('stocks', [
            'user_id' => $user->id,
            'name' => 'Stok Baru',
            'sku' => 'SKU-NEW',
            'description' => 'Deskripsi **stok**',
            'quantity' => 3,
            'unit' => 'box',
            'min_stock' => 5,
            'status' => 'low_stock',
        ]);

        // Empty price string is stored as null
        Livewire::test(StockList::class)
            ->call('openCreateModal')
            ->set('formName', 'Tanpa Harga')
            ->set('formPrice', '')
            ->call('saveStock')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('stocks', ['name' => 'Tanpa Harga', 'price' => null, 'status' => 'out_of_stock']);
    }

    public function test_edit_stock_success(): void
    {
        $user = User::factory()->member()->create();
        $this->actingAs($user);

        $stock = Stock::factory()->create(['user_id' => $user->id, 'name' => 'Nama Lama', 'price' => 2500]);

        Livewire::test(StockList::class)
            ->call('openEditModal', $stock->id)
            ->assertSet('editingStockId', $stock->id)
            ->assertSet('formName', 'Nama Lama')
            ->assertSet('formQuantity', 50)
            ->set('formName', 'Nama Telah Diedit')
            ->set('formQuantity', 0)
            ->call('saveStock')
            ->assertHasNoErrors()
            ->assertSet('showFormModal', false);

        $this->assertDatabaseHas('stocks', [
            'id' => $stock->id,
            'name' => 'Nama Telah Diedit',
            'quantity' => 0,
            'status' => 'out_of_stock',
        ]);
    }

    public function test_edit_stock_without_price(): void
    {
        $user = User::factory()->member()->create();
        $this->actingAs($user);

        $stock = Stock::factory()->create(['user_id' => $user->id, 'price' => null]);

        Livewire::test(StockList::class)
            ->call('openEditModal', $stock->id)
            ->assertSet('formPrice', null);
    }

    public function test_adjust_quantity(): void
    {
        $user = User::factory()->member()->create();
        $this->actingAs($user);

        $stock = Stock::factory()->create(['user_id' => $user->id, 'quantity' => 6, 'min_stock' => 5, 'status' => 'available']);

        Livewire::test(StockList::class)
            ->call('adjustQuantity', $stock->id, -1);

        $stock->refresh();
        $this->assertEquals(5, $stock->quantity);
        $this->assertEquals('low_stock', $stock->status);

        Livewire::test(StockList::class)
            ->call('adjustQuantity', $stock->id, -5);

        $stock->refresh();
        $this->assertEquals(0, $stock->quantity);
        $this->assertEquals('out_of_stock', $stock->status);
    }

    public function test_member_cannot_adjust_or_edit_other_members_stock(): void
    {
        $user = User::factory()->member()->create();
        $other = User::factory()->member()->create();
        $stock = Stock::factory()->create(['user_id' => $other->id, 'quantity' => 10]);

        $this->actingAs($user);

        Livewire::test(StockList::class)
            ->call('adjustQuantity', $stock->id, 1)
            ->assertForbidden();

        Livewire::test(StockList::class)
            ->call('openEditModal', $stock->id)
            ->assertForbidden();

        $this->assertEquals(10, $stock->fresh()->quantity);
    }

    public function test_delete_stock(): void
    {
        $user = User::factory()->member()->create();
        $this->actingAs($user);

        $stock = Stock::factory()->create(['user_id' => $user->id]);

        Livewire::test(StockList::class)
            ->call('deleteStock', $stock->id);

        $this->assertDatabaseMissing('stocks', ['id' => $stock->id]);
    }

    public function test_confirm_and_cancel_delete_stock(): void
    {
        $user = User::factory()->member()->create();
        $this->actingAs($user);

        $stock = Stock::factory()->create(['user_id' => $user->id]);

        Livewire::test(StockList::class)
            ->assertSet('confirmingStockDeletionId', null)
            ->call('confirmDelete', $stock->id)
            ->assertSet('confirmingStockDeletionId', $stock->id)
            ->call('cancelDelete')
            ->assertSet('confirmingStockDeletionId', null)
            ->call('confirmDelete', $stock->id)
            ->call('deleteStock')
            ->assertSet('confirmingStockDeletionId', null);

        $this->assertDatabaseMissing('stocks', ['id' => $stock->id]);
    }

    public function test_search_and_status_filter(): void
    {
        $user = User::factory()->member()->create();
        $this->actingAs($user);

        Stock::factory()->create(['user_id' => $user->id, 'name' => 'Beras Premium', 'sku' => 'BRS-01', 'quantity' => 0, 'status' => 'out_of_stock']);
        Stock::factory()->create(['user_id' => $user->id, 'name' => 'Gula Pasir', 'status' => 'available']);

        Livewire::test(StockList::class)
            ->set('search', 'Beras')
            ->assertSee('Beras Premium')
            ->assertDontSee('Gula Pasir')
            ->set('search', '')
            ->set('statusFilter', 'available')
            ->assertSee('Gula Pasir')
            ->assertDontSee('Beras Premium');
    }

    public function test_export_excel(): void
    {
        $user = User::factory()->member()->create();
        $this->actingAs($user);

        Stock::factory()->create(['user_id' => $user->id, 'name' => 'Exportable Stock']);

        Livewire::test(StockList::class)
            ->call('exportExcel')
            ->assertFileDownloaded();
    }

    public function test_import_excel(): void
    {
        $user = User::factory()->member()->create();
        $this->actingAs($user);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        foreach (['No', 'Name', 'SKU', 'Description', 'Quantity', 'Unit', 'Price', 'Min Stock'] as $i => $header) {
            $sheet->setCellValue(chr(65 + $i).'1', $header);
        }
        foreach (['1', 'Imported Excel Stock', 'XLS-1', 'Excel Desc', 4, 'pcs', 7500, 5] as $i => $value) {
            $sheet->setCellValue(chr(65 + $i).'2', $value);
        }

        $tempPath = tempnam(sys_get_temp_dir(), 'test_upload_').'.xlsx';
        $writer = new Xlsx($spreadsheet);
        $writer->save($tempPath);

        $fileContent = file_get_contents($tempPath);
        @unlink($tempPath);

        $uploadedFile = UploadedFile::fake()->createWithContent('stocks.xlsx', $fileContent);

        Livewire::test(StockList::class)
            ->call('openImportModal')
            ->assertSet('showImportModal', true)
            ->set('importFile', $uploadedFile)
            ->call('importExcel')
            ->assertSet('showImportModal', false);

        $this->assertDatabaseHas('stocks', [
            'user_id' => $user->id,
            'name' => 'Imported Excel Stock',
            'sku' => 'XLS-1',
            'description' => 'Excel Desc',
            'status' => 'low_stock',
        ]);
    }

    public function test_ai_generation_and_saving(): void
    {
        $user = User::factory()->member()->create();
        $this->actingAs($user);

        // Mock AiClientInterface
        $mockAiClient = $this->createStub(AiClientInterface::class);
        $mockAiClient->method('generateStocks')->willReturn([
            new GeneratedStockItem(name: 'AI Item 1', sku: 'AI-1', description: 'Desc 1', quantity: 10, unit: 'box', price: 2000.0, minStock: 3),
            new GeneratedStockItem(name: 'AI Item 2'),
        ]);
        $this->app->instance(AiClientInterface::class, $mockAiClient);

        // Test validation for prompt and count
        Livewire::test(StockList::class)
            ->call('openAiModal')
            ->set('aiPrompt', '')
            ->set('aiCount', 0)
            ->call('generateWithAi')
            ->assertHasErrors(['aiPrompt', 'aiCount']);

        $component = Livewire::test(StockList::class)
            ->call('openAiModal')
            ->assertSet('showAiModal', true)
            ->set('aiPrompt', 'Buatkan daftar barang ATK')
            ->set('aiCount', 2)
            ->call('generateWithAi')
            ->assertCount('aiGeneratedItems', 2)
            ->assertCount('aiSelectedIndices', 2);

        // Try saving with no items selected
        $component->set('aiSelectedIndices', [])
            ->call('saveAiGenerated')
            ->assertSet('aiErrorMessage', 'Pilih minimal satu item stok yang ingin disimpan.');

        // Save selected items
        $component->set('aiSelectedIndices', [0, 1])
            ->call('saveAiGenerated')
            ->assertSet('showAiModal', false);

        $this->assertDatabaseHas('stocks', ['user_id' => $user->id, 'name' => 'AI Item 1', 'sku' => 'AI-1', 'quantity' => 10, 'unit' => 'box', 'status' => 'available']);
        $this->assertDatabaseHas('stocks', ['user_id' => $user->id, 'name' => 'AI Item 2', 'quantity' => 0, 'status' => 'out_of_stock']);
    }

    public function test_ai_generation_handles_exception(): void
    {
        $user = User::factory()->member()->create();
        $this->actingAs($user);

        $mockAiClient = $this->createStub(AiClientInterface::class);
        $mockAiClient->method('generateStocks')->willThrowException(new \RuntimeException('API quota exceeded'));
        $this->app->instance(AiClientInterface::class, $mockAiClient);

        Livewire::test(StockList::class)
            ->call('openAiModal')
            ->set('aiPrompt', 'Error test')
            ->call('generateWithAi')
            ->assertSet('aiErrorMessage', 'API quota exceeded');
    }

    public function test_close_modals(): void
    {
        $user = User::factory()->member()->create();
        $this->actingAs($user);

        Livewire::test(StockList::class)
            ->call('openCreateModal')
            ->call('closeFormModal')
            ->assertSet('showFormModal', false)
            ->call('openImportModal')
            ->call('closeImportModal')
            ->assertSet('showImportModal', false)
            ->call('openAiModal')
            ->call('closeAiModal')
            ->assertSet('showAiModal', false);
    }
}
