<?php

namespace Revolution\Ordering\Providers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;
use Revolution\Ordering\Console\InstallCommand;
use Revolution\Ordering\Contracts\Auth\OrderingGuard;
use Revolution\Ordering\Providers\Concerns\WithBindings;
use Revolution\Ordering\Providers\Concerns\WithGoogleSheets;
use Revolution\Ordering\Providers\Concerns\WithLivewire;
use Revolution\Ordering\Providers\Concerns\WithRoutes;
use Revolution\Ordering\View\Components\DashboardLayout;
use Revolution\Ordering\View\Components\AppLayout;

class OrderingServiceProvider extends ServiceProvider
{
    use WithBindings;
    use WithGoogleSheets;
    use WithLivewire;
    use WithRoutes;

    public function register()
    {
        $this->registerBindings();

        $this->registerGoogle();

        $this->registerLivewire();

        config([
            'auth.guards.ordering' => array_merge([
                'driver'   => 'ordering',
                'provider' => null,
            ], config('auth.guards.ordering', [])),
        ]);

        if (! $this->app->configurationIsCached()) {
            $this->mergeConfigFrom(__DIR__.'/../../config/ordering.php', 'ordering');
        }
    }

    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../../config/ordering.php' => config_path('ordering.php'),
            ], 'ordering-config');

            $this->publishes([
                __DIR__.'/../../resources/views' => resource_path('views/vendor/ordering'),
            ], 'ordering-views');

            $this->commands([
                InstallCommand::class,
            ]);
        }

        $this->configureView();

        $this->configureAuth();

        $this->configureRoutes();
    }

    protected function configureView()
    {
        $this->loadViewsFrom(__DIR__.'/../../resources/views', 'ordering');

        $this->loadViewComponentsAs('ordering', [
            DashboardLayout::class,
            AppLayout::class,
        ]);
    }

    protected function configureAuth()
    {
        Auth::viaRequest('ordering', $this->app->make(OrderingGuard::class));
    }
}
