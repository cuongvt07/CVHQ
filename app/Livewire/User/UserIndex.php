<?php

namespace App\Livewire\User;

use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Hash;
use App\Traits\WithBulkActions;
use App\Traits\HasPermissions;

class UserIndex extends Component
{
    use WithPagination, WithBulkActions, HasPermissions;

    protected function getModuleKey(): string
    {
        return 'users';
    }

    public $search = '';
    public $roleFilter = 'All';
    public $perPage = 10;

    protected $queryString = [
        'search' => ['except' => ''],
        'roleFilter' => ['except' => 'All'],
        'perPage' => ['except' => 10],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    // Form properties
    public $userId;
    public $name, $email, $password, $role = 'staff', $permissions = [];

    protected function rules()
    {
        return [
            'name' => 'required|min:3',
            'email' => 'required|email|unique:users,email,' . $this->userId,
            'password' => $this->userId ? 'nullable|min:6' : 'required|min:6',
            'role' => 'required|in:admin,staff',
            'permissions' => 'nullable|array',
        ];
    }

    public function resetForm()
    {
        $this->userId = null;
        $this->name = '';
        $this->email = '';
        $this->password = '';
        $this->role = 'staff';
        $this->permissions = [];
        $this->resetErrorBag();
    }

    public function create()
    {
        $this->resetForm();
        $this->dispatch('open-user-modal');
    }

    public function edit($id)
    {
        $this->resetForm();
        $user = User::findOrFail($id);
        $this->userId = $user->id;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->role = $user->role;
        $this->permissions = $user->permissions ?? [];
        
        $this->dispatch('open-user-modal');
    }

    public function save()
    {
        $this->validate();

        $data = [
            'name' => $this->name,
            'email' => $this->email,
            'role' => $this->role,
            'permissions' => $this->role === 'admin' ? null : $this->permissions,
        ];

        if ($this->password) {
            $data['password'] = Hash::make($this->password);
        }

        if ($this->userId) {
            User::find($this->userId)->update($data);
            $this->dispatch('notify', message: 'Cập nhật nhân viên thành công!', type: 'success');
        } else {
            User::create($data);
            $this->dispatch('notify', message: 'Thêm nhân viên thành công!', type: 'success');
        }

        $this->dispatch('close-user-modal');
        $this->resetForm();
    }

    public function confirmDelete($id)
    {
        $this->userId = $id;
        $this->dispatch('open-delete-modal');
    }

    public function delete()
    {
        if ($this->userId === auth()->id()) {
            $this->dispatch('notify', message: 'Bạn không thể tự xóa chính mình!', type: 'error');
            $this->dispatch('close-delete-modal');
            return;
        }

        User::find($this->userId)->delete();
        $this->dispatch('notify', message: 'Đã xóa tài khoản nhân viên!', type: 'success');
        $this->dispatch('close-delete-modal');
        $this->userId = null;
    }

    public function getUsers()
    {
        return User::query()
            ->when($this->search, function($query) {
                $query->where('name', 'like', "%{$this->search}%")
                      ->orWhere('email', 'like', "%{$this->search}%");
            })
            ->when($this->roleFilter !== 'All', fn($q) => $q->where('role', $this->roleFilter))
            ->orderBy('created_at', 'desc')
            ->paginate($this->perPage);
    }

    public function getAvailablePermissionsProperty()
    {
        return [
            'dashboard' => ['label' => 'Tổng quan', 'actions' => []],
            'pos' => ['label' => 'Bán hàng (POS)', 'actions' => []],
            'products' => [
                'label' => 'Sản phẩm',
                'actions' => [
                    'product.edit_commission' => 'Sửa hoa hồng sản phẩm'
                ]
            ],
            'categories' => ['label' => 'Danh mục', 'actions' => []],
            'customers' => ['label' => 'Khách hàng', 'actions' => []],
            'users' => ['label' => 'Nhân viên', 'actions' => []],
            'invoices' => [
                'label' => 'Hóa đơn',
                'actions' => [
                    'invoice.edit' => 'Sửa & Trả hàng',
                    'invoice.cancel' => 'Hủy hóa đơn',
                    'invoice.view_commission' => 'Xem hoa hồng'
                ]
            ],
            'reports' => ['label' => 'Báo cáo', 'actions' => []],
        ];
    }

    protected function getRecordsForBulk()
    {
        return $this->getUsers();
    }

    protected function getModelForBulk()
    {
        return User::class;
    }

    public function render()
    {
        return view('livewire.user.user-index', [
            'users' => $this->getUsers()
        ])->layout('layouts.app');
    }
}
