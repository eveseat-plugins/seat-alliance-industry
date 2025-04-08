<?php

namespace RecursiveTree\Seat\AllianceIndustry;

use RecursiveTree\Seat\AllianceIndustry\Jobs\RemoveExpiredDeliveries;
use RecursiveTree\Seat\AllianceIndustry\Jobs\SendOrderNotifications;
use RecursiveTree\Seat\AllianceIndustry\Jobs\UpdateRepeatingOrders;
use RecursiveTree\Seat\AllianceIndustry\Models\Delivery;
use RecursiveTree\Seat\AllianceIndustry\Models\Order;
use RecursiveTree\Seat\AllianceIndustry\Observers\DeliveryObserver;
use RecursiveTree\Seat\AllianceIndustry\Observers\OrderObserver;
use RecursiveTree\Seat\AllianceIndustry\Observers\UserObserver;
use RecursiveTree\Seat\AllianceIndustry\Policies\UserPolicy;
use Seat\Services\AbstractSeatPlugin;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Artisan;
use Seat\Web\Models\User;

class AllianceIndustryServiceProvider extends AbstractSeatPlugin
{
    public function boot(){
        AllianceIndustrySettings::init();

        if (!$this->app->routesAreCached()) {
            include __DIR__ . '/Http/routes.php';
        }

        $this->loadTranslationsFrom(__DIR__ . '/resources/lang/', 'allianceindustry');
        $this->loadViewsFrom(__DIR__ . '/resources/views/', 'allianceindustry');
        $this->loadMigrationsFrom(__DIR__ . '/database/migrations/');

        Gate::define('allianceindustry.same-user', UserPolicy::class.'@checkUser');

        Delivery::observe(DeliveryObserver::class);
        Order::observe(OrderObserver::class);
        User::observe(UserObserver::class);

        Artisan::command('allianceindustry:notifications {--sync}', function () {
            if ($this->option("sync")){
                $this->info("processing...");
                SendOrderNotifications::dispatchSync();
                $this->info("Synchronously sent notification!");
            } else {
                SendOrderNotifications::dispatch()->onQueue('notifications');
                $this->info("Scheduled notifications!");
            }
        });

        $this->mergeConfigFrom(
            __DIR__ . '/Config/notifications.alerts.php', 'notifications.alerts'
        );

        $this->mergeConfigFrom(
            __DIR__ . '/Config/inventory.sources.php', 'inventory.sources'
        );
        $this->mergeConfigFrom(__DIR__ . '/Config/priceproviders.backends.php','priceproviders.backends');

        $this->mergeConfigFrom(__DIR__ . '/Config/allianceindustry.sde.tables.php','seat.sde.tables');
        $this->mergeConfigFrom(__DIR__ . '/Config/fitting.exportlinks.php','fitting.exportlinks');


        Artisan::command('allianceindustry:orders:repeating {--sync}', function () {
            if($this->option("sync")){
                UpdateRepeatingOrders::dispatchSync();
            } else {
                UpdateRepeatingOrders::dispatch();
            }
        });

        Artisan::command('allianceindustry:deliveries:expired {--sync}', function () {
            if($this->option("sync")){
                RemoveExpiredDeliveries::dispatchSync();
            } else {
                RemoveExpiredDeliveries::dispatch();
            }
        });
    }

    public function register(){
        $this->mergeConfigFrom(__DIR__ . '/Config/allianceindustry.sidebar.php','package.sidebar');
        $this->registerPermissions(__DIR__ . '/Config/allianceindustry.permissions.php', 'allianceindustry');
    }

    public function getName(): string
    {
        return 'SeAT Alliance Industry Operations Planner';
    }

    public function getPackageRepositoryUrl(): string
    {
        return 'https://github.com/recursivetree/seat-alliance-industry';
    }

    public function getPackagistPackageName(): string
    {
        return 'seat-alliance-industry';
    }

    public function getPackagistVendorName(): string
    {
        return 'recursivetree';
    }
}