<?php

namespace App\Providers;

use App\Modules\Purchase\Models\PurchaseOrder;
use App\Modules\Purchase\Models\PurchasePartialOrder;
use App\Modules\Sales\Models\RecipePartialOrder;
use App\Modules\Sales\Models\SalesOrder;
use App\Modules\Thread\Models\ThreadColor;
use App\Modules\Wastage\Models\WastageOrder;
use App\Modules\Yarn\Models\YarnOrder;
use Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider;
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
            $this->app->register(IdeHelperServiceProvider::class);
        }


        Relation::morphMap([
            'yarn'             => YarnOrder::class,
            'purchase'         => PurchaseOrder::class,
            'sales'            => SalesOrder::class,
            'wastage'          => WastageOrder::class,
            'thread_color'     => ThreadColor::class,
            'purchase_partial' => PurchasePartialOrder::class,
            'sales_partial'    => RecipePartialOrder::class,
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
