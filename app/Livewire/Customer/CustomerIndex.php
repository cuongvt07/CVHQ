<?php

namespace App\Livewire\Customer;

use App\Models\Customer;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use App\Imports\CustomersImport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use App\Traits\WithBulkActions;

class CustomerIndex extends Component
{
    use WithPagination, WithFileUploads, WithBulkActions;

    public $search = '';
    public $importFile;
    public $perPage = 10;

    // Import Progress
    public $importing = false;
    public $importProgress = 0;
    public $importTotal = 0;
    public $importCurrent = 0;
    public $importErrors = [];
    public $importBatchId;

    // Form properties
    public $customerId;
    public $customer_code, $full_name, $phone, $email, $address, $customer_group, $note;
    public $status = 'Active';

    protected $rules = [
        'customer_code' => 'required|unique:customers,customer_code',
        'full_name' => 'required|min:3',
        'phone' => 'nullable|numeric',
        'email' => 'nullable|email',
        'customer_group' => 'nullable',
        'status' => 'required|in:Active,Inactive',
    ];

    protected $queryString = [
        'search' => ['except' => ''],
        'perPage' => ['except' => 10],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingPerPage()
    {
        $this->resetPage();
    }

    public function import()
    {
        $this->validate([
            'importFile' => 'required|mimes:xlsx,xls,csv|max:10240',
        ]);

        $this->importBatchId = Str::random(10);
        $this->importing = true;
        $this->importProgress = 0;
        $this->importErrors = [];

        try {
            $import = new CustomersImport();
            $import->setImportKey($this->importBatchId);
            Excel::import($import, $this->importFile->getRealPath());
            
            $this->importFile = null;
        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            $this->importing = false;
            $failures = $e->failures();
            foreach ($failures as $failure) {
                $this->importErrors[] = "Dòng {$failure->row()}: " . implode(', ', $failure->errors());
            }
        } catch (\Exception $e) {
            $this->importing = false;
            $this->importErrors[] = $e->getMessage();
        }
    }

    public function pollImportProgress()
    {
        if (!$this->importing) return;

        $progress = Cache::get("import_progress_{$this->importBatchId}");

        if ($progress) {
            $this->importTotal = $progress['total'];
            $this->importCurrent = $progress['current'];
            
            if ($this->importTotal > 0) {
                $this->importProgress = min(100, round(($this->importCurrent / $this->importTotal) * 100));
            }

            if ($this->importCurrent >= $this->importTotal || $progress['status'] === 'failed' || $progress['status'] === 'finished') {
                $this->importing = false;
                $this->importErrors = array_merge($this->importErrors, $progress['errors']);
                
                if (empty($this->importErrors)) {
                    $this->dispatch('notify', message: 'Import hoàn tất thành công!', type: 'success');
                }
                
                $this->dispatch('import-finished', id: 'customers');
            }
        }
    }

    public function resetForm()
    {
        $this->customerId = null;
        $this->customer_code = '';
        $this->full_name = '';
        $this->phone = '';
        $this->email = '';
        $this->address = '';
        $this->customer_group = 'Khách lẻ';
        $this->note = '';
        $this->status = 'Active';
        $this->resetErrorBag();
    }

    public function create()
    {
        $this->resetForm();
        $this->dispatch('open-customer-modal');
    }

    public function edit($id)
    {
        $this->resetForm();
        $customer = Customer::findOrFail($id);
        $this->customerId = $customer->id;
        $this->customer_code = $customer->customer_code;
        $this->full_name = $customer->full_name;
        $this->phone = $customer->phone;
        $this->email = $customer->email;
        $this->address = $customer->address;
        $this->customer_group = $customer->customer_group;
        $this->note = $customer->note;
        $this->status = $customer->status;
        
        $this->dispatch('open-customer-modal');
    }

    public function save()
    {
        $rules = $this->rules;
        if ($this->customerId) {
            $rules['customer_code'] = 'required|unique:customers,customer_code,' . $this->customerId;
        }

        $this->validate($rules);

        $data = [
            'customer_code' => $this->customer_code,
            'full_name' => $this->full_name,
            'phone' => $this->phone,
            'email' => $this->email,
            'address' => $this->address,
            'customer_group' => $this->customer_group,
            'note' => $this->note,
            'status' => $this->status,
        ];

        if ($this->customerId) {
            Customer::find($this->customerId)->update($data);
            $this->dispatch('notify', message: 'Cập nhật khách hàng thành công!', type: 'success');
        } else {
            Customer::create($data);
            $this->dispatch('notify', message: 'Thêm khách hàng thành công!', type: 'success');
        }

        $this->dispatch('close-customer-modal');
        $this->resetForm();
    }

    public function confirmDelete($id)
    {
        $this->customerId = $id;
        $this->dispatch('open-delete-modal');
    }

    public function delete()
    {
        Customer::find($this->customerId)->delete();
        $this->dispatch('notify', message: 'Đã xóa khách hàng!', type: 'success');
        $this->dispatch('close-delete-modal');
        $this->customerId = null;
    }

    public function getCustomers()
    {
        return Customer::query()
            ->when($this->search, fn($q) => $q->where('full_name', 'like', "%{$this->search}%")
                                              ->orWhere('customer_code', 'like', "%{$this->search}%")
                                              ->orWhere('phone', 'like', "%{$this->search}%"))
            ->orderBy('created_at', 'desc')
            ->paginate($this->perPage);
    }

    protected function getRecordsForBulk()
    {
        return $this->getCustomers();
    }

    protected function getModelForBulk()
    {
        return Customer::class;
    }

    public function render()
    {
        return view('livewire.customer.customer-index', [
            'customers' => $this->getCustomers()
        ])->layout('layouts.app');
    }
}
