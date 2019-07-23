<?php

namespace App\Modules\Customer\Repositories;

use App\Modules\Customer\Models\Agent;
use Knovators\Support\Traits\BaseRepository;

/**
 * Class AgentRepository
 * @package App\Modules\Customer\Repository
 */
class AgentRepository extends BaseRepository
{

    /**
     * Configure the Model
     *
     **/
    public function model() {
        return Agent::class;
    }


}
