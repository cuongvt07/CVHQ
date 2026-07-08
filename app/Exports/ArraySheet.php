<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

/** 1 sheet Excel đơn giản: tiêu đề + header + dữ liệu. */
class ArraySheet implements FromArray, WithHeadings, WithTitle
{
    public function __construct(
        protected string $title,
        protected array $headings,
        protected array $rows,
    ) {
    }

    public function array(): array
    {
        return $this->rows;
    }

    public function headings(): array
    {
        return $this->headings;
    }

    public function title(): string
    {
        // Tên sheet tối đa 31 ký tự, bỏ ký tự cấm.
        return mb_substr(str_replace(['\\', '/', '?', '*', '[', ']', ':'], ' ', $this->title), 0, 31);
    }
}
