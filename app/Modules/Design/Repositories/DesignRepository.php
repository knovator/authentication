<?php

namespace App\Modules\Design\Repositories;

use App\Modules\Design\Models\Design;
use Knovators\Support\Criteria\OrderByDescId;
use Knovators\Support\Traits\BaseRepository;
use Prettus\Repository\Exceptions\RepositoryException;

/**
 * Class DesignRepository
 * @package App\Modules\Design\Repository
 */
class DesignRepository extends BaseRepository
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
        return Design::class;
    }


}
