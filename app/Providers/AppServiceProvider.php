<?php

namespace App\Providers;

use App\Modules\Purchase\Models\PurchaseOrder;
use App\Modules\Thread\Models\ThreadColor;
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
            'purchase'        => PurchaseOrder::class,
            'thread_color' => ThreadColor::class
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
