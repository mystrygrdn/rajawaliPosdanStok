<?php

namespace App\Providers;

use Native\Desktop\Facades\Window;
use Native\Desktop\Contracts\ProvidesPhpIni;

class NativeAppServiceProvider implements ProvidesPhpIni
{
    /**
     * Executed once the native application has been booted.
     * Use this method to open windows, register global shortcuts, etc.
     */
    public function boot(): void
    {
        Window::open()
            ->title('Rajawali Stok — POS & Inventory')
            ->width(1366)
            ->height(800)
            ->minWidth(1024)
            ->minHeight(700);
    }

    /**
     * Return an array of php.ini directives to be set.
     */
    public function phpIni(): array
    {
        return [
            'memory_limit'       => '512M',
            'max_execution_time' => '0',
            'max_input_time'     => '0',
            'display_errors'     => '0',
        ];
    }
}