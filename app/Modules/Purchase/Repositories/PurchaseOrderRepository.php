<?php

namespace App\Modules\Purchase\Repositories;

use App\Modules\Purchase\Models\PurchaseOrder;
use Knovators\Support\Criteria\OrderByDescId;
use Knovators\Support\Traits\BaseRepository;
use Prettus\Repository\Exceptions\RepositoryException;

/**
 * Class PurchaseOrderRepository
 * @package App\Modules\Purchase\Repository
 */
class PurchaseOrderRepository extends BaseRepository
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
        return PurchaseOrder::class;
    }


    /**
     * @return mixed
     * @throws RepositoryException
     * @throws \Exception
     */
    public function getPurchaseOrderList() {
        $this->applyCriteria();
        $designs = datatables()->of($this->model->with([
            'detail',
            'mainImage.file:id,uri'
        ])->select('designs.*'))->make(true);
        $this->resetModel();

        return $designs;
    }

}
