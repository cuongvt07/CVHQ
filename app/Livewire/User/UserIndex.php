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
    public $name, $username, $email, $password, $role = 'staff', $can_receive_commission = true, $work_branch = '', $permissions = [];
    public $is_active = true;

    // Khi sao chép nhân viên: tên nhân viên nguồn (hiển thị nhắc trong form)
    public $copiedFromName = null;

    protected function rules()
    {
        return [
            'name' => 'required|min:3',
            'username' => 'required|min:3|unique:users,username,' . $this->userId,
            'email' => 'nullable|email|unique:users,email,' . $this->userId,
            'password' => $this->userId ? 'nullable|min:6' : 'required|min:6',
            'role' => 'required|in:admin,staff',
            'can_receive_commission' => 'boolean',
            'work_branch' => 'nullable|in:' . implode(',', array_keys(\App\Models\Branch::options())),
            'permissions' => 'nullable|array',
        ];
    }

    public function resetForm()
    {
        $this->userId = null;
        $this->name = '';
        $this->username = '';
        $this->email = '';
        $this->password = '';
        $this->role = 'staff';
        $this->is_active = true;
        $this->can_receive_commission = true;
        $this->work_branch = '';
        $this->permissions = [];
        $this->copiedFromName = null;
        $this->resetErrorBag();
    }

    /** Chặn thao tác nếu thiếu quyền chi tiết (admin luôn vượt qua). */
    private function ensure(string $perm): bool
    {
        if (auth()->user()->hasPermission($perm)) {
            return true;
        }
        $this->dispatch('notify', message: 'Bạn không có quyền thực hiện thao tác này!', type: 'error');
        return false;
    }

    public function create()
    {
        if (!$this->ensure('user.create')) return;
        $this->resetForm();
        $this->dispatch('open-user-modal');
    }

    public function edit($id)
    {
        if (!$this->ensure('user.edit')) return;
        $this->resetForm();
        $user = User::findOrFail($id);
        $this->userId = $user->id;
        $this->name = $user->name;
        $this->username = $user->username;
        $this->email = $user->email;
        $this->role = $user->role;
        $this->is_active = (bool) $user->is_active;
        $this->can_receive_commission = $user->can_receive_commission;
        $this->work_branch = $user->work_branch ?? '';
        $this->permissions = $user->permissions ?? [];

        $this->dispatch('open-user-modal');
    }

    /**
     * Sao chép nhân viên: tạo nhân viên mới với toàn bộ quyền (và vai trò,
     * chi nhánh, cấu hình hoa hồng) giống nhân viên nguồn. Chỉ cần nhập
     * tên / email / mật khẩu mới.
     */
    public function copy($id)
    {
        if (!$this->ensure('user.create')) return;
        $source = User::findOrFail($id);

        $this->resetForm();
        // userId vẫn null => save() sẽ TẠO MỚI, không sửa nhân viên nguồn.
        $this->role = $source->role;
        $this->can_receive_commission = $source->can_receive_commission;
        $this->work_branch = $source->work_branch ?? '';
        $this->permissions = $source->permissions ?? [];
        $this->copiedFromName = $source->name;

        $this->dispatch('open-user-modal');
        $this->dispatch('notify', message: "Đã sao chép quyền từ \"{$source->name}\". Nhập tên, email và mật khẩu cho nhân viên mới.", type: 'info');
    }

    public function save()
    {
        if (!$this->ensure($this->userId ? 'user.edit' : 'user.create')) return;
        $this->validate();

        $data = [
            'name' => $this->name,
            'username' => $this->username,
            'email' => $this->email ?: null,
            'role' => $this->role,
            'is_active' => $this->is_active,
            'can_receive_commission' => $this->can_receive_commission,
            'work_branch' => $this->work_branch ?: null,
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
        if (!$this->ensure('user.delete')) return;
        $this->userId = $id;
        $this->dispatch('open-delete-modal');
    }

    public function delete()
    {
        if (!$this->ensure('user.delete')) return;
        if ($this->userId === auth()->id()) {
            $this->dispatch('notify', message: 'Bạn không thể tự xóa chính mình!', type: 'error');
            $this->dispatch('close-delete-modal');
            return;
        }

        // Xóa MỀM: giữ bản ghi để hóa đơn cũ vẫn liên kết được (user_id còn hợp lệ).
        User::find($this->userId)->delete();
        $this->dispatch('notify', message: 'Đã xóa tài khoản nhân viên!', type: 'success');
        $this->dispatch('close-delete-modal');
        $this->userId = null;
    }

    /**
     * Ngừng hoạt động / kích hoạt lại tài khoản (nhân viên nghỉ việc).
     * Không xóa — giữ nguyên dữ liệu, chỉ chặn đăng nhập.
     */
    public function toggleActive($id)
    {
        if (!$this->ensure('user.edit')) return;
        if ((int) $id === (int) auth()->id()) {
            $this->dispatch('notify', message: 'Không thể tự ngừng hoạt động chính mình!', type: 'error');
            return;
        }

        $user = User::find($id);
        if (!$user) {
            return;
        }
        $user->update(['is_active' => ! $user->is_active]);
        $this->dispatch('notify', message: $user->is_active
            ? 'Đã kích hoạt lại tài khoản.'
            : 'Đã ngừng hoạt động tài khoản.', type: 'success');
    }

    /**
     * Tick ô phân quyền tổng (module) -> tự tick/bỏ tick toàn bộ ô con (actions).
     */
    public function toggleModule($moduleKey, $checked)
    {
        if (!$this->ensure('user.permissions')) return;
        $actions = array_keys($this->availablePermissions[$moduleKey]['actions'] ?? []);
        $keys = array_merge([$moduleKey], $actions);
        $perms = is_array($this->permissions) ? $this->permissions : [];

        $this->permissions = $checked
            ? array_values(array_unique(array_merge($perms, $keys)))
            : array_values(array_diff($perms, $keys));
    }

    public function getUsers()
    {
        return User::query()
            ->when($this->search, function($query) {
                $query->where('name', 'like', "%{$this->search}%")
                      ->orWhere('username', 'like', "%{$this->search}%")
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
            'dashboard' => ['label' => 'Tổng quan', 'actions' => []],
            'pos' => ['label' => 'Bán hàng (POS)', 'actions' => []],
            'products' => [
                'label' => 'Sản phẩm',
                'actions' => [
                    'product.edit_commission' => 'Sửa hoa hồng sản phẩm',
                    'product.stock_check' => 'Kiểm kho',
                    'product.stock_check_delete' => 'Xóa phiếu kiểm kho',
                    'product.delete' => 'Xóa sản phẩm'
                ]
            ],
            'categories' => ['label' => 'Danh mục', 'actions' => []],
            'commissions' => [
                'label' => 'Bảng hoa hồng',
                'actions' => [
                    'commission.edit' => 'Sửa hoa hồng',
                    'commission.sync' => 'Đồng bộ hoa hồng',
                    'commission.import' => 'Nhập Excel',
                    'commission.export' => 'Xuất Excel',
                    'commission.settings' => 'Cấu hình hoa hồng tự động',
                ]
            ],
            'customers' => ['label' => 'Khách hàng', 'actions' => []],
            'users' => [
                'label' => 'Nhân viên',
                'actions' => [
                    'user.create' => 'Thêm nhân viên',
                    'user.edit' => 'Sửa nhân viên',
                    'user.delete' => 'Xóa nhân viên',
                    'user.permissions' => 'Phân quyền nhân viên',
                ]
            ],
            'invoices' => [
                'label' => 'Hóa đơn',
                'actions' => [
                    'invoice.edit' => 'Sửa hóa đơn',
                    'invoice.return' => 'Trả hàng',
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
