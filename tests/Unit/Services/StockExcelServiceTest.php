<?php

namespace Tests\Unit\Services;

use App\Features\Stock\Models\Stock;
use App\Features\Stock\Repositories\StockRepository;
use App\Features\Stock\Services\StockExcelService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Tests\TestCase;

class StockExcelServiceTest extends TestCase
{
    use RefreshDatabase;

    protected StockExcelService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new StockExcelService(new StockRepository());
    }

    public function test_build_spreadsheet_and_export_response(): void
    {
        $user = User::factory()->create();
        $stocks = Stock::factory()->count(2)->create(['user_id' => $user->id]);
        $stocks->push(Stock::factory()->create(['user_id' => $user->id, 'sku' => null, 'description' => null, 'price' => null]));

        $spreadsheet = $this->service->buildSpreadsheet($stocks, 'Custom Stocks');
        $this->assertInstanceOf(Spreadsheet::class, $spreadsheet);
        $this->assertEquals('Custom Stocks', $spreadsheet->getActiveSheet()->getTitle());

        $response = $this->service->exportToResponse($stocks, 'test.xlsx');
        $this->assertInstanceOf(StreamedResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());

        ob_start();
        $response->sendContent();
        $outputContent = ob_get_clean();
        $this->assertNotEmpty($outputContent);
    }

    public function test_import_spreadsheet(): void
    {
        $user = User::factory()->create();

        // Create an in-memory spreadsheet and save to a temporary file
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Headers
        foreach (['No', 'Name', 'SKU', 'Description', 'Quantity', 'Unit', 'Price', 'Min Stock'] as $i => $header) {
            $sheet->setCellValue(chr(65 + $i).'1', $header);
        }

        // Row 2: complete row, quantity above minimum -> available
        foreach (['1', 'Imported Stock 1', 'SKU-1', 'Desc 1', 50, 'box', 12500.5, 10] as $i => $value) {
            $sheet->setCellValue(chr(65 + $i).'2', $value);
        }

        // Row 3: low stock, '-' placeholders for sku and description
        foreach (['2', 'Imported Stock 2', '-', '-', 3, 'pcs', 1000, 5] as $i => $value) {
            $sheet->setCellValue(chr(65 + $i).'3', $value);
        }

        // Row 4: only a name -> defaults (qty 0, pcs, no price, min 5) -> out_of_stock
        $sheet->setCellValue('A4', '3');
        $sheet->setCellValue('B4', 'Imported Stock 3');

        // Row 5: empty name (skipped with error message)
        $sheet->setCellValue('A5', '4');
        $sheet->setCellValue('B5', '');
        $sheet->setCellValue('C5', 'SKIP-ME');

        $tempPath = tempnam(sys_get_temp_dir(), 'test_excel_').'.xlsx';
        $writer = new Xlsx($spreadsheet);
        $writer->save($tempPath);

        $result = $this->service->import($tempPath, $user->id);
        @unlink($tempPath);

        $this->assertEquals(3, $result['imported']);
        $this->assertCount(1, $result['errors']);
        $this->assertStringContainsString('Baris 5', $result['errors'][5]);

        $this->assertDatabaseHas('stocks', [
            'user_id' => $user->id,
            'name' => 'Imported Stock 1',
            'sku' => 'SKU-1',
            'quantity' => 50,
            'unit' => 'box',
            'min_stock' => 10,
            'status' => 'available',
        ]);
        $this->assertDatabaseHas('stocks', [
            'user_id' => $user->id,
            'name' => 'Imported Stock 2',
            'sku' => null,
            'description' => null,
            'quantity' => 3,
            'status' => 'low_stock',
        ]);
        $this->assertDatabaseHas('stocks', [
            'user_id' => $user->id,
            'name' => 'Imported Stock 3',
            'quantity' => 0,
            'unit' => 'pcs',
            'price' => null,
            'min_stock' => 5,
            'status' => 'out_of_stock',
        ]);
        $this->assertDatabaseMissing('stocks', ['sku' => 'SKIP-ME']);
    }

    public function test_import_with_no_valid_rows_inserts_nothing(): void
    {
        $user = User::factory()->create();

        $spreadsheet = new Spreadsheet();
        $spreadsheet->getActiveSheet()->setCellValue('A1', 'No');

        $tempPath = tempnam(sys_get_temp_dir(), 'test_excel_').'.xlsx';
        (new Xlsx($spreadsheet))->save($tempPath);

        $result = $this->service->import($tempPath, $user->id);
        @unlink($tempPath);

        $this->assertEquals(0, $result['imported']);
        $this->assertDatabaseCount('stocks', 0);
    }
}
