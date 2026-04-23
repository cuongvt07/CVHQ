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
                $totalRows = $event->getReader()->getTotalRows();
                
                $total = 0;
                foreach ($totalRows as $sheetName => $rowCount) {
                    // Subtract 1 for heading row, but ensure it's not negative
                    $total += max(0, $rowCount - 1);
                }

                Log::info("Import starting for key {$this->importKey}. Total rows: {$total}");
                
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
                    // We increment by the actual row count processed in this chunk if possible, 
                    // but chunkSize() is a good enough estimate for the UI.
                    $progress['current'] += $this->chunkSize(); 
                    if ($progress['current'] > $progress['total']) {
                        $progress['current'] = $progress['total'];
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
