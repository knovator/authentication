<?php

namespace App\Jobs;

use App\Modules\Sales\Models\SalesOrder;
use App\Modules\Sales\Support\ExportSaleOrderSummary;
use App\Repositories\MasterRepository;
use Barryvdh\Snappy\PdfWrapper;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

/**
 * Class OrderFormJob
 * @package App\Jobs
 */
class OrderFormJob implements ShouldQueue
{

    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, ExportSaleOrderSummary;

    private $salesOrder;

    /**
     * Create a new job instance.
     *
     * @param $salesOrder
     */
    public function __construct(SalesOrder $salesOrder) {
        $this->salesOrder = $salesOrder;
    }

    /**
     * Execute the job.
     *
     * @param MasterRepository $masterRepository
     * @return void
     */
    public function handle(MasterRepository $masterRepository) {
        $pdf = $this->renderSummary($this->salesOrder, $masterRepository);
        $fileUri = '/uploads/order-forms/' . time() . '-' . $this->salesOrder->order_no . '.pdf';
        /** @var PdfWrapper $pdf */
        $pdf->save(public_path() . $fileUri);
        $this->salesOrder->customer->sendOrderNotifyMail($this->salesOrder->fresh('manufacturingCompany'),
            config('app.url') .
            $fileUri);
        dd($pdf);
    }
}
