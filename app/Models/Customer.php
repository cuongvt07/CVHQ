<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'customer_code', 'full_name', 'phone', 'email', 'address', 'ward',
        'district', 'delivery_area', 'customer_type', 'company', 'tax_code', 'identity_number',
        'birthday', 'gender', 'facebook', 'customer_group', 'note',
        'created_by', 'branch_created', 'last_transaction_at', 'current_debt',
        'total_spent', 'total_spent_net', 'status'
    ];

    protected $casts = [
        'birthday' => 'date',
        'last_transaction_at' => 'datetime',
        'current_debt' => 'integer',
        'total_spent' => 'integer',
        'total_spent_net' => 'integer',
    ];

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }
}
