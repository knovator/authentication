<?php

namespace App\Modules\Sales\Support;


use Carbon\Carbon;

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

        $orders = $this->model->selectRaw("YEAR(order_date) AS year,SUM({$field}) AS {$field},COUNT(*) AS total_orders")
                              ->groupBy('year');

        if (isset($input['date_range'])) {
            $orders = $orders->whereDate('order_date', '>=', $input['date_range']['start_date'])
                             ->whereDate('order_date', '<=', $input['date_range']['end_date']);
        }

        switch ($input['group']) {

            case 'daily':
                return $orders->selectRaw('DATE_FORMAT(order_date ,"%Y-%m-%d") as end_date')
                              ->groupBy('order_date')->get()->keyBy('end_date');

            case 'monthly':
                return $orders->selectRaw('MONTH(order_date) as month')
                              ->groupBy('month')->get()->keyBy('month');

            case 'weekly':
                return $orders->selectRaw('WEEK(order_date) as week')
                              ->groupBy('week')->get()->keyBy('week');

            default:
                return $orders->get()->keyBy('year');
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
}
