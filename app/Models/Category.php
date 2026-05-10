<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

use App\Traits\Loggable;

class Category extends Model
{
    use Loggable;
    use SoftDeletes;
    protected $fillable = ['name', 'slug'];

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}
