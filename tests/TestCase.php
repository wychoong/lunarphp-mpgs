<?php

namespace WyChoong\Mpgs\Tests;

use Cartalyst\Converter\Laravel\ConverterServiceProvider;
use Illuminate\Database\Eloquent\Factories\Factory;
use Livewire\LivewireServiceProvider;
use Lunar\LunarServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;
use WyChoong\Mpgs\MpgsPaymentServiceProvider;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        Factory::guessFactoryNamesUsing(
            fn (string $modelName) => 'WyChoong\\Mpgs\\Database\\Factories\\'.class_basename($modelName).'Factory'
        );
    }

    protected function getPackageProviders($app)
    {
        return [
            LivewireServiceProvider::class,
            LunarServiceProvider::class,
            ConverterServiceProvider::class,
            MpgsPaymentServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        config()->set('database.default', 'testing');

        /*
        $migration = include __DIR__.'/../database/migrations/create_lunarphp-mpgs_table.php.stub';
        $migration->up();
        */
    }
}
