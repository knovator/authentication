<?php

namespace App\Modules\Report\Http\Exports;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Exception;
use PhpOffice\PhpSpreadsheet\Style\Fill;

/**
 * Class CustomerExport
 * @package App\Modules\Report\Http\Exports
 */
class CustomerExport implements FromView, ShouldAutoSize, WithEvents
{

    protected $orders;


    /**
     * CustomerExport constructor.
     * @param Collection $orders
     */
    public function __construct(Collection $orders) {
        $this->orders = $orders;
    }

    /**
     * @return View
     */
    public function view() : View {
        return view('exports.top_customers', [
            'orders' => $this->orders
        ]);
    }

    /**
     * @return array
     */
    public function registerEvents() : array {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $this->createStyle($event, 'A1:E1', 12);
                $event->sheet->styleCells(
                    'A1:E1',
                    [
                        'font' => [
                            'bold' => true,
                        ]
                    ]
                );
                $event->sheet->getStyle('A1:E1')->getFill()
                             ->setFillType(Fill::FILL_SOLID)
                             ->getStartColor()->setARGB('FFEFD5');
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
