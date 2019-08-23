<?php


namespace App\Modules\Yarn\Support;

use App\Constants\Master as MasterConstant;
use App\Modules\Yarn\Models\YarnOrder;
use Illuminate\Database\Eloquent\Builder;
use Barryvdh\Snappy\Facades\SnappyPdf;
use Knovators\Masters\Repository\MasterRepository;


/**
 * Trait ExportYarnOrderSummary
 * @package App\Modules\Yarn\Support
 */
trait ExportYarnOrderSummary
{

    /**
     * @param YarnOrder $yarnOrder
     * @return
     */
    public function renderSummary(YarnOrder &$yarnOrder) {
        $yarnOrder->load([
            'threads.threadColor.thread:id,name,denier,company_name',
            'threads.threadColor.color:id,name,code',
            'customer.state:id,name,code,gst_code',
        ]);

        return SnappyPdf::loadView('receipts.yarn-orders.main_summary.summary',
            compact('salesOrder', 'isInvoice'));
    }
}
