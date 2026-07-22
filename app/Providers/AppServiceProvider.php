<?php

namespace App\Providers;

use App\Models\Company;
use App\Models\Passenger;
use App\Models\Voucher;
use App\Observers\PassengerObserver;
use App\Observers\VoucherObserver;
use App\Services\BookingImportService;
use App\Services\DoubleEntryService;
use App\Services\PackageCalculationService;
use Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider;
use Carbon\CarbonInterval;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Laravel\Cashier\Cashier;
use Laravel\Sanctum\Sanctum;

class AppServiceProvider extends ServiceProvider
{

    /**
     * Register any application services.
     */
    public function register(): void
    {
        Cashier::ignoreMigrations();
        Sanctum::ignoreMigrations();

        if (config('app.redirect_https')) {
            $this->app['request']->server->set('HTTPS', true);
        }

        // Bind travel agency services as singletons
        $this->app->singleton(DoubleEntryService::class);
        $this->app->singleton(BookingImportService::class);
        $this->app->singleton(PackageCalculationService::class);
    }

    public function boot(): void
    {
        Cashier::useCustomerModel(Company::class);

        if (config('app.redirect_https')) {
            URL::forceScheme('https');
        }

        Schema::defaultStringLength(191);

        if (app()->environment('development')) {
            $this->app->register(IdeHelperServiceProvider::class);
        }

        // ---------------------------------------------------------------
        // Register Model Observers (Travel Agency)
        // ---------------------------------------------------------------
        Voucher::observe(VoucherObserver::class);
        Passenger::observe(PassengerObserver::class);

        CarbonInterval::macro('formatHuman', function ($totalMinutes, $seconds = false): string {

            if ($seconds) {
                return static::seconds($totalMinutes)->cascade()->forHumans(['short' => true, 'options' => 0]);
                /** @phpstan-ignore-line */
            }

            return static::minutes($totalMinutes)->cascade()->forHumans(['short' => true, 'options' => 0]);
            /** @phpstan-ignore-line */
        });

        //    Model::preventLazyLoading(app()->environment('development'));
    }

}
