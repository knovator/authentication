<?php


namespace App\Modules\Sales\Support;

use App\Constants\Master as MasterConstant;
use App\Modules\Sales\Models\SalesOrder;
use Illuminate\Database\Eloquent\Builder;
use Barryvdh\Snappy\Facades\SnappyPdf;
use Knovators\Masters\Repository\MasterRepository;


/**
 * Trait ExportSaleOrderSummary
 * @package App\Modules\Sales\Support
 */
trait ExportSaleOrderSummary
{
    /**
     * @param SalesOrder $salesOrder
     * @param            $masterRepository
     * @return
     */
    public function renderSummary(SalesOrder &$salesOrder, MasterRepository $masterRepository) {
        $statusId = $masterRepository->findByCode(MasterConstant::SO_DELIVERED)->id;
        $salesOrder->load([
            'orderRecipes'               => function ($orderRecipes) {
                /** @var Builder $orderRecipes */
                $orderRecipes->orderBy('id')->with([
                    'recipe.fiddles' => function ($fiddles) {
                        /** @var Builder $fiddles */
                        $fiddles->where('recipes_fiddles.fiddle_no', '=', 1)->with('color');
                    }
                ]);
            },
            'orderRecipes.partialOrders' => function ($partialOrders) use ($statusId) {
                /** @var Builder $partialOrders */
                $partialOrders->whereHas('delivery', function ($delivery) use ($statusId) {
                    /** @var Builder $delivery */
                    $delivery->where('status_id', $statusId);
                });
            },
            'manufacturingCompany',
            'design.detail',
            'design.mainImage.file',
            'customer.state'
        ]);

        $isInvoice = false;
        if ($salesOrder->deliveries()->where('status_id', $statusId)->exists()) {
            $isInvoice = true;
        }


        return SnappyPdf::loadView('receipts.sales-orders.main_summary.summary',
            compact('salesOrder', 'isInvoice'));
    }
}
