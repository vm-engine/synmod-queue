<?php

declare(strict_types=1);

namespace VmEngine\Queue;

use Illuminate\Support\ServiceProvider;
use VmEngine\Synapse\Traits\AutoRegistersComponents;

class QueueServiceProvider extends ServiceProvider
{
    use AutoRegistersComponents;

    public function register(): void
    {
        $this->registerComponents();
    }

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'queue');
        $this->loadTranslationsFrom(__DIR__.'/../lang', 'queue');
    }
}
