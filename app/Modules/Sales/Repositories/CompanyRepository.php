<?php

namespace App\Modules\Sales\Repositories;

use App\Modules\Sales\Models\ManufacturingCompany;
use Knovators\Support\Traits\BaseRepository;

/**
 * Class CompanyRepository
 * @package App\Modules\Sales\Repository
 */
class CompanyRepository extends BaseRepository
{

    /**
     * Configure the Model
     *
     **/
    public function model() {
        return ManufacturingCompany::class;
    }


}
