<?php

namespace App\Livewire\System;

use App\Models\Branch;
use App\Models\User;
use App\Models\Invoice;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
use Livewire\Component;

class BranchIndex extends Component
{
    public $branchId;
    public string $code = '';
    public string $name = '';
    public string $address = '';
    public string $phone = '';
    public string $manager = '';
    public string $color = 'blue';
    public bool $is_active = true;
    public int $sort_order = 0;

    public bool $showModal = false;

    public function mount(): void
    {
        abort_unless(auth()->user()?->role === 'admin', 403);
    }

    protected function rules(): array
    {
        return [
            'code' => ['required', 'alpha_dash', 'max:30', Rule::unique('branches', 'code')->ignore($this->branchId)],
            'name' => 'required|string|max:120',
            'address' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'manager' => 'nullable|string|max:120',
            'color' => 'required|in:' . implode(',', array_keys(Branch::COLORS)),
            'is_active' => 'boolean',
            'sort_order' => 'integer|min:0',
        ];
    }

    protected function resetForm(): void
    {
        $this->branchId = null;
        $this->code = '';
        $this->name = '';
        $this->address = '';
        $this->phone = '';
        $this->manager = '';
        $this->color = 'blue';
        $this->is_active = true;
        $this->sort_order = (int) (Branch::max('sort_order') ?? 0) + 1;
        $this->resetErrorBag();
    }

    public function create(): void
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function edit($id): void
    {
        $b = Branch::findOrFail($id);
        $this->branchId = $b->id;
        $this->code = $b->code;
        $this->name = $b->name;
        $this->address = (string) $b->address;
        $this->phone = (string) $b->phone;
        $this->manager = (string) $b->manager;
        $this->color = $b->color ?: 'slate';
        $this->is_active = (bool) $b->is_active;
        $this->sort_order = (int) $b->sort_order;
        $this->resetErrorBag();
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->code = Str::lower(trim($this->code));
        $this->validate();

        Branch::updateOrCreate(
            ['id' => $this->branchId],
            [
                'code' => $this->code,
                'name' => $this->name,
                'address' => $this->address ?: null,
                'phone' => $this->phone ?: null,
                'manager' => $this->manager ?: null,
                'color' => $this->color,
                'is_active' => $this->is_active,
                'sort_order' => $this->sort_order,
            ]
        );

        $this->showModal = false;
        $this->dispatch('notify', message: 'Đã lưu chi nhánh!', type: 'success');
    }

    public function toggleActive($id): void
    {
        $b = Branch::findOrFail($id);
        $b->is_active = !$b->is_active;
        $b->save();
        $this->dispatch('notify', message: 'Đã cập nhật trạng thái chi nhánh.', type: 'success');
    }

    public function delete($id): void
    {
        $b = Branch::findOrFail($id);

        // Chặn xóa nếu đang được tham chiếu (nhân viên / hóa đơn).
        $userCount = User::where('work_branch', $b->code)->count();
        $invoiceCount = Invoice::where('branch', $b->code)->count();
        if ($userCount > 0 || $invoiceCount > 0) {
            $this->dispatch('notify', message: "Không thể xóa: còn {$userCount} nhân viên và {$invoiceCount} hóa đơn thuộc chi nhánh này. Hãy tắt (ẩn) thay vì xóa.", type: 'error');
            return;
        }

        $b->delete();
        $this->dispatch('notify', message: 'Đã xóa chi nhánh.', type: 'success');
    }

    public function render()
    {
        $branches = Branch::withCount([])
            ->orderBy('sort_order')->orderBy('name')->get()
            ->map(function ($b) {
                $b->user_count = User::where('work_branch', $b->code)->count();
                $b->invoice_count = Invoice::where('branch', $b->code)->count();
                return $b;
            });

        return view('livewire.system.branch-index', [
            'branches' => $branches,
            'colors' => Branch::COLORS,
        ])->layout('layouts.app');
    }
}
