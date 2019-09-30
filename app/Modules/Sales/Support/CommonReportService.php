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
            $orders = $orders->whereDate('order_date', '>=', $input['date_range']['start_date'])
                             ->whereDate('order_date', '<=', $input['date_range']['end_date']);
        }


        switch ($input['group']) {

            case 'daily':
                $orders = $orders->selectRaw('order_date as start_date,order_date as end_date')
                                 ->groupBy('order_date')->get()->keyBy('end_date');

                return $this->generateDailyDateRange($orders, $from, $to, $field);

            case 'monthly':
//                $orders = $orders->selectRaw('MONTH(order_date) as month,DATE_SUB(LAST_DAY(order_date),INTERVAL DAY(LAST_DAY(order_date))- 1 DAY) AS start_date,LAST_DAY(order_date) AS end_date')
//                                 ->groupBy('month')->get();
                $orders = $orders->selectRaw('MONTH(order_date) as month')
                                 ->groupBy('month')->get()->keyBy('month');

                return $this->generateMonthDateRange($orders, $from, $to, $field);
                break;

            case 'weekly':
                $orders = $orders->selectRaw('WEEK(order_date) as week,DATE(order_date + INTERVAL ( - WEEKDAY(order_date)) DAY) as start_date,DATE(order_date + INTERVAL (6 - WEEKDAY(order_date)) DAY) as end_date')
                                 ->groupBy('week');
                break;

            case 'yearly':
                $orders = $orders->selectRaw('DATE_FORMAT(order_date ,"%Y-01-01") as start_date,DATE_FORMAT(order_date ,"%Y-12-31") as end_date');
                break;
        }

        return $orders;
    }


//    private function generateDateRange(Carbon $start_date, Carbon $end_date) {
//        $dates = [];
//        for ($date = $start_date->startOfYear(); $date->lte($end_date); $date->addYear()) {
//            $dates[] = $date->format('Y-m-d');
//        }
//
//        return $dates;
//
//    }

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
                $dates[] = $this->formatDateResponse($order->start_date, $order->end_date,
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
     * @return array
     */
    private function generateMonthDateRange($orders, Carbon $from, Carbon $to, $quantityType) {

        $months = [];
        for ($month = $from; $month->lte($to); $month->startOfMonth()->addMonth()) {
            $monthInt = $month->month;

            $startDate = $month->format('Y-m-d');
            $endDate = $month->endOfMonth();

            if ($month->endOfMonth()->gte($to)) {
                $endDate = $to;
            }

            if (isset($orders[$monthInt])) {
                $order = $orders[$monthInt];
                $months[] = $this->formatDateResponse($startDate,
                    $endDate->format('Y-m-d'),
                    $order->total_orders, $order->{$quantityType}, $quantityType);
            } else {
                $months[] = $this->formatDateResponse($startDate,
                    $endDate->format('Y-m-d'),
                    0, 0, $quantityType);
            }
        }

        return $months;
    }


//    private function generateDateRange(Carbon $start_date, Carbon $end_date) {
//        $startDate = Carbon::parse($start_date)->subDays($start_date->weekday());
//        $dates = [];
//        for ($date = $startDate; $date->lte($end_date->endOfWeek()); $date->addWeek()) {
//            $dates[] = $date->format('Y-m-d');
//        }
//        return $dates;
//    }
}
