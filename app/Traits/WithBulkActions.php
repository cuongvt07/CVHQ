<?php

namespace App\Traits;

trait WithBulkActions
{
    public $selectedRows = [];
    public $selectAll = false;

    public function updatedSelectAll($value)
    {
        if ($value) {
            $this->selectedRows = $this->getRecordsForBulk()->pluck('id')->map(fn($id) => (string) $id)->toArray();
        } else {
            $this->selectedRows = [];
        }
    }

    public function updatedSelectedRows()
    {
        $this->selectAll = false;
    }

    public function bulkDelete()
    {
        if (empty($this->selectedRows)) return;

        $this->getModelForBulk()::whereIn('id', $this->selectedRows)->delete();
        
        $this->selectedRows = [];
        $this->selectAll = false;
        
        $this->dispatch('notify', message: 'Đã xóa các mục đã chọn!', type: 'success');
    }

    abstract protected function getRecordsForBulk();
    abstract protected function getModelForBulk();
}
