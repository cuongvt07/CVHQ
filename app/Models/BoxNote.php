<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// Thông tin ghi chú cho từng thùng (nơi để, đặc điểm...). code = mã thùng như D08.
class BoxNote extends Model
{
    protected $fillable = ['code', 'note'];
}
