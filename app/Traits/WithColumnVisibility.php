<?php

namespace App\Traits;

trait WithColumnVisibility
{
    public $visibleColumns = [];

    public function initializeWithColumnVisibility()
    {
        if (empty($this->visibleColumns)) {
            $this->visibleColumns = $this->getDefaultVisibleColumns();
        }
    }

    public function toggleColumn($column)
    {
        if (in_array($column, $this->visibleColumns)) {
            $this->visibleColumns = array_diff($this->visibleColumns, [$column]);
        } else {
            $this->visibleColumns[] = $column;
        }
        
        $this->visibleColumns = array_values($this->visibleColumns);
    }

    abstract protected function getDefaultVisibleColumns(): array;
}
