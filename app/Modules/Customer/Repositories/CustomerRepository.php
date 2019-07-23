<?php

namespace App\Modules\Customer\Repositories;

use App\Modules\Customer\Models\Customer;
use Knovators\Support\Criteria\OrderByDescId;
use Knovators\Support\Traits\BaseRepository;
use Prettus\Repository\Exceptions\RepositoryException;

/**
 * Class CustomerRepository
 * @package App\Modules\Customer\Repository
 */
class CustomerRepository extends BaseRepository
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
        return Customer::class;
    }


    /**
     * @return mixed
     * @throws RepositoryException
     * @throws \Exception
     */
    public function getCustomerList() {
        $this->applyCriteria();
        $designs = datatables()->of($this->model->with([
            'state',
            'agent',
        ])->select('customers.*'))->make(true);
        $this->resetModel();

        return $designs;
    }

}
