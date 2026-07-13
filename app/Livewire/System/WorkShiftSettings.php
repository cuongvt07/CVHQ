<?php

namespace App\Livewire\System;

use App\Models\WorkShift;
use Livewire\Component;

class WorkShiftSettings extends Component
{
    public ?int $editId = null;
    public string $name = '';
    public string $start_time = '';
    public string $end_time = '';

    public function mount(): void
    {
        if (auth()->user()->role !== 'admin') {
            abort(403, 'Bạn không có quyền truy cập trang này.');
        }
    }

    protected function rules(): array
    {
        return [
            'name'       => 'required|string|max:100',
            'start_time' => 'required|date_format:H:i',
            'end_time'   => 'required|date_format:H:i',
        ];
    }

    public function edit(int $id): void
    {
        $s = WorkShift::find($id);
        if (!$s) return;
        $this->editId = $s->id;
        $this->name = $s->name;
        $this->start_time = substr((string) $s->start_time, 0, 5);
        $this->end_time = substr((string) $s->end_time, 0, 5);
    }

    public function resetForm(): void
    {
        $this->reset(['editId', 'name', 'start_time', 'end_time']);
        $this->resetErrorBag();
    }

    public function save(): void
    {
        $data = $this->validate();
        WorkShift::updateOrCreate(
            ['id' => $this->editId],
            [
                'name'       => $data['name'],
                'start_time' => $data['start_time'],
                'end_time'   => $data['end_time'],
                'sort_order' => $this->editId ? WorkShift::find($this->editId)?->sort_order ?? 0 : (WorkShift::max('sort_order') + 1),
            ]
        );
        $this->resetForm();
        $this->dispatch('notify', message: 'Đã lưu ca làm việc.', type: 'success');
    }

    public function toggleActive(int $id): void
    {
        $s = WorkShift::find($id);
        if ($s) {
            $s->update(['is_active' => !$s->is_active]);
        }
    }

    public function delete(int $id): void
    {
        WorkShift::where('id', $id)->delete();
        if ($this->editId === $id) {
            $this->resetForm();
        }
        $this->dispatch('notify', message: 'Đã xóa ca làm việc.', type: 'success');
    }

    public function render()
    {
        return view('livewire.system.work-shift-settings', [
            'shifts' => WorkShift::orderBy('sort_order')->orderBy('start_time')->get(),
        ])->layout('layouts.app');
    }
}
