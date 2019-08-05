<?php

namespace App\Providers;

use App\Modules\Purchase\Models\PurchaseOrder;
use App\Modules\Sales\Models\SalesOrder;
use App\Modules\Thread\Models\ThreadColor;
use App\Modules\Yarn\Models\YarnOrder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register() {
        if ($this->app->environment() !== 'production') {
            $this->app->register(\Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider::class);
        }


        Relation::morphMap([
            'yarn'         => YarnOrder::class,
            'purchase'     => PurchaseOrder::class,
            'sales'        => SalesOrder::class,
            'thread_color' => ThreadColor::class,
        ]);
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot() {
        //
    }
}
