<?php

namespace App\Modules\Stock\Repositories;

use App\Modules\Stock\Models\Stock;
use Carbon\Carbon;
use Exception;
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
     * @param $stockCountStatus
     * @param $columns
     * @return string
     */
    private function setStockCountColumn($stockCountStatus, $columns) {
        foreach ($stockCountStatus as $key => $status) {
            if ($key == 'available_count' || $key == 'remaining_count') {
                $condition = '';
                $last = end($stockCountStatus[$key]);
                foreach ($stockCountStatus[$key] as $availableId) {
                    $condition .= 'status_id = ' . $availableId . ($availableId != $last ? ' OR ' : '');
                }
                $columns .= ",SUM(IF($condition, kg_qty, 0)) AS {$key}";

            } else {
                $status['code'] = strtolower($status['code']);
                $columns .= ",SUM(IF(status_id = {$status['id']}, kg_qty, 0)) AS {$status['code']}";
            }
        }

        return $columns;

    }


    /**
     * @param $usedCount
     * @return mixed
     * @throws Exception
     */
    public function getStockOverview($usedCount) {
        $columns = $this->setStockCountColumn($usedCount, 'product_id,product_type');
        $stocks = $this->model->selectRaw($columns)->with([
            'product.thread:id,name,denier',
            'product.color:id,name,code'
        ])->groupBy('product_id', 'product_type')->orderBy('remaining_count');

        return datatables()->of($stocks)->make(true);
    }


    /**
     * @param $threadColorId
     * @param $usedCount
     * @return mixed
     * @throws Exception
     */
    public function stockCount($threadColorId, $usedCount) {
        $columns = $this->setStockCountColumn($usedCount, 'product_id,product_type');

        return $this->model->selectRaw($columns)->with([
            'product.thread:id,name,denier',
            'product.color:id,name,code'
        ])->where('product_id', $threadColorId)->first();
    }


}
