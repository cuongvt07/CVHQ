<?php

namespace App\Livewire\User;

use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Hash;
use App\Traits\WithColumnVisibility;
use App\Traits\WithUserPreferences;
use App\Traits\WithBulkActions;
use App\Traits\HasPermissions;

class UserIndex extends Component
{
    use WithPagination, WithBulkActions, HasPermissions, WithColumnVisibility, WithUserPreferences;

    protected function getModuleKey(): string
    {
        return 'users';
    }

    public $search = '';
    public $roleFilter = 'All';
    public $perPage = 10;

    protected function getDefaultVisibleColumns(): array
    {
        return ['info', 'role', 'work_branch', 'created_at', 'actions'];
    }

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
    public $name, $email, $password, $role = 'staff', $can_receive_commission = true, $work_branch = '', $permissions = [];

    protected function rules()
    {
        return [
            'name' => 'required|min:3',
            'email' => 'required|email|unique:users,email,' . $this->userId,
            'password' => $this->userId ? 'nullable|min:6' : 'required|min:6',
            'role' => 'required|in:admin,staff',
            'can_receive_commission' => 'boolean',
            'work_branch' => 'nullable|in:sg,hn',
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
        $this->can_receive_commission = true;
        $this->work_branch = '';
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
        $this->can_receive_commission = $user->can_receive_commission;
        $this->work_branch = $user->work_branch ?? '';
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
            'can_receive_commission' => $this->can_receive_commission,
            'work_branch' => $this->work_branch ?: null,
            'permissions' => $this->role === 'admin' ? null : $this->permissions,
        ];

        if ($this->password) {
            $data['password'] = Hash::make($this->password);
        }

        if ($this->userId) {
            User::find($this->userId)->update($data);
            $this->dispatch('notify', message: 'Cáº­p nháº­t nhÃ¢n viÃªn thÃ nh cÃ´ng!', type: 'success');
        } else {
            User::create($data);
            $this->dispatch('notify', message: 'ThÃªm nhÃ¢n viÃªn thÃ nh cÃ´ng!', type: 'success');
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
            $this->dispatch('notify', message: 'Báº¡n khÃ´ng thá»ƒ tá»± xÃ³a chÃ­nh mÃ¬nh!', type: 'error');
            $this->dispatch('close-delete-modal');
            return;
        }

        User::find($this->userId)->delete();
        $this->dispatch('notify', message: 'ÄÃ£ xÃ³a tÃ i khoáº£n nhÃ¢n viÃªn!', type: 'success');
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
            ->paginate($this->perPage)
            ->onEachSide(1);
    }

    public function getAvailablePermissionsProperty()
    {
        return [
            'dashboard' => ['label' => 'Tá»•ng quan', 'actions' => []],
            'pos' => ['label' => 'BÃ¡n hÃ ng (POS)', 'actions' => []],
            'products' => [
                'label' => 'Sáº£n pháº©m',
                'actions' => [
                    'product.edit_commission' => 'Sá»­a hoa há»“ng sáº£n pháº©m',
                    'product.stock_check' => 'Kiá»ƒm kho',
                    'product.stock_check_delete' => 'XÃ³a phiáº¿u kiá»ƒm kho',
                    'product.delete' => 'XÃ³a sáº£n pháº©m'
                ]
            ],
            'categories' => ['label' => 'Danh má»¥c', 'actions' => []],
            'commissions' => ['label' => 'Báº£ng hoa há»“ng', 'actions' => []],
            'customers' => ['label' => 'KhÃ¡ch hÃ ng', 'actions' => []],
            'users' => ['label' => 'NhÃ¢n viÃªn', 'actions' => []],
            'invoices' => [
                'label' => 'HÃ³a Ä‘Æ¡n',
                'actions' => [
                    'invoice.edit' => 'Sá»­a hÃ³a Ä‘Æ¡n',
                    'invoice.return' => 'Tráº£ hÃ ng',
                    'invoice.cancel' => 'Há»§y hÃ³a Ä‘Æ¡n',
                    'invoice.view_commission' => 'Xem hoa há»“ng'
                ]
            ],
            'reports' => ['label' => 'BÃ¡o cÃ¡o', 'actions' => []],
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
