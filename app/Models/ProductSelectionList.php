<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductSelectionList extends Model
{
    protected $fillable = ['user_id', 'name', 'product_ids'];

    protected $casts = [
        'product_ids' => 'array',
    ];

    public function ids(): array
    {
        return array_values(array_unique(array_map('intval', $this->product_ids ?? [])));
    }
}
