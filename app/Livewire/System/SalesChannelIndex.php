<?php

namespace App\Livewire\System;

use App\Models\SalesChannel;
use Illuminate\Support\Str;
use Livewire\Component;
use App\Traits\HasPermissions;

class SalesChannelIndex extends Component
{
    use HasPermissions;

    protected function getModuleKey(): string
    {
        return 'sales_channels';
    }

    public $search = '';

    public $showModal       = false;
    public $editingId       = null;
    public $name            = '';
    public $color           = '#0088CC';
    public $icon            = '';
    public $is_active       = true;
    public $sort_order      = 0;
    public $confirmDeleteId = null;

    protected function rules(): array
    {
        return [
            'name'       => 'required|min:1|max:100',
            'color'      => 'required|regex:/^#[0-9A-Fa-f]{6}$/',
            'icon'       => 'nullable|max:32',
            'is_active'  => 'boolean',
            'sort_order' => 'integer|min:0',
        ];
    }

    public function openCreate(): void
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        $channel = SalesChannel::findOrFail($id);
        $this->editingId  = $channel->id;
        $this->name       = $channel->name;
        $this->color      = $channel->color;
        $this->icon       = $channel->icon ?? '';
        $this->is_active  = (bool) $channel->is_active;
        $this->sort_order = (int) $channel->sort_order;
        $this->showModal  = true;
    }

    public function save(): void
    {
        $this->validate();

        $slug = Str::slug($this->name);
        if ($this->editingId) {
            $channel = SalesChannel::findOrFail($this->editingId);
            if ($channel->name !== $this->name) {
                $slug = $this->uniqueSlug($slug, $this->editingId);
            } else {
                $slug = $channel->slug;
            }
            $channel->update([
                'name'       => $this->name,
                'slug'       => $slug,
                'color'      => $this->color,
                'icon'       => $this->icon ?: null,
                'is_active'  => $this->is_active,
                'sort_order' => $this->sort_order,
            ]);
            $this->dispatch('notify', message: 'Cập nhật kênh bán thành công!', type: 'success');
        } else {
            SalesChannel::create([
                'name'       => $this->name,
                'slug'       => $this->uniqueSlug($slug),
                'color'      => $this->color,
                'icon'       => $this->icon ?: null,
                'is_active'  => $this->is_active,
                'sort_order' => $this->sort_order,
            ]);
            $this->dispatch('notify', message: 'Thêm kênh bán thành công!', type: 'success');
        }

        $this->closeModal();
    }

    protected function uniqueSlug(string $base, ?int $ignoreId = null): string
    {
        $slug = $base ?: 'kenh';
        $i = 2;
        while (SalesChannel::where('slug', $slug)->when($ignoreId, fn($q) => $q->where('id', '!=', $ignoreId))->exists()) {
            $slug = $base . '-' . $i++;
        }
        return $slug;
    }

    public function toggleActive(int $id): void
    {
        $channel = SalesChannel::findOrFail($id);
        $channel->update(['is_active' => !$channel->is_active]);
    }

    public function confirmDelete(int $id): void
    {
        $this->confirmDeleteId = $id;
    }

    public function delete(): void
    {
        if (!$this->confirmDeleteId) return;
        SalesChannel::findOrFail($this->confirmDeleteId)->delete();
        $this->dispatch('notify', message: 'Đã xoá kênh bán!', type: 'success');
        $this->confirmDeleteId = null;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetForm();
    }

    protected function resetForm(): void
    {
        $this->editingId  = null;
        $this->name       = '';
        $this->color      = '#0088CC';
        $this->icon       = '';
        $this->is_active  = true;
        $this->sort_order = 0;
        $this->resetErrorBag();
    }

    public function render()
    {
        $channels = SalesChannel::query()
            ->when($this->search, fn($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('livewire.system.sales-channel-index', [
            'channels' => $channels,
        ])->layout('layouts.app');
    }
}
