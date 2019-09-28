<?php

namespace App\Modules\Sales\Support;


use App\Modules\Report\Http\Resources\Overview;
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
     * @return AnonymousResourceCollection
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
                $orders = $orders->selectRaw('order_date as start_date,order_date as end_date')
                                 ->groupBy('order_date');
                break;

            case 'monthly':
                $orders = $orders->selectRaw('MONTH(order_date) as month,DATE_SUB(LAST_DAY(order_date),INTERVAL DAY(LAST_DAY(order_date))- 1 DAY) AS start_date,LAST_DAY(order_date) AS end_date')
                                 ->groupBy('month');
                break;

            case 'weekly':
                $orders = $orders->selectRaw('WEEK(order_date) as week,DATE(order_date + INTERVAL ( - WEEKDAY(order_date)) DAY) as start_date,DATE(order_date + INTERVAL (6 - WEEKDAY(order_date)) DAY) as end_date')
                                 ->groupBy('week');
                break;

            case 'yearly':
                $orders = $orders->selectRaw('DATE_FORMAT(order_date ,"%Y-01-01") as start_date,DATE_FORMAT(order_date ,"%Y-12-31") as end_date');
                break;
        }
        return Overview::collection($orders->get());
    }


}
