<?php


namespace App\Repositories;

use App\Models\State;
use Knovators\Support\Criteria\OrderByNameCriteria;
use Knovators\Support\Traits\BaseRepository;
use Prettus\Repository\Exceptions\RepositoryException;

/**
 * Class StateRepository
 * @package App\Repositories
 */
class StateRepository extends BaseRepository
{

    /**
     * @throws RepositoryException
     */
    public function boot() {
        $this->pushCriteria(OrderByNameCriteria::class);
    }


    /**
     * Configure the Model
     *
     **/
    public function model() {
        return State::class;
    }


    /**
     * @throws RepositoryException
     */
    public function activeStateList() {

        $this->applyCriteria();
        $states = $this->model->whereIsActive(true)->get(['id', 'name']);
        $this->resetModel();

        return $states;
    }

}
