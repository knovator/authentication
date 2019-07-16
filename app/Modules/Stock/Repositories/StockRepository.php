<?php

namespace App\Modules\Stock\Repositories;

use App\Modules\Stock\Models\Stock;
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
     * @param $partialOrderIds
     * @return mixed
     */
    public function removeByPartialOrderId($partialOrderIds) {
        return $this->model->whereIn('order_recipe_id',
            $partialOrderIds)->delete();
    }

    /**
     * @param $threadColor
     * @return
     * @throws \Exception
     */
    public function getThreadOrderReport($threadColor) {
        $reports = $this->model->selectRaw('order_id,order_type,SUM(kg_qty) as stock')->where([
            'product_id'   => $threadColor->id,
            'product_type' => 'thread_color'
        ])->groupBy(['order_id', 'order_type'])->with([
            'order.customer.state:id,name,code',
            'order.status:id,name,code'
        ])->orderByDesc('order_id');

        $reports = datatables()->of($reports)->make(true);

        return $reports;
    }

}
