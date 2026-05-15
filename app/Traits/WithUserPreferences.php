<?php

namespace App\Traits;

trait WithUserPreferences
{
    public function initializeWithUserPreferences()
    {
        if (auth()->check()) {
            $settings = auth()->user()->ui_settings[$this->getModuleKey()] ?? [];
            
            foreach ($this->getPersistedProperties() as $property) {
                if (isset($settings[$property])) {
                    $this->{$property} = $settings[$property];
                }
            }
        }
    }

    public function updated($name, $value)
    {
        if (in_array($name, $this->getPersistedProperties()) && auth()->check()) {
            $user = auth()->user();
            $settings = $user->ui_settings ?? [];
            $settings[$this->getModuleKey()][$name] = $value;
            $user->update(['ui_settings' => $settings]);
        }
    }

    abstract protected function getModuleKey(): string;
    
    protected function getPersistedProperties(): array
    {
        return ['perPage'];
    }
}
