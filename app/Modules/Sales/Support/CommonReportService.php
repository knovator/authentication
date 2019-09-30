<?php

namespace App\Modules\Sales\Support;


use App\Modules\Report\Http\Resources\Overview;
use App\Modules\Sales\Models\SalesOrder;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * Trait CommonReportService
 * @package App\Modules\Sales\Support
 */
trait CommonReportService
{


    /**
     * @param $input
     * @param $field
     * @return array
     */
    public function getReportList($input, $field) {
        $from = Carbon::parse($input['date_range']['start_date']);
        $to = Carbon::parse($input['date_range']['end_date']);

        $orders = $this->model->selectRaw("YEAR(order_date) AS year,SUM({$field}) AS {$field},COUNT(*) AS total_orders")
                              ->groupBy('year');

        if (isset($input['date_range'])) {
            $orders = $orders->whereDate('order_date', '>=', $from)
                             ->whereDate('order_date', '<=', $to);
        }


        switch ($input['group']) {

            case 'daily':
                $orders = $orders->selectRaw('order_date as end_date')
                                 ->groupBy('order_date')->get()->keyBy('end_date');

                return $this->generateDailyDateRange($orders, $from, $to, $field);

            case 'monthly':
                $orders = $orders->selectRaw('MONTH(order_date) as month')
                                 ->groupBy('month')->get()->keyBy('month');

                return $this->generateDateRange($orders, $from, $to, $field, 'month');

            case 'weekly':
                $orders = $orders->selectRaw('WEEK(order_date) as week')
                                 ->groupBy('week')->get()->keyBy('week');

                return $this->generateDateRange($orders, $from, $to, $field, 'week');
                break;

            default:
                return $this->generateDateRange($orders->get()->keyBy('year'), $from, $to,
                    $field, 'year');
        }


    }

    /**
     * @param        $orders
     * @param Carbon $from
     * @param Carbon $to
     * @param        $quantityType
     * @return array
     */
    private function generateDailyDateRange($orders, Carbon $from, Carbon $to, $quantityType) {
        $dates = [];
        for ($date = $from; $date->lte($to); $date->addDay()) {
            $newDate = $date->format('Y-m-d');
            if (isset($orders[$newDate])) {
                $order = $orders[$newDate];
                $dates[] = $this->formatDateResponse($newDate, $newDate,
                    $order->total_orders, $order->{$quantityType}, $quantityType);
            } else {
                $dates[] = $this->formatDateResponse($newDate, $newDate,
                    0, 0, $quantityType);
            }

        }

        return $dates;

    }

    /**
     * @param $startDate
     * @param $endDate
     * @param $totalOrders
     * @param $totalQuantity
     * @param $quantityType
     * @return array
     */
    private function formatDateResponse(
        $startDate,
        $endDate,
        $totalOrders,
        $totalQuantity,
        $quantityType
    ) {
        return [
            [
                'start_date'  => $startDate,
                'end_date'    => $endDate,
                $quantityType => $totalQuantity
            ],
            $totalOrders
        ];
    }


    /**
     * @param        $orders
     * @param Carbon $from
     * @param Carbon $to
     * @param        $quantityType
     * @param        $type
     * @return array
     */
    private function generateDateRange(
        $orders,
        Carbon $from,
        Carbon $to,
        $quantityType,
        $type
    ) {

        $dates = [];
        for ($date = $from; $date->lte($to); $from->{'startOf' . $type}()->{'add' . $type}()) {
            $dateInt = $date->{$type};
            $startDate = $date->format('Y-m-d');
            $endDate = $date->{'endOf' . $type}();

            if ($endDate->gt($to)) {
                $endDate = $to;
            }

            if (isset($orders[$dateInt])) {
                $order = $orders[$dateInt];
                $dates[] = $this->formatDateResponse($startDate,
                    $endDate->format('Y-m-d'),
                    $order->total_orders, $order->{$quantityType}, $quantityType);
            } else {
                $dates[] = $this->formatDateResponse($startDate,
                    $endDate->format('Y-m-d'),
                    0, 0, $quantityType);
            }
        }

        return $dates;
    }
}
