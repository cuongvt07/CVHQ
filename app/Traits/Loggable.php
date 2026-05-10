<?php

namespace App\Traits;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

trait Loggable
{
    public static function bootLoggable()
    {
        static::created(function ($model) {
            $model->logActivity('created');
        });

        static::updated(function ($model) {
            $changes = [
                'before' => array_intersect_key($model->getOriginal(), $model->getChanges()),
                'after' => $model->getChanges(),
            ];
            $model->logActivity('updated', $changes);
        });

        static::deleted(function ($model) {
            $model->logActivity('deleted');
        });
    }

    protected function logActivity($action, $changes = null)
    {
        if (!Auth::check()) return;

        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => $action,
            'model_type' => get_class($this),
            'model_id' => $this->id,
            'changes' => $changes,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
        ]);
    }
}
