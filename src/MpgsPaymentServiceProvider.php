<?php

namespace WyChoong\Mpgs;

use Illuminate\Support\Facades\Blade;
use Livewire\Livewire;
use Lunar\Facades\Payments;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use WyChoong\Mpgs\Components\PaymentForm;
use WyChoong\Mpgs\Contracts\LunarMpgsInterface;
use WyChoong\Mpgs\Managers\MpgsManager;

class MpgsPaymentServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('lunar-mpgs')
            ->hasConfigFile()
            ->hasViews();
    }

    public function packageBooted()
    {
        Payments::extend('mpgs', function ($app) {
            return $app->make(MpgsPaymentType::class);
        });

        $this->app->singleton(LunarMpgsInterface::class, function ($app) {
            return new MpgsManager();
        });

        Livewire::component('mpgs.payment', PaymentForm::class);

        Blade::directive('mpgsScripts', function () {
            $jsUrl = config('lunar-mpgs.checkout_js');

            return <<<EOT
                <script src="$jsUrl"
                    data-error="errorCallback"
                    data-cancel="cancelCallback"
                    data-complete="completeCallback">
                </script>
                <script type="text/javascript">
                    const et = new EventTarget();
                    function errorCallback(error) {
                        et.dispatchEvent(new Event('mpgs-error'));
                    }
                    function cancelCallback() {
                        et.dispatchEvent(new Event('mpgs-cancel'));
                    }
                    function completeCallback() {
                        et.dispatchEvent(new Event('mpgs-complete'));
                    }
                </script>
            EOT;
        });
    }
}
