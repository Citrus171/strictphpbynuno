<?php

declare(strict_types=1);

namespace App\Providers;

use App\Shipping\FlatRateShipping;
use Illuminate\Support\ServiceProvider;
use Lunar\Admin\Support\Facades\LunarPanel;
use Lunar\Base\ShippingModifiers;
use Lunar\Facades\Telemetry;

final class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        LunarPanel::register();
    }

    public function boot(): void
    {
        Telemetry::optOut();

        resolve(ShippingModifiers::class)->add(FlatRateShipping::class);
    }
}
