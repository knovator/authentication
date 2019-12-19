<?php

namespace App\Modules\Stock\Repositories;

use App\Constants\Master;
use App\Modules\Stock\Models\Stock;
use App\Modules\Thread\Constants\ThreadType;
use App\Modules\Thread\Models\ThreadColor;
use DB;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Knovators\Support\Criteria\OrderByDescId;
use Knovators\Support\Traits\BaseRepository;
use Prettus\Repository\Exceptions\RepositoryException;

/**
 * Class StockRepository
 * @package App\Modules\Stock\Repository
 */
class StockRepository extends BaseRepository
{

    /**
     * @throws RepositoryException
     */
    public function boot() {
        $this->pushCriteria(OrderByDescId::class);
    }

    /**
     * Configure the Model
     *
     **/
    public function model() {
        return Stock::class;
    }

    /**
     * @param $field
     * @param $values
     * @return mixed
     */
    public function removeByField($field, $values) {
        return $this->model->whereIn($field,
            $values)->delete();
    }

    /**
     * @param $threadColor
     * @param $exceptIds
     * @param $stockCountStatus
     * @return
     * @throws Exception
     */
    public function getThreadOrderReport($threadColor, $exceptIds, $stockCountStatus) {
        $columns = 'order_id,order_type,SUM(kg_qty) as stock';
        $reports = $this->model->selectRaw($this->setStockCountColumn($stockCountStatus, $columns))
                               ->where([
                                   'product_id'   => $threadColor->id,
                                   'product_type' => 'thread_color',
                               ])->whereNotIn('status_id', $exceptIds)->groupBy([
                'order_id',
                'order_type'
            ])->with([
                'order.customer.state:id,name,code',
                'order.status:id,name,code',
            ])->orderByDesc('created_at');

        $reports = datatables()->of($reports)->make(true);

        return $reports;
    }

    /**
     * @param        $stockCountStatus
     * @param        $columns
     * @param string $tablePrefix
     * @param string $quantityColumn
     * @return string
     */
    private function setStockCountColumn(
        $stockCountStatus,
        $columns,
        $tablePrefix = '',
        $quantityColumn = 'kg_qty'
    ) {

        foreach ($stockCountStatus as $key => $status) {
            if (!preg_match("/[A-Z]/", $key)) {
                $condition = '';
                $last = end($stockCountStatus[$key]);
                foreach ($stockCountStatus[$key] as $availableId) {
                    $condition .= $tablePrefix . 'status_id = ' . $availableId . ($availableId !=
                        $last ? ' OR ' : '');
                }
                $columns .= ",IFNULL(SUM(IF($condition, {$quantityColumn}, 0)),0) AS {$key}";

            } else {
                $status['code'] = strtolower($status['code']);
                $columns .= ",IFNULL(SUM(IF({$tablePrefix}status_id = {$status['id']}, {$quantityColumn}, 0)),0) AS {$status['code']}";
            }
        }

        return $columns;

    }


    /**
     * @param $usedCount
     * @param $input
     * @return mixed
     * @throws Exception
     */
    public function getStockOverview($usedCount, $input) {
        $columns = $this->setStockCountColumn(Arr::except($usedCount, 'beam_statuses'),
            'product_id,product_type');
        $stocks = $this->model->selectRaw($columns)->with([
            'product' => function ($product) use ($input, $usedCount) {
                /** @var Builder $product */
                if (isset($input['type']) && ($input['type'] == ThreadType::WARP)) {
                    $product->with([
                        'beamMeters' => function ($beamMeters) use ($usedCount,$input) {
                            /** @var Builder $beamMeters */
                            $beamMeters->where('sales_orders.status_id','<>',$input['cancel_order'])->selectRaw($this->setStockCountColumn($usedCount['beam_statuses'],
                                'thread_color_id', 'deliveries.', 'deliveries.meters'));
                        }
                        ,
                        'totalOrderMeters'=> function ($totalOrderMeters) use($usedCount,$input) {
                            $totalOrderMeters->where('sales_orders.status_id', '<>',$input['cancel_order']);
                        }
                    ]);
                }
                $product->with('thread:id,name,denier', 'color:id,name,code');
            }

        ])->groupBy('product_id', 'product_type');


        /** @var Builder $stocks */


        if (isset($input['type_id']) || isset($input['is_demanded'])) {
            $stocks = $stocks->whereHasMorph('product', [ThreadColor::class],
                function ($product) use ($input) {
                    /** @var Builder $product */

                    if (isset($input['type_id'])) {
                        return $product->whereHas('thread', function ($thread) use ($input) {
                            /** @var Builder $thread */
                            $thread->where('type_id', '=', $input['type_id']);
                        });
                    }


                    return $product->where('is_demanded', '=', true);

                });
        } else {
            $stocks = $stocks->orderBy('remaining_count');
        }


        return datatables()->of($stocks)->make(true);
    }


    /**
     * @param      $statuses
     * @param      $usedCount
     * @param bool $export
     * @return mixed
     * @throws Exception
     */
    public function leastUsedThreads($statuses, $usedCount, $export = false) {
        $columns = $this->setStockCountColumn($usedCount,
            "product_id,product_type,max(created_at) as last_used_date, (SUM(IF(status_id = {$statuses[Master::PO_DELIVERED]['id']} OR status_id = {$statuses[Master::SO_MANUFACTURING]['id']} OR status_id = {$statuses[Master::SO_DELIVERED]['id']} OR status_id = {$statuses[Master::WASTAGE_PENDING]['id']} OR status_id = {$statuses[Master::WASTAGE_DELIVERED]['id']}, kg_qty, 0)))-ABS(SUM(IF(status_id = {$statuses[Master::SO_PENDING]['id']}, kg_qty, 0))) AS unused_qty");
        $stocks = $this->model->selectRaw($columns)
                              ->with([
                                  'product.thread:id,name,denier',
                                  'product.color:id,name,code'
                              ])->groupBy('product_id', 'product_type')
                              ->havingRaw('available_count > 0')
                              ->orderByRaw('unused_qty DESC,last_used_date ASC');

        $stocks = datatables()->of($stocks);

        if ($export) {
            return $stocks->skipPaging()->make(true)->getData()->data;
        }

        return $stocks->make(true);
    }


    /**
     * @param $threadColorId
     * @param $usedCount
     * @param $input
     * @return mixed
     */
    public function stockCount($threadColorId, $usedCount, $input) {
        $stock = $this->model->selectRaw($this->setStockCountColumn($usedCount,
            'product_id,product_type'));
        /** @var Builder $stock */
        if (isset($input['customer_ids'])) {
            $stock = $stock->whereHasMorph('order', ['*'], function ($order) use ($input) {
                $order->whereIn('customer_id', $input['customer_ids']);
            });
        }
        /** @var Stock $stock */
        $stock = $stock->where('product_id', $threadColorId)->first();

        return $stock;
    }

    /**
     * @param $customerId
     * @param $usedCount
     * @param $input
     * @return mixed
     */
    public function customerStockCount($customerId, $usedCount) {
        $stock = $this->model->selectRaw($this->setStockCountColumn($usedCount,
            'product_type'))
                             ->whereHasMorph('order', ['*'], function ($order) use ($customerId) {
                                 $order->whereCustomerId($customerId);
                             })->first();


        return $stock;
    }


}
