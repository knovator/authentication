<?php

namespace App\Modules\Sales\Http\Exports;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Exception;

/**
 * Class App\Modules\Sales\Http\Exports
 * @package App\Exports
 */
class SalesOrder implements FromView, ShouldAutoSize, WithEvents
{

    protected $orders;

    /**
     * ExportAppliedCandidates constructor.
     * @param Collection $orders
     */
    public function __construct(Collection $orders) {
        $this->orders = $orders;
    }

    /**
     * @return View
     */
    public function view() : View {
        return view('exports.sales_orders', [
            'orders' => $this->orders,
        ]);
    }

    /**
     * @return array
     */
    public function registerEvents() : array {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $this->createStyle($event, 'A1:G1', 12);
                $event->sheet->styleCells(
                    'A1:G1',
                    [
                        'font' => [
                            'bold' => true,
                        ]
                    ]
                );
            },
        ];
    }

    /**
     * @param $event
     * @param $cell
     * @param $size
     * @throws Exception
     */
    private function createStyle($event, $cell, $size) {
        /** @var AfterSheet $event */
        $event->sheet->getDelegate()->getStyle($cell)->getFont()->setSize($size);
    }
}
