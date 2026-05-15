<?php

namespace App\Traits;

trait WithColumnVisibility
{
    public $visibleColumns = [];

    public function initializeWithColumnVisibility()
    {
        if (auth()->check()) {
            $saved = auth()->user()->ui_settings[$this->getModuleKey()]['columns'] ?? null;
            if ($saved) {
                $this->visibleColumns = $saved;
            }
        }

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

        if (auth()->check()) {
            $user = auth()->user();
            $settings = $user->ui_settings ?? [];
            $settings[$this->getModuleKey()]['columns'] = $this->visibleColumns;
            $user->update(['ui_settings' => $settings]);
        }
    }

    abstract protected function getModuleKey(): string;
    abstract protected function getDefaultVisibleColumns(): array;
}
