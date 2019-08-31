<?php

namespace App\Modules\Stock\Repositories;

use App\Modules\Purchase\Models\PurchaseOrder;
use App\Modules\Sales\Models\SalesOrder;
use App\Modules\Stock\Models\Stock;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\MorphTo;
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
        $reports = $this->model->selectRaw($this->setStockCountColumn($stockCountStatus))->where([
            'product_id'   => $threadColor->id,
            'product_type' => 'thread_color',
        ])->whereNotIn('status_id', $exceptIds)->groupBy(['order_id', 'order_type'])->with([
            'order.customer.state:id,name,code',
            'order.status:id,name,code',
        ])->orderByDesc('created_at');

        $reports = datatables()->of($reports)->make(true);

        return $reports;
    }

    /**
     * @param $stockCountStatus
     * @return string
     */
    private function setStockCountColumn($stockCountStatus) {
        $columns = 'order_id,order_type,SUM(kg_qty) as stock';
        foreach ($stockCountStatus as $status) {
            $status['code'] = strtolower($status['code']);
            $columns .= ",SUM(IF(status_id = {$status['id']}, kg_qty, 0)) AS {$status['code']}";
        }

        return $columns;

    }

}
