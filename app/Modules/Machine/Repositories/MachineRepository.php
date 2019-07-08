<?php

namespace App\Modules\Machine\Repositories;

use App\Modules\Machine\Models\Machine;
use Illuminate\Database\Eloquent\Builder;
use Knovators\Support\Criteria\IsActiveCriteria;
use Knovators\Support\Criteria\OrderByDescId;
use Knovators\Support\Traits\BaseRepository;
use Prettus\Repository\Exceptions\RepositoryException;

/**
 * Class MachineRepository
 * @package App\Modules\Machine\Repository
 */
class MachineRepository extends BaseRepository
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
        return Machine::class;
    }


    /**
     * @return mixed
     * @throws RepositoryException
     * @throws \Exception
     */
    public function getMachineList() {
        $this->applyCriteria();
        $machines = datatables()->of($this->model->select('machines.*')->with([
            'threadColor.thread',
            'threadColor.color:id,name,code'
        ]))->make(true);
        $this->resetModel();

        return $machines;

    }

    /**
     * @param $input
     * @return mixed
     * @throws RepositoryException
     */
    public function getActiveMachines($input) {
        $this->pushCriteria(IsActiveCriteria::class);
        $this->applyCriteria();
        $machines = $this->model->select('id', 'name', 'panno');
        if (isset($input['sales_order'])) {
            /** @var Builder $machines */
            $machines = $machines->where([
                'thread_color_id' => $input['sales_order']->design_beam_id,
                'reed'           => $input['sales_order']->design->detail->reed
            ]);
        }
        $machines = $machines->get();
        $this->resetModel();

        return $machines;
    }


}
