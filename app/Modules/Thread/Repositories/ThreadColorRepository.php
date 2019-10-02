<?php


namespace App\Modules\Thread\Repositories;

use App\Modules\Thread\Models\ThreadColor;
use Carbon\Carbon;
use DB;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Knovators\Support\Criteria\OrderByDescId;
use Knovators\Support\Traits\BaseRepository;
use Prettus\Repository\Exceptions\RepositoryException;

/**
 * Class ThreadColorRepository
 * @package App\Modules\Thread\Repository
 */
class ThreadColorRepository extends BaseRepository
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
        return ThreadColor::class;
    }


    /**
     * @param $statusId
     * @param $input
     * @return Model
     * @throws RepositoryException
     */
    public function getColorsList($input) {

        $this->applyCriteria();

        $threadColors = $this->model->whereHas('thread', function ($thread) use ($input) {
            if (isset($input['type_id'])) {
                $thread->where('type_id', '=', $input['type_id']);
            }
        })->whereHas('color', function ($color) use ($input) {
            if (!isset($input['all'])) {
                /** @var Builder $color */
                $color->where('is_active', '=', true);
            }
        })->where('is_active', '=', true);

        $threadColors = $threadColors->with(['thread:id,name,denier,price', 'color:id,name,code'])
                                     ->get();


        $this->resetModel();

        return $threadColors;
    }


    /**
     * @param $ids
     * @return Builder[]|Collection|Model[]
     */
    public function findWithAvailableQty($ids) {
        return $this->model->with(['availableStock', 'thread:id,name,denier', 'color:id,name'])
                           ->whereKey($ids)
                           ->get();
    }

    /**
     * @return mixed
     * @throws RepositoryException
     */
    public function getStockOverview() {
        $this->applyCriteria();
        $threadColors = $this->model->with($this->commonRelations())
                                    ->has('stocks');

        $threadColors = datatables()->of($threadColors)->make(true);
        $this->resetModel();

        return $threadColors;
    }

    /**
     * @return array
     */
    private function commonRelations() {
        return [
            'thread:id,name,denier',
            'color:id,name,code',
            'inPurchaseQty',
            'availableStock',
            'pendingStock',
            'manufacturingStock',
            'deliveredStock',
        ];
    }


    /**
     * @param      $input
     * @param      $soDeliveredId
     * @param bool $export
     * @return Model
     * @throws Exception
     */
    public function leastUsedThreads($input, $soDeliveredId, $export = false) {

        $now = Carbon::now();
        $input['endDate'] = $now->format('Y-m-d');
        $input['startDate'] = $now->subMonths(3)->format('Y-m-d');

        $threads = $this->model->wherehas('stocks',
            function ($stocks) use ($soDeliveredId, $input) {
                /** @var Builder $stocks */
                $stocks->where('status_id', '<>', $soDeliveredId);

                if (isset($input['api']) && $input['api'] == 'dashboard') {
                    $stocks->whereDate('created_at', '>=', $input['startDate'])
                           ->whereDate('created_at', '<=', $input['endDate']);
                }
            })->with(['thread:id,name,denier', 'color:id,name,code', 'availableStock']);


        $threads = datatables()->of($threads);


        if ($export) {
            return $threads->skipPaging()->make(true)->getData()->data;
        }

        return $threads->make(true);

    }

}
