<?php

namespace App\Modules\Machine\Repositories;

use App\Modules\Machine\Models\Machine;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
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
     * @param $statusId
     * @return mixed
     * @throws RepositoryException
     * @throws Exception
     */
    public function getMachineList($statusId) {
        $this->applyCriteria();
        $machines = datatables()->of($this->model->select('machines.*')->with([
            'threadColor.thread',
            'threadColor.color:id,name,code',
        ])->withCount([
            'soPartialOrders as associated_count' => function ($soPartialOrders) use ($statusId) {
                /** @var Builder $soPartialOrders */
                $soPartialOrders->whereHas('delivery', function ($delivery) use ($statusId) {
                    /** @var Builder $delivery */
                    $delivery->where('status_id', '=', $statusId);
                });
            }
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
                'reed' => $input['sales_order']->design->detail->reed
                //                'thread_color_id' => $input['sales_order']->designBeam->thread_color_id,
            ]);
        }
        $machines = $machines->get();
        $this->resetModel();

        return $machines;
    }


    /**
     * @param $deliveryId
     * @return
     */
    public function manufacturingReceipts($deliveryId) {
        $machines = $this->model->with([
            'soPartialOrders' =>
                function ($soPartialOrders) use ($deliveryId) {
                    /** @var Builder $soPartialOrders */
                    $soPartialOrders->with([
                        'orderRecipe.recipe.fiddles' => function ($fiddles) {
                            /** @var Builder $fiddles */
                            $fiddles->with('thread', 'color')->orderByDesc('id');
                        },
                    ])->where('delivery_id',
                        $deliveryId);
                },
            'threadColor.thread',
            'threadColor.color'
        ])->whereHas('soPartialOrders',
            function ($soPartialOrders) use ($deliveryId) {
                /** @var Builder $soPartialOrders */
                $soPartialOrders->where('delivery_id', $deliveryId);
            }
        )->get();


        $partialIds = [];

        $machines->each(function ($machine) use (&$partialIds) {
            $partialIds = array_merge($partialIds,
                $machine->soPartialOrders->pluck('id')->toArray());
        });
        $machines->load([
            'orderCopiedMachines' => function ($orderCopiedMachines) use ($partialIds) {
                /** @var Builder $orderCopiedMachines */
                $orderCopiedMachines->whereIn('partial_order_id', $partialIds);
            }
        ]);


        return $machines;


    }


}
