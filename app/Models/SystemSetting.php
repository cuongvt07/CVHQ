<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class SystemSetting extends Model
{
    protected $fillable = ['key', 'value', 'description'];

    /**
     * URL logo hệ thống đã resolve sang /storage/... (null nếu chưa cấu hình).
     */
    public static function logoUrl(): ?string
    {
        $path = self::get('app_logo');
        if (empty($path)) {
            return null;
        }
        if (Str::startsWith($path, ['http://', 'https://', '/'])) {
            return $path;
        }
        return asset('storage/' . ltrim($path, '/'));
    }

    public static function get($key, $default = null)
    {
        $setting = self::where('key', $key)->first();
        if (!$setting) return $default;

        $value = $setting->value;
        
        // Try to decode JSON
        $decoded = json_decode($value, true);
        return (json_last_error() === JSON_ERROR_NONE) ? $decoded : $value;
    }

    public static function set($key, $value, $description = null)
    {
        if (is_array($value) || is_object($value)) {
            $value = json_encode($value);
        }

        return self::updateOrCreate(
            ['key' => $key],
            ['value' => (string)$value, 'description' => $description]
        );
    }
}
