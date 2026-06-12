<?php

namespace App\Livewire\Product;

use App\Models\Product;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use App\Imports\ProductsImport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Traits\WithBulkActions;
use App\Traits\HasPermissions;
use App\Traits\WithColumnVisibility;
use App\Traits\WithUserPreferences;

class ProductIndex extends Component
{
    use WithPagination, WithFileUploads, WithBulkActions, HasPermissions, WithColumnVisibility, WithUserPreferences;

    protected function getModuleKey(): string
    {
        return 'products';
    }

    protected function getPersistedProperties(): array
    {
        return ['perPage', 'branch'];
    }

    protected function getDefaultVisibleColumns(): array
    {
        return ['sku', 'brand', 'category', 'location', 'stock', 'price', 'actions'];
    }

    public $search = '';
    public $category = 'All';
    public $selectedCategories = [];
    public $boxCode = '';
    public $brandFilter = '';
    public $stockStatus = 'all';
    public $importFile;
    public $perPage = 10;
    public $branch = 'all';
    public $quickEditMode = false;
    public $sortField = 'created_at';
    public $sortDirection = 'desc';

    // Import Progress
    public $importing = false;
    public $importProgress = 0;
    public $importTotal = 0;
    public $importCurrent = 0;
    public $importErrors = [];
    public $importBatchId;

    // Bulk Add Feature
    public $bulkPrefix = '';
    public $bulkBaseName = '';
    public $bulkCategory = '';
    public $bulkBrand = '';
    public $bulkSalePrice = 0;
    public $bulkCommission = 0;
    public $bulkRowCount = 30;
    public array $bulkProducts = [];
    // public $commissionRanges = [];

    protected $queryString = [
        'perPage' => ['except' => 10],
        'branch' => ['except' => 'all'],
        'sortField' => ['except' => 'created_at'],
        'sortDirection' => ['except' => 'desc'],
        'visibleColumns' => ['except' => ['sku', 'name', 'brand', 'category', 'price', 'stock', 'location', 'actions']],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingCategory()
    {
        $this->resetPage();
        if ($this->category !== 'All') {
            $this->selectedCategories = [$this->category];
        } else {
            $this->selectedCategories = [];
        }
    }

    public function updatedSelectedCategories()
    {
        $this->resetPage();
    }

    public function updatedBoxCode()
    {
        $this->resetPage();
    }

    public function updatedStockStatus()
    {
        $this->resetPage();
    }

    public function updatedBranch()
    {
        $this->resetPage();
    }

    public function updatingPerPage()
    {
        $this->resetPage();
    }

    public function updatedSortField()
    {
        if (!array_key_exists($this->sortField, $this->sortableFields())) {
            $this->sortField = 'created_at';
        }

        $this->resetPage();
    }

    public function updatedSortDirection()
    {
        if (!in_array($this->sortDirection, ['asc', 'desc'], true)) {
            $this->sortDirection = 'desc';
        }

        $this->resetPage();
    }

    public function updatedSalePrice()
    {
        if (!$this->isAutoCommissionEnabled()) {
            return;
        }

        $this->commission_amount = $this->commissionForPrice((int) $this->sale_price);
    }

    protected function isAutoCommissionEnabled(): bool
    {
        return filter_var(\App\Models\SystemSetting::get('auto_commission_enabled', false), FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * Danh sách vị trí hàng hóa đã tồn tại — dùng để gợi ý khi nhập vị trí
     * (cả thêm từng sản phẩm lẫn thêm hàng loạt).
     */
    public function getLocationOptionsProperty(): array
    {
        return \App\Models\Product::getUniqueLocations();
    }

    /**
     * Bấm nút "+" cạnh ô vị trí: xác nhận vị trí đang nhập (có thể là vị trí mới).
     * Vị trí sẽ được lưu cùng sản phẩm khi bấm Lưu.
     */
    public function confirmLocation(): void
    {
        $loc = trim((string) $this->location);
        if ($loc === '') {
            $this->dispatch('notify', message: 'Vui lòng nhập vị trí trước khi thêm.', type: 'warning');
            return;
        }

        $this->location = $loc;
        $isNew = !in_array($loc, \App\Models\Product::getUniqueLocations(), true);
        $this->dispatch('notify', message: $isNew ? "Đã thêm vị trí mới: {$loc}" : "Đã chọn vị trí: {$loc}", type: 'success');
    }

    protected function commissionForPrice(int $price): int
    {
        $ranges = \App\Models\SystemSetting::get('commission_ranges', []);
        if (!is_array($ranges)) {
            return 0;
        }

        foreach ($ranges as $range) {
            $min = (int) ($range['min'] ?? 0);
            $max = (int) ($range['max'] ?? 0);
            $amount = (int) ($range['amount'] ?? 0);

            if ($price >= $min && ($max <= 0 || $price < $max)) {
                return $amount;
            }
        }

        return 0;
    }

    public function clearFilter($type, $value = null)
    {
        if ($type === 'all') {
            $this->selectedCategories = [];
            $this->boxCode = '';
            $this->brandFilter = '';
            $this->stockStatus = 'all';
            $this->search = '';
            $this->category = 'All';
            $this->branch = 'all';
        } elseif ($type === 'selectedCategories') {
            if ($value) {
                $this->selectedCategories = array_diff($this->selectedCategories, [$value]);
            } else {
                $this->selectedCategories = [];
            }
        } elseif ($type === 'boxCode') {
            $this->boxCode = '';
        } elseif ($type === 'brandFilter') {
            $this->brandFilter = '';
        } elseif ($type === 'stockStatus') {
            $this->stockStatus = 'all';
        } elseif ($type === 'search') {
            $this->search = '';
        }

        $this->resetPage();
    }

    public function clearSelection(): void
    {
        $this->selectedRows = [];
        $this->selectAll = false;
    }

    public $expandedProductId = null;

    public function toggleHistory($id)
    {
        if ($this->expandedProductId === $id) {
            $this->expandedProductId = null;
        } else {
            $this->expandedProductId = $id;
        }
    }

    // Form properties
    public $productId;
    public $sku, $base_name, $category_path, $brand, $sale_price, $commission_amount, $stock_quantity, $location;
    public $is_active = true;
    public $newImages = []; // Array of UploadedFile objects
    public $existingImages = []; // Array of strings (paths)
    public $capturedImages = []; // Array of Base64 strings
    public $productAttributes = []; // [['key' => '', 'value' => '']]
    public $existingKeys = [];
    public $isAutoGenerated = false;

    protected $rules = [
        'sku' => 'required|unique:products,sku',
        'base_name' => 'required|min:3',
        'category_path' => 'nullable',
        'brand' => 'nullable',
        'sale_price' => 'required|numeric|min:0',
        'stock_quantity' => 'nullable|numeric|min:0',
        'location' => 'nullable|string|max:255',
        'is_active' => 'boolean',
        'newImages.*' => 'nullable|image|max:5120',
    ];

    public function import()
    {
        // Tăng thời gian thực thi ngay từ đầu để tránh lỗi timeout trong quá trình validate và đọc file
        set_time_limit(300);

        $this->validate([
            'importFile' => 'required', // Chỉ yêu cầu có file, bỏ qua mimes/max để tránh lỗi metadata trên server
        ]);

        $this->importBatchId = Str::random(10);
        $this->importing = true;
        $this->importProgress = 0;
        $this->importErrors = [];

        try {
            $import = new ProductsImport();
            $import->setImportKey($this->importBatchId);

            // Store the file to ensure it's available for the queue worker
            $filePath = $this->importFile->store('imports');

            Excel::queueImport($import, $filePath);

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
        if (!$this->importing)
            return;

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

                $this->dispatch('import-finished', id: 'products');
            }
        }
    }

    public function resetForm()
    {
        $this->productId = null;
        $this->sku = '';
        $this->base_name = '';
        $this->category_path = '';
        $this->brand = '';
        $this->sale_price = 0;
        $this->commission_amount = 0;
        $this->stock_quantity = 999;
        $this->location = '';
        $this->is_active = true;
        $this->newImages = [];
        $this->existingImages = [];
        $this->capturedImages = [];
        $this->productAttributes = [];
        $this->isAutoGenerated = false;
        $this->resetErrorBag();
    }

    public function addAttribute()
    {
        $this->productAttributes[] = ['key' => '', 'value' => ''];
    }

    public function removeAttribute($index)
    {
        unset($this->productAttributes[$index]);
        $this->productAttributes = array_values($this->productAttributes);
    }

    public function loadAttributeSuggestions()
    {
        $this->existingKeys = Product::getUniqueAttributeKeys();
    }

    public function create()
    {
        $this->resetForm();
        $this->loadAttributeSuggestions();

        // SKU starts blank — user types a prefix (e.g. "GTS") and tabs to auto-fill.
        if ($this->isAutoCommissionEnabled()) {
            $this->updatedSalePrice();
        }

        $this->dispatch('open-product-modal');
    }

    /**
     * Livewire hook: on SKU changes, auto-complete to next available SKU using prefix.
     * Format: <prefix>-<number>. Prefix is CASE-SENSITIVE: "abc", "ABC", "Abc" are distinct.
     */
    public function updatedSku($value): void
    {
        if ($this->productId) return; // edit mode — never rewrite

        $value = trim((string) $value);
        if ($value === '') {
            $this->sku = '';
            $this->isAutoGenerated = false;
            return;
        }

        if ($this->sku !== $value) {
            $this->sku = $value;
        }

        if (!preg_match('/^[A-Za-z0-9_\-]+$/', $value)) {
            $this->isAutoGenerated = false;
            return;
        }

        // Already in full format <prefix>-<number> → user typed a specific SKU, don't rewrite
        if (preg_match('/-\d+$/', $value)) {
            $this->isAutoGenerated = false;
            return;
        }

        // Strip trailing dash if user typed "abc-" — treat as just "abc"
        $prefix = rtrim($value, '-');

        // Require prefix to be at least 2 characters to avoid aggressive auto-completion on single-letter entry
        if (strlen($prefix) < 2) {
            $this->isAutoGenerated = false;
            return;
        }

        $nextSku = $this->nextSkuForPrefix($prefix);
        if ($nextSku !== $value) {
            $this->sku = $nextSku;
            $this->isAutoGenerated = true;
        } else {
            $this->isAutoGenerated = false;
        }
    }

    /**
     * Wire:click handler for the "Tạo mã" button — explicit re-suggest.
     */
    public function suggestSku(): void
    {
        if ($this->productId) return;
        $value = trim((string) $this->sku);
        if ($value === '') {
            $this->sku = $this->generateNewSku();
            $this->isAutoGenerated = true;
            return;
        }
        // Strip trailing "-N" (full SKU like "abc-123") or trailing dash to recover the prefix
        $prefix = preg_replace('/-?\d+$/', '', $value);
        $prefix = rtrim($prefix, '-');
        if ($prefix === '') $prefix = $value;
        $this->sku = $this->nextSkuForPrefix($prefix);
        $this->isAutoGenerated = true;
    }

    /**
     * Alias used by the product modal's "Tạo mã tự động" button (wire:click="generateSku").
     */
    public function generateSku(): void
    {
        $this->suggestSku();
    }

    /**
     * Auto-generate SKU for a brand-new product.
     * Pattern: "SP" + zero-padded 6-digit sequence (e.g. SP000001).
     * Picks the highest existing SP-numeric SKU and adds 1; falls back to SP000001.
     */
    private function generateNewSku(): string
    {
        $prefix = \App\Models\SystemSetting::get('sku_prefix', 'SP');
        $padding = (int) \App\Models\SystemSetting::get('sku_padding', 6);
        return $this->nextSkuForPrefix($prefix, $padding);
    }

    /**
     * Compute the next non-conflicting SKU for a given non-digit prefix.
     * FORMAT: <PREFIX>-<NUMBER>. Match only SKUs with dash separator.
     * "ABC" matches "ABC-1", "ABC-099" — NOT "ABC1", "ABCD-1", "ABC-X1".
     */
    private function nextSkuForPrefix(string $prefix, ?int $padding = null): string
    {
        $prefix = strtoupper(trim($prefix));
        $prefix = rtrim($prefix, '-'); // strip trailing dash if user typed it
        if ($prefix === '') return '';

        $defaultPadding = (int) \App\Models\SystemSetting::get('sku_padding', 6);
        $padding = $padding ?? $defaultPadding;

        // EXACT match via SQL REGEXP: ^<prefix>-<digits>$
        $pattern = '^' . preg_quote($prefix, '/') . '-[0-9]+$';

        $existing = Product::whereRaw('sku REGEXP ?', [$pattern])
            ->pluck('sku');

        $maxNumber = 0;
        $maxPadding = 0;
        $rx = '/^' . preg_quote($prefix, '/') . '-(\d+)$/i';
        $hasExisting = false;

        foreach ($existing as $sku) {
            if (preg_match($rx, $sku, $m)) {
                $hasExisting = true;
                $num = (int) $m[1];
                if ($num > $maxNumber) {
                    $maxNumber = $num;
                    $maxPadding = strlen($m[1]);
                }
            }
        }

        // If no existing SKU for this prefix, start at 1 with no padding (clean "ABC-1")
        // unless this is the default system-generated SKU (use configured padding for "SP-000001")
        if (!$hasExisting) {
            $maxPadding = ($padding > 0 && $prefix === strtoupper((string) \App\Models\SystemSetting::get('sku_prefix', 'SP')))
                ? $padding
                : 1;
        }

        $nextNumber = $maxNumber + 1;
        $candidate = $prefix . '-' . str_pad((string) $nextNumber, $maxPadding, '0', STR_PAD_LEFT);
        while (Product::where('sku', $candidate)->exists()) {
            $nextNumber++;
            $candidate = $prefix . '-' . str_pad((string) $nextNumber, $maxPadding, '0', STR_PAD_LEFT);
        }
        return $candidate;
    }

    public function edit($id)
    {
        $this->resetForm();
        $this->loadAttributeSuggestions();
        $product = Product::findOrFail($id);
        $this->productId = $product->id;
        $this->sku = $product->sku;
        $this->base_name = $product->base_name;
        $this->category_path = $product->category_path;
        $this->brand = $product->brand;
        $this->sale_price = $product->sale_price;
        $this->commission_amount = $product->commission_amount;
        $this->stock_quantity = $product->stock_quantity;
        $this->location = $product->location;
        $this->is_active = $product->is_active;
        $this->existingImages = is_array($product->images) ? $product->images : [];
        $this->newImages = [];
        $this->capturedImages = [];

        $this->productAttributes = [];
        if (!empty($product->attributes) && is_array($product->attributes)) {
            foreach ($product->attributes as $key => $value) {
                $this->productAttributes[] = ['key' => $key, 'value' => $value];
            }
        }

        $this->dispatch('open-product-modal');
    }

    public function save($keepOpen = false)
    {
        $currentSku = $this->sku;
        $rules = $this->rules;
        if ($this->productId) {
            $rules['sku'] = 'required|unique:products,sku,' . $this->productId;
        }

        $this->validate($rules);

        // Parse structured attributes
        $attributes = [];
        foreach ($this->productAttributes as $attr) {
            $key = trim($attr['key'] ?? '');
            if (!empty($key)) {
                $attributes[$key] = trim($attr['value'] ?? '');
            }
        }

        $productData = [
            'sku' => $this->sku,
            'base_name' => $this->base_name,
            'category_path' => $this->category_path,
            'brand' => $this->brand,
            'sale_price' => $this->sale_price,
            'stock_quantity' => $this->stock_quantity === '' || $this->stock_quantity === null ? 999 : $this->stock_quantity,
            'location' => $this->location,
            'is_active' => $this->is_active,
            'attributes' => $attributes,
        ];

        // Hoa hồng: nhân viên có quyền edit_commission có thể sửa luôn; nhân viên không có quyền chỉ được nhập khi TẠO MỚI.
        $canEditCommission = auth()->user()?->hasPermission('product.edit_commission');
        if ($this->isAutoCommissionEnabled()) {
            $this->commission_amount = $this->commissionForPrice((int) $this->sale_price);
        }
        if ($canEditCommission || !$this->productId || $this->isAutoCommissionEnabled()) {
            $productData['commission_amount'] = $this->commission_amount;
        }

        // Process Images
        $allImagePaths = $this->existingImages;

        // Handle uploaded images
        foreach ($this->newImages as $image) {
            $allImagePaths[] = $image->store('products', 'public');
        }

        // Handle captured images (Base64)
        foreach ($this->capturedImages as $base64) {
            $imageData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $base64));
            $fileName = 'products/' . Str::random(40) . '.png';
            \Storage::disk('public')->put($fileName, $imageData);
            $allImagePaths[] = $fileName;
        }

        $productData['images'] = $allImagePaths;

        if ($this->productId) {
            $product = Product::findOrFail($this->productId);
            $product->update($productData);
            $this->dispatch('notify', message: 'Cập nhật sản phẩm thành công!', type: 'success');
        } else {
            Product::create($productData);
            $this->dispatch('notify', message: 'Thêm sản phẩm thành công!', type: 'success');
        }

        if ($keepOpen) {
            $nextSku = $this->getNextSku($currentSku);

            // Clone data logic: keep everything except specific fields
            $this->productId = null;
            $this->sku = $nextSku;
            $this->location = '';
            $this->productAttributes = [];

            $this->loadAttributeSuggestions();
            $this->dispatch('notify', message: 'Đã lưu và sao chép dữ liệu cho sản phẩm tiếp theo!', type: 'success');
        } else {
            $this->dispatch('close-product-modal');
            $this->resetForm();
        }
    }

    public function saveAndCreateNext()
    {
        $this->save(true);
    }

    // Bulk Add Methods
    public function openBulkAddModal()
    {
        $this->bulkPrefix = \App\Models\SystemSetting::get('sku_prefix', 'SP');
        $this->bulkBaseName = '';
        $this->bulkCategory = '';
        $this->bulkBrand = '';
        $this->bulkSalePrice = 0;
        $this->bulkCommission = 0;
        $this->bulkRowCount = 30;
        $this->bulkProducts = [];
        $this->addBulkRow($this->bulkRowCount);
        
        $this->dispatch('open-bulk-modal');
    }

    public function updatedBulkSalePrice()
    {
        if (!$this->isAutoCommissionEnabled()) {
            return;
        }

        $this->bulkCommission = $this->commissionForPrice((int) $this->bulkSalePrice);
    }

    public function applyBulkRowCount()
    {
        $target = max(1, min(200, (int) $this->bulkRowCount));
        $this->bulkRowCount = $target;
        $current = count($this->bulkProducts);

        if ($target > $current) {
            $this->addBulkRow($target - $current);
            return;
        }

        if ($target < $current) {
            $this->bulkProducts = array_slice($this->bulkProducts, 0, $target);
        }
    }

    public function addBulkRow($count = 1)
    {
        for ($i = 0; $i < $count; $i++) {
            $this->bulkProducts[] = [
                'attribute' => '',
                'location' => '',
                'stock' => 999
            ];
        }
    }

    public function removeBulkRow($index)
    {
        unset($this->bulkProducts[$index]);
        $this->bulkProducts = array_values($this->bulkProducts);
    }

    public function saveBulkProducts()
    {
        $this->validate([
            'bulkPrefix' => 'required|min:1',
            'bulkBaseName' => 'required|min:2',
            'bulkSalePrice' => 'required|numeric|min:0',
        ]);

        if (empty($this->bulkProducts)) {
            $this->dispatch('notify', message: 'Vui lòng thêm ít nhất 1 dòng sản phẩm!', type: 'warning');
            return;
        }

        $count = 0;
        $canEditCommission = auth()->user()?->hasPermission('product.edit_commission');
        if ($this->isAutoCommissionEnabled()) {
            $this->bulkCommission = $this->commissionForPrice((int) $this->bulkSalePrice);
        }
        
        foreach ($this->bulkProducts as $row) {
            $attrVal = trim($row['attribute'] ?? '');
            $location = trim($row['location'] ?? '');
            if ($attrVal === '' && $location === '') {
                continue;
            }

            // Generate SKU
            $sku = $this->nextSkuForPrefix($this->bulkPrefix);
            
            // Build attributes array
            $attributes = [];
            if ($attrVal !== '') {
                $attributes['Màu sắc/Phân loại'] = $attrVal;
            }

            $productData = [
                'sku' => $sku,
                'base_name' => $this->bulkBaseName,
                'category_path' => $this->bulkCategory,
                'brand' => $this->bulkBrand,
                'sale_price' => $this->bulkSalePrice,
                'stock_quantity' => $row['stock'] === '' || $row['stock'] === null ? 999 : (int)$row['stock'],
                'location' => $location,
                'is_active' => true,
                'attributes' => $attributes,
            ];

            if ($canEditCommission || $this->isAutoCommissionEnabled()) {
                $productData['commission_amount'] = $this->bulkCommission;
            } else {
                $productData['commission_amount'] = 0;
            }

            Product::create($productData);
            $count++;
        }

        if ($count === 0) {
            $this->dispatch('notify', message: 'Chưa có dòng nào có màu hoặc vị trí để lưu.', type: 'warning');
            return;
        }

        $this->dispatch('notify', message: "Đã thêm thành công {$count} sản phẩm!", type: 'success');
        $this->dispatch('close-bulk-modal');
    }


    public function bulkCopyToSG()
    {
        if (empty($this->selectedRows)) {
            $this->dispatch('notify', message: 'Vui lòng chọn sản phẩm cần sao chép!', type: 'warning');
            return;
        }

        $products = Product::whereIn('id', $this->selectedRows)->get();
        $count = 0;
        $skipped = 0;

        foreach ($products as $product) {
            // Skip if already a SG product
            if (Str::startsWith($product->sku, 'Z')) {
                $skipped++;
                continue;
            }

            $newSku = 'Z' . $product->sku;

            // Check if SKU already exists
            if (Product::where('sku', $newSku)->exists()) {
                $skipped++;
                continue;
            }

            // Create new SG product
            $newProduct = $product->replicate();
            $newProduct->sku = $newSku;
            $newProduct->stock_quantity = 0;
            $newProduct->location = '';
            $newProduct->save();

            $count++;
        }

        $this->selectedRows = [];
        $this->selectAll = false;

        $message = "Đã sao chép thành công {$count} sản phẩm sang SG.";
        if ($skipped > 0) {
            $message .= " (Bỏ qua {$skipped} mã đã tồn tại hoặc không hợp lệ)";
        }

        $this->dispatch('notify', message: $message, type: 'success');
        $this->branch = 'sg'; // Switch to SG branch to see new items
    }

    public function removeImage($index, $type)
    {
        if ($type === 'existing') {
            unset($this->existingImages[$index]);
            $this->existingImages = array_values($this->existingImages);
        } elseif ($type === 'new') {
            unset($this->newImages[$index]);
            $this->newImages = array_values($this->newImages);
        } elseif ($type === 'captured') {
            unset($this->capturedImages[$index]);
            $this->capturedImages = array_values($this->capturedImages);
        }
    }

    public function addCapturedImage($dataUri)
    {
        $this->capturedImages[] = $dataUri;
    }

    private function getNextSku($sku)
    {
        if (empty($sku))
            return '';

        $nextSku = $sku;
        $iteration = 0;

        while ($iteration < 100) { // Safety break
            // Try to match pattern TEXT-NUMBER or just TEXTNUMBER
            if (preg_match('/^(.*?)(\d+)$/', $nextSku, $matches)) {
                $prefix = $matches[1];
                $number = (int) $matches[2];
                $nextNumber = $number + 1;

                // Keep leading zeros if any
                $nextNumberStr = str_pad((string) $nextNumber, strlen($matches[2]), '0', STR_PAD_LEFT);
                $nextSku = $prefix . $nextNumberStr;
            } else {
                // If no trailing number, just add -1
                $nextSku = $nextSku . '-1';
            }

            // Check if this SKU exists
            if (!Product::where('sku', $nextSku)->exists()) {
                return $nextSku;
            }

            $iteration++;
        }

        return $nextSku . '-copy';
    }

    public function confirmDelete($id)
    {
        if (!auth()->user()?->hasPermission('product.delete')) {
            $this->dispatch('notify', message: 'Bạn không có quyền xóa sản phẩm.', type: 'error');
            return;
        }

        $this->productId = $id;
        $this->dispatch('open-delete-modal');
    }

    public function delete()
    {
        if (!auth()->user()?->hasPermission('product.delete')) {
            $this->dispatch('notify', message: 'Bạn không có quyền xóa sản phẩm.', type: 'error');
            return;
        }

        $product = Product::find($this->productId);
        if ($product) {
            foreach ((array) $product->images as $path) {
                if ($path) Storage::disk('public')->delete($path);
            }
            $product->delete();
        }
        $this->dispatch('notify', message: 'Đã xóa sản phẩm!', type: 'success');
        $this->dispatch('close-delete-modal');
        $this->productId = null;
    }

    public function bulkDelete()
    {
        if (!auth()->user()?->hasPermission('product.delete')) {
            $this->dispatch('notify', message: 'Bạn không có quyền xóa sản phẩm.', type: 'error');
            return;
        }

        if (empty($this->selectedRows)) return;

        $products = $this->getModelForBulk()::whereIn('id', $this->selectedRows)->get(['id', 'images']);
        foreach ($products as $product) {
            foreach ((array) $product->images as $path) {
                if ($path) Storage::disk('public')->delete($path);
            }
        }
        $products->each->delete();

        $this->selectedRows = [];
        $this->selectAll = false;

        $this->dispatch('notify', message: 'Đã xóa các mục đã chọn!', type: 'success');
    }

    public function toggleStatus($id)
    {
        $product = Product::findOrFail($id);
        $product->update(['is_active' => !$product->is_active]);
        $this->dispatch('notify', message: 'Cập nhật trạng thái thành công!', type: 'success');
    }

    public function updateField($id, $field, $value)
    {
        $product = Product::findOrFail($id);

        $rules = [
            'sku' => 'required|unique:products,sku,' . $id,
            'base_name' => 'required|min:3',
            'brand' => 'nullable|string|max:255',
            'category_path' => 'nullable|string|max:255',
            'sale_price' => 'required|numeric|min:0',
            'location' => 'nullable|string|max:255',
            'stock_quantity' => 'nullable|numeric|min:0',
        ];

        if (!isset($rules[$field]))
            return;

        try {
            $validator = \Validator::make([$field => $value], [$field => $rules[$field]]);
            if ($validator->fails()) {
                throw new \Exception($validator->errors()->first());
            }

            if ($field === 'stock_quantity') {
                $change = (int) $value - (int) $product->stock_quantity;
                if ($change !== 0) {
                    $product->recordStockHistory(
                        'Adjustment',
                        $change,
                        null,
                        null,
                        'Điều chỉnh thủ công (Sửa nhanh)'
                    );
                }
            }

            $updates = [$field => $value];
            if ($field === 'sale_price' && $this->isAutoCommissionEnabled()) {
                $updates['commission_amount'] = $this->commissionForPrice((int) $value);
            }

            $product->update($updates);
            $this->dispatch('notify', message: 'Cập nhật thành công!', type: 'success');
        } catch (\Exception $e) {
            $this->dispatch('notify', message: 'Lỗi: ' . $e->getMessage(), type: 'error');
            // Force refresh to revert invalid UI state
            $this->dispatch('$refresh');
        }
    }

    public function sortBy($field)
    {
        if (!array_key_exists($field, $this->sortableFields())) {
            return;
        }

        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }

        $this->resetPage();
    }

    protected function sortableFields(): array
    {
        return [
            'created_at' => 'created_at',
            'sku' => 'sku',
            'base_name' => 'base_name',
            'name' => 'name',
            'brand' => 'brand',
            'category_path' => 'category_path',
            'location' => 'location',
            'stock_quantity' => 'stock_quantity',
            'sale_price' => 'sale_price',
        ];
    }

    protected function currentSortField(): string
    {
        return $this->sortableFields()[$this->sortField] ?? 'created_at';
    }

    protected function currentSortDirection(): string
    {
        return in_array($this->sortDirection, ['asc', 'desc'], true) ? $this->sortDirection : 'desc';
    }

    protected function hasManualSort(): bool
    {
        return $this->currentSortField() !== 'created_at' || $this->currentSortDirection() !== 'desc';
    }

    protected function applySort($query): void
    {
        $query->orderBy($this->currentSortField(), $this->currentSortDirection())
            ->orderBy('id', 'desc');
    }

    public function getProducts()
    {
        $hasManualSort = $this->hasManualSort();

        $query = Product::query()
            ->when($this->branch !== 'all', function ($query) {
                if ($this->branch === 'sg') {
                    $query->where('sku', 'LIKE', 'Z%');
                } else {
                    $query->where('sku', 'NOT LIKE', 'Z%');
                }
            })
            ->when($this->search, function ($query) use ($hasManualSort) {
                $keywords = array_filter(explode(' ', $this->search));
                foreach ($keywords as $keyword) {
                    $query->where(function ($q) use ($keyword) {
                        $q->whereRaw("sku REGEXP ?", ['(^|[^0-9])' . $keyword . '([^0-9]|$)'])
                            ->orWhereRaw("location REGEXP ?", ['(^|[^0-9])' . $keyword . '([^0-9]|$)'])
                            ->orWhereRaw("name REGEXP ?", ['(^|[^0-9])' . $keyword . '([^0-9]|$)'])
                            ->orWhereRaw("brand REGEXP ?", ['(^|[^0-9])' . $keyword . '([^0-9]|$)']);
                    });
                }

                if (!$hasManualSort) {
                    $query->orderByRaw("CASE
                        WHEN sku = ? THEN 1
                        WHEN location = ? THEN 2
                        WHEN sku LIKE ? THEN 3
                        WHEN name LIKE ? THEN 4
                        ELSE 5
                    END", [$this->search, $this->search, $this->search . '%', $this->search . '%']);
                }
            })
            ->when($this->selectedCategories, function ($query) {
                $query->whereIn('category_path', $this->selectedCategories);
            })
            ->when($this->boxCode, function ($query) {
                $query->whereRaw("location REGEXP ?", ['(^|[^0-9])' . $this->boxCode . '([^0-9]|$)']);
            })
            ->when($this->brandFilter, function ($query) {
                $query->where('brand', $this->brandFilter);
            })
            ->when($this->stockStatus !== 'all', function ($query) {
                if ($this->stockStatus === 'in_stock')
                    $query->where('stock_quantity', '>', 0);
                if ($this->stockStatus === 'out_of_stock')
                    $query->where('stock_quantity', '<=', 0);
                if ($this->stockStatus === 'low_stock')
                    $query->where('stock_quantity', '<', 10)->where('stock_quantity', '>', 0);
            });

        $this->applySort($query);

        return $query->paginate($this->perPage)->onEachSide(1);
    }

    protected function getRecordsForBulk()
    {
        return $this->getProducts();
    }

    protected function getModelForBulk()
    {
        return Product::class;
    }

    public function render()
    {
        return view('livewire.product.product-index', [
            'products' => $this->getProducts(),
            'categories_list' => Product::whereNotNull('category_path')->distinct()->pluck('category_path'),
            'brands_list' => Product::whereNotNull('brand')->distinct()->pluck('brand'),
            'box_codes_list' => Product::whereNotNull('location')->distinct()->pluck('location'),
        ])->layout('layouts.app');
    }
}
