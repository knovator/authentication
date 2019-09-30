<?php

namespace App\Modules\Purchase\Http\Exports;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Exception;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

/**
 * Class App\Modules\Purchase\Http\Exports
 * @package App\Exports
 */
class PurchaseOrder implements FromView, ShouldAutoSize, WithEvents
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
        return view('exports.purchase_orders', [
            'purchaseOrders' => $this->orders,
        ]);
    }

    /**
     * @return array
     */
    public function registerEvents() : array {
        $subBorderStyle = [
            'borders' => [
                'outline' => [
                    'borderStyle' => Border::BORDER_HAIR,
                    'color'       => ['argb' => '#32CD32'],
                ],
            ],
        ];

        return [
            AfterSheet::class => function (AfterSheet $event) use ($subBorderStyle) {
                $this->setBorderOnCell($event, $subBorderStyle);
                $this->createStyle($event, 'A1:G1', 11);
                $event->sheet->styleCells(
                    'A1:G1',
                    [
                        'font' => [
                            'bold' => true,
                        ]
                    ]
                );
                $event->sheet->getStyle('A1:G1')->getFill()
                             ->setFillType(Fill::FILL_SOLID)
                             ->getStartColor()->setARGB('FFFF00');
            },
        ];
    }

    /**
     * @param $event
     * @param $subBorderStyle
     */
    private function setBorderOnCell($event, $subBorderStyle) {
        $mainStart = 2;
        foreach ($this->orders as $orderKey => $order) {
            $threads = count($order->threads);
            $mainStart += $threads;
            $mainEnd = $mainStart + $order->partial_orders_count - 1;
            if ($order->deliveries_count) {
                $event->sheet->getDelegate()->getStyle("E{$mainStart}:G{$mainEnd}")
                             ->applyFromArray($subBorderStyle);

            }
            $mainStart = $mainEnd + 2;
        }
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
