<?php


namespace App\Repositories;

use App\Models\GenerateId;
use Knovators\Support\Traits\BaseRepository;

/**
 * Class GenerateIdRepository
 * @package App\Repositories
 */
class GenerateIdRepository extends BaseRepository
{


    /**
     * Configure the Model
     *
     **/
    public function model() {
        return GenerateId::class;
    }

}
