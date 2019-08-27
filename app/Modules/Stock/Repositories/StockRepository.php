<?php

namespace App\Modules\Stock\Repositories;

use App\Modules\Stock\Models\Stock;
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
     * @param $statusIds
     * @return
     * @throws Exception
     */
    public function getThreadOrderReport($threadColor, $statusIds) {
        $reports = $this->model->selectRaw('order_id,order_type,SUM(kg_qty) as stock')->where([
            'product_id'   => $threadColor->id,
            'product_type' => 'thread_color',
        ])->whereNotIn('status_id', $statusIds)->groupBy(['order_id', 'order_type'])->with([
            'order.customer.state:id,name,code',
            'order.status:id,name,code'
        ])->orderByDesc('created_at');

        $reports = datatables()->of($reports)->make(true);

        return $reports;
    }

}
