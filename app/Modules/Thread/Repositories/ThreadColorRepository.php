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
        $threadColors = $this->model;

        if (isset($input['type_id'])) {
            /** @var Builder $threadColors */
            $threadColors = $threadColors->whereHas('thread', function ($thread) use ($input) {
                /** @var Builder $thread */
                $thread->where('type_id', '=', $input['type_id']);
            });
        }

        if (!isset($input['all'])) {
            $threadColors = $threadColors->whereHas('color', function ($color) use ($input) {
                /** @var Builder $color */
                $color->where('is_active', '=', true);

            })->where('is_active', '=', true);
        }

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

}
