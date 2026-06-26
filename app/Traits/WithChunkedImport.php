<?php

namespace App\Traits;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

/**
 * Import Excel ĐỒNG BỘ theo từng chunk (không dùng queue/worker).
 *
 * Luồng (kiểu Botble):
 *  - startImport(): đọc file 1 lần -> chia chunk -> cache từng chunk -> bật importing.
 *  - Frontend lặp gọi processImportChunk() cho tới khi importing = false.
 *    Mỗi lần xử lý 1 chunk -> request ngắn, UI cập nhật progress + log trực tiếp.
 *
 * Component dùng trait phải hiện thực:
 *  - readImportRows(string $absolutePath): array   // trả mảng các dòng dữ liệu
 *  - importOneRow(array $row, int $rowNumber): void // xử lý 1 dòng, throw nếu lỗi
 * Và có thể override: importChunkSize(), importFinishedId(), finalizeImport().
 */
trait WithChunkedImport
{
    public $importFile;
    public bool $importing = false;
    public ?string $importBatchId = null;
    public int $importTotal = 0;
    public int $importCurrent = 0;
    public int $importChunkIndex = 0;
    public int $importChunkCount = 0;
    public int $importProgress = 0;
    public array $importErrors = [];
    public array $importLog = [];

    protected function importChunkSize(): int
    {
        return 25;
    }

    /** id khớp với <x-import-modal id="..."> để đóng modal khi xong. */
    protected function importFinishedId(): string
    {
        return 'import';
    }

    /** Hook chạy sau khi import xong (vd: thông báo tổng kết). */
    protected function finalizeImport(): void
    {
    }

    abstract protected function readImportRows(string $absolutePath): array;

    abstract protected function importOneRow(array $row, int $rowNumber): void;

    /** Kiểm tra quyền trước khi import. Component override nếu cần. */
    protected function authorizeImport(): bool
    {
        return true;
    }

    public function startImport(): void
    {
        if (!$this->authorizeImport()) {
            return;
        }

        $this->validate(['importFile' => 'required']);
        @set_time_limit(120);

        $this->resetImportState();

        try {
            $rows = $this->readImportRows($this->importFile->getRealPath());
        } catch (\Throwable $e) {
            $this->importErrors[] = 'Lỗi đọc file: ' . $e->getMessage();
            $this->importLog[] = '✗ Không đọc được file.';
            return;
        }

        // Bỏ các dòng rỗng hoàn toàn.
        $rows = array_values(array_filter($rows, function ($r) {
            if (!is_array($r)) {
                return !empty($r);
            }
            return count(array_filter($r, fn ($v) => $v !== null && $v !== '')) > 0;
        }));

        $this->importTotal = count($rows);
        if ($this->importTotal === 0) {
            $this->importLog[] = 'File không có dòng dữ liệu nào.';
            $this->importFile = null;
            return;
        }

        $this->importBatchId = (string) Str::uuid();
        $chunks = array_chunk($rows, $this->importChunkSize());
        $this->importChunkCount = count($chunks);
        foreach ($chunks as $i => $chunk) {
            Cache::put($this->importCacheKey($i), $chunk, 3600);
        }

        $this->importLog[] = 'Đã đọc file: ' . $this->importTotal . ' dòng. Bắt đầu xử lý...';
        $this->importing = true;
        $this->importFile = null;
    }

    public function processImportChunk(): void
    {
        if (!$this->importing || !$this->importBatchId) {
            return;
        }

        $chunk = Cache::get($this->importCacheKey($this->importChunkIndex), []);
        Cache::forget($this->importCacheKey($this->importChunkIndex));

        $fail = 0;
        foreach ($chunk as $i => $row) {
            $rowNumber = $this->importCurrent + $i + 1;
            try {
                $this->importOneRow((array) $row, $rowNumber);
            } catch (\Throwable $e) {
                $fail++;
                $this->pushError('Dòng ' . $rowNumber . ': ' . $e->getMessage());
            }
        }

        $this->importCurrent = min($this->importTotal, $this->importCurrent + count($chunk));
        $this->importChunkIndex++;
        $this->importProgress = $this->importTotal > 0
            ? (int) min(100, round($this->importCurrent * 100 / $this->importTotal))
            : 100;

        $this->pushLog('Đã xử lý ' . $this->importCurrent . '/' . $this->importTotal . ' dòng'
            . ($fail > 0 ? ' — lỗi: ' . $fail : ''));

        if (count($chunk) === 0 || $this->importChunkIndex >= $this->importChunkCount || $this->importCurrent >= $this->importTotal) {
            $this->finishImport();
        }
    }

    protected function finishImport(): void
    {
        $this->importing = false;
        // Dọn cache còn sót (nếu có).
        for ($i = 0; $i < $this->importChunkCount; $i++) {
            Cache::forget($this->importCacheKey($i));
        }
        $okCount = max(0, $this->importCurrent - count($this->importErrors));
        $this->pushLog('✓ Hoàn tất. Thành công: ' . $okCount . ', lỗi: ' . count($this->importErrors));
        $this->finalizeImport();
        $this->dispatch('import-finished', id: $this->importFinishedId());
    }

    /** Reset UI import (gọi khi mở lại modal). */
    public function resetImport(): void
    {
        $this->importFile = null;
        $this->resetImportState();
    }

    protected function resetImportState(): void
    {
        $this->importing = false;
        $this->importBatchId = null;
        $this->importTotal = 0;
        $this->importCurrent = 0;
        $this->importChunkIndex = 0;
        $this->importChunkCount = 0;
        $this->importProgress = 0;
        $this->importErrors = [];
        $this->importLog = [];
        $this->resetErrorBag();
    }

    protected function pushLog(string $line): void
    {
        $this->importLog[] = $line;
        if (count($this->importLog) > 120) {
            $this->importLog = array_slice($this->importLog, -120);
        }
    }

    protected function pushError(string $line): void
    {
        if (count($this->importErrors) < 300) {
            $this->importErrors[] = $line;
        }
        $this->pushLog('⚠ ' . $line);
    }

    protected function importCacheKey(int $index): string
    {
        return 'import_rows_' . $this->importBatchId . '_' . $index;
    }
}
