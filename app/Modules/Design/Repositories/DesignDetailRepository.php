<?php

namespace App\Modules\Design\Repositories;

use App\Modules\Design\Models\DesignDetail;
use Knovators\Support\Traits\BaseRepository;

/**
 * Class DesignDetailRepository
 * @package App\Modules\Design\Repository
 */
class DesignDetailRepository extends BaseRepository
{


    /**
     * Configure the Model
     *
     **/
    public function model() {
        return DesignDetail::class;
    }

}
