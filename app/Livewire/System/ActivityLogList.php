<?php

namespace App\Livewire\System;

use App\Models\ActivityLog;
use App\Models\User;
use Livewire\Component;
use App\Traits\WithColumnVisibility;
use App\Traits\WithUserPreferences;

class ActivityLogList extends Component
{
    use WithPagination, WithColumnVisibility, WithUserPreferences;

    protected function getModuleKey(): string
    {
        return 'activity_logs';
    }

    public $search = '';
    public $user_id = '';
    public $action = '';
    public $date_from = '';
    public $date_to = '';
    public $perPage = 25;
    public $visibleColumns = [];

    protected $queryString = [
        'user_id' => ['except' => ''],
        'action' => ['except' => ''],
        'date_from' => ['except' => ''],
        'date_to' => ['except' => ''],
        'perPage' => ['except' => 25],
    ];

    public function mount()
    {
        if (auth()->user()->role !== 'admin') {
            abort(403, 'Bạn không có quyền truy cập trang này.');
        }
    }

    public function updated($propertyName)
    {
        if (in_array($propertyName, ['search', 'user_id', 'action', 'date_from', 'date_to'])) {
            $this->resetPage();
        }
    }

    public function clearFilters()
    {
        $this->reset(['search', 'user_id', 'action', 'date_from', 'date_to']);
        $this->resetPage();
    }

    public function render()
    {
        $logs = ActivityLog::with('user')
            ->when($this->user_id, function($query) {
                $query->where('user_id', $this->user_id);
            })
            ->when($this->action, function($query) {
                $query->where('action', $this->action);
            })
            ->when($this->date_from, function($query) {
                $query->whereDate('created_at', '>=', $this->date_from);
            })
            ->when($this->date_to, function($query) {
                $query->whereDate('created_at', '<=', $this->date_to);
            })
            ->when($this->search, function($query) {
                $query->whereHas('user', function($q) {
                    $q->where('name', 'like', '%' . $this->search . '%');
                })->orWhere('model_type', 'like', '%' . $this->search . '%')
                  ->orWhere('model_id', 'like', '%' . $this->search . '%');
            })
            ->orderBy('created_at', 'desc')
            ->paginate($this->perPage);

        return view('livewire.system.activity-log-list', [
            'logs' => $logs,
            'users' => User::orderBy('name')->get(),
            'actions' => ['created', 'updated', 'deleted']
        ])->layout('layouts.app');
    }
}
