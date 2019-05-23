<?php namespace App\Modules\User\Repositories;

use Knovators\Support\Criteria\OrderByDescId;
use App\User;
use Knovators\Authentication\Repository\UserRepository as BaseRepository;
use Knovators\Support\Traits\StoreWithTrashedRecord;
use Prettus\Repository\Exceptions\RepositoryException;

/**
 * Class UserRepository
 * @package App\Modules\User\Repository
 */
class UserRepository extends BaseRepository
{

    use StoreWithTrashedRecord;

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
        return User::class;
    }

    /**
     * @return mixed
     * @throws RepositoryException
     */
    public function getUserList() {
        $this->applyCriteria();
        $users = datatables()->of($this->model)->make(true);
        $this->resetModel();

        return $users;
    }

}
