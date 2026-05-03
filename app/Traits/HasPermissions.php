<?php

namespace App\Traits;

trait HasPermissions
{
    /**
     * Module key for permission checking
     */
    abstract protected function getModuleKey(): string;

    public function mountHasPermissions()
    {
        if (!auth()->check()) {
            return redirect('/');
        }

        if (!auth()->user()->hasPermission($this->getModuleKey())) {
            session()->flash('notify', [
                'message' => 'Bạn không có quyền truy cập module này!',
                'type' => 'error'
            ]);

            // Redirect to a safe place (Dashboard or POS if they have access)
            if (auth()->user()->hasPermission('pos')) {
                return redirect()->route('pos');
            }

            return redirect()->route('dashboard');
        }
    }
}
