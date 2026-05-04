<?php

namespace App\Traits;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Events\BeforeImport;
use Maatwebsite\Excel\Events\AfterChunk;
use Maatwebsite\Excel\Events\AfterImport;
use Maatwebsite\Excel\Events\ImportFailed;

trait TracksImportProgress
{
    protected $importKey;

    public function setImportKey($key)
    {
        $this->importKey = $key;
        return $this;
    }

    public function recordError($message)
    {
        $progress = Cache::get("import_progress_{$this->importKey}");
        if ($progress) {
            $progress['errors'][] = $message;
            Cache::put("import_progress_{$this->importKey}", $progress, 3600);
        }
    }

    public function registerEvents(): array
    {
        return [
            BeforeImport::class => function (BeforeImport $event) {
                $reader = $event->getReader();
                $totalRows = $reader->getTotalRows();
                
                // Fallback: If getTotalRows returns empty or zero, try to get it from sheet delegates
                if (empty($totalRows) || array_sum($totalRows) === 0) {
                    try {
                        $spreadsheet = $reader->getDelegate();
                        foreach ($spreadsheet->getAllSheets() as $sheet) {
                            $totalRows[$sheet->getTitle()] = $sheet->getHighestRow();
                        }
                        Log::info("Fallback row count used for key {$this->importKey}: " . json_encode($totalRows));
                    } catch (\Exception $e) {
                        Log::warning("Failed to use fallback row count: " . $e->getMessage());
                    }
                }

                $total = 0;
                $offset = 1;
                if (method_exists($this, 'startRow')) {
                    $offset = $this->startRow() - 1;
                } elseif (method_exists($this, 'headingRow')) {
                    $offset = $this->headingRow();
                }

                foreach ($totalRows as $sheetName => $rowCount) {
                    $total += max(0, $rowCount - $offset);
                }

                Log::info("Import starting for key {$this->importKey}. Details: Offset={$offset}, RawRows=" . json_encode($totalRows) . ", CalculatedTotal={$total}");
                
                Cache::put("import_progress_{$this->importKey}", [
                    'total' => $total,
                    'current' => 0,
                    'status' => 'processing',
                    'errors' => [],
                ], 3600);
            },

            AfterChunk::class => function (AfterChunk $event) {
                $progress = Cache::get("import_progress_{$this->importKey}");
                if ($progress) {
                    $progress['current'] += $this->chunkSize(); 
                    if ($progress['current'] > $progress['total']) {
                        $progress['current'] = $progress['total'];
                    }
                    
                    Log::info("Import progress for key {$this->importKey}: {$progress['current']}/{$progress['total']}");
                    
                    if ($progress['current'] >= $progress['total']) {
                        $progress['status'] = 'finished';
                    }
                    
                    Cache::put("import_progress_{$this->importKey}", $progress, 3600);
                }
            },
            AfterImport::class => function (AfterImport $event) {
                $progress = Cache::get("import_progress_{$this->importKey}");
                if ($progress) {
                    $progress['status'] = 'finished';
                    $progress['current'] = $progress['total'];
                    Cache::put("import_progress_{$this->importKey}", $progress, 3600);
                }
            },
            ImportFailed::class => function (ImportFailed $event) {
                Log::error("Import failed for key {$this->importKey}: " . $event->getException()->getMessage());
                $progress = Cache::get("import_progress_{$this->importKey}");
                if ($progress) {
                    $progress['status'] = 'failed';
                    $progress['errors'][] = $event->getException()->getMessage();
                    Cache::put("import_progress_{$this->importKey}", $progress, 3600);
                }
            },
        ];
    }
}
