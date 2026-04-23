<?php

namespace App\Livewire\Category;

use App\Models\Category;
use Livewire\Component;
use Livewire\WithPagination;
use App\Traits\WithBulkActions;
use Illuminate\Support\Str;

class CategoryIndex extends Component
{
    use WithPagination, WithBulkActions;

    public $search = '';
    public $perPage = 10;

    // Form properties
    public $categoryId;
    public $name, $slug;

    protected $queryString = [
        'search' => ['except' => ''],
        'perPage' => ['except' => 10],
    ];

    protected $rules = [
        'name' => 'required|min:2|unique:categories,name',
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function resetForm()
    {
        $this->categoryId = null;
        $this->name = '';
        $this->slug = '';
        $this->resetErrorBag();
    }

    public function create()
    {
        $this->resetForm();
        $this->dispatch('open-category-modal');
    }

    public function edit($id)
    {
        $this->resetForm();
        $category = Category::findOrFail($id);
        $this->categoryId = $category->id;
        $this->name = $category->name;
        $this->slug = $category->slug;
        
        $this->dispatch('open-category-modal');
    }

    public function save()
    {
        $rules = $this->rules;
        if ($this->categoryId) {
            $rules['name'] = 'required|min:2|unique:categories,name,' . $this->categoryId;
        }

        $this->validate($rules);

        $data = [
            'name' => $this->name,
            'slug' => Str::slug($this->name),
        ];

        if ($this->categoryId) {
            Category::find($this->categoryId)->update($data);
            $this->dispatch('notify', message: 'Cập nhật danh mục thành công!', type: 'success');
        } else {
            Category::create($data);
            $this->dispatch('notify', message: 'Thêm danh mục thành công!', type: 'success');
        }

        $this->dispatch('close-category-modal');
        $this->resetForm();
    }

    public function confirmDelete($id)
    {
        $this->categoryId = $id;
        $this->dispatch('open-delete-modal');
    }

    public function delete()
    {
        Category::find($this->categoryId)->delete();
        $this->dispatch('notify', message: 'Đã xóa danh mục!', type: 'success');
        $this->dispatch('close-delete-modal');
        $this->categoryId = null;
    }

    public function getCategories()
    {
        return Category::query()
            ->when($this->search, fn($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->orderBy('created_at', 'desc')
            ->paginate($this->perPage);
    }

    protected function getRecordsForBulk()
    {
        return $this->getCategories();
    }

    protected function getModelForBulk()
    {
        return Category::class;
    }

    public function render()
    {
        return view('livewire.category.category-index', [
            'categories' => $this->getCategories()
        ])->layout('layouts.app');
    }
}
