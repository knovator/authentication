<?php

namespace App\Modules\Customer\Http\Exports;

use App\Modules\Customer\Models\Customer;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Exception;
use PhpOffice\PhpSpreadsheet\Style\Fill;

/**
 * Class Ledger
 * @package App\Exports
 */
class Ledger implements FromView, ShouldAutoSize, WithEvents
{

    protected $orders;

    protected $orderType;

    protected $customer;

    /**
     * ExportAppliedCandidates constructor.
     * @param Collection $orders
     * @param Customer   $customer
     * @param string     $orderType
     */
    public function __construct(Collection $orders, Customer $customer, string $orderType) {
        $this->orders = $orders;
        $this->orderType = $orderType;
        $this->customer = $customer;
    }

    /**
     * @return View
     */
    public function view() : View {
        return view('exports.ledger_orders', [
            'orders'    => $this->orders,
            'orderType' => $this->orderType,
            'customer'  => $this->customer,
        ]);
    }

    /**
     * @return array
     */
    public function registerEvents() : array {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $this->createStyle($event, 'A7:E7', 12);
                $event->sheet->styleCells(
                    'A7:E7',
                    [
                        'font' => [
                            'bold' => true,
                        ]
                    ]
                );
                $event->sheet->getStyle('A7:E7')->getFill()
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
