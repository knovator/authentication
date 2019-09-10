<?php

namespace App\Jobs;

use App\Modules\Sales\Models\SalesOrder;
use App\Modules\Yarn\Models\YarnOrder;
use App\Modules\Yarn\Support\ExportYarnOrderSummary;
use App\Repositories\MasterRepository;
use Barryvdh\Snappy\PdfWrapper;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Class YarnOrderFormJob
 * @package App\Jobs
 */
class YarnOrderFormJob implements ShouldQueue
{

    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, ExportYarnOrderSummary;

    private $yarnOrder;

    /**
     * Create a new job instance.
     *
     * @param $yarnOrder
     */
    public function __construct(YarnOrder $yarnOrder) {
        $this->yarnOrder = $yarnOrder;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle() {
        $pdf = $this->renderSummary($this->yarnOrder);
        $fileUri = $this->getFileUri();
        /** @var PdfWrapper $pdf */
        $pdf->save(public_path() . $fileUri);

        if (!is_null($this->yarnOrder->manufacturingCompany)) {
            $companyName = $this->yarnOrder->manufacturingCompany->name;
        } else {
            $companyName = 'JENNY TEXO FAB';
        }
        $this->yarnOrder->customer->sendOrderNotifyMail($companyName,
            config('app.url') . $fileUri,'yarn');
    }

    /**
     * @return string
     */
    private function getFileUri() {
        return '/uploads/order-forms/' . time() . '-' . $this->yarnOrder->order_no . '.pdf';
    }
}
