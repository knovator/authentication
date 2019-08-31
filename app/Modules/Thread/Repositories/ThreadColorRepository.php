<?php


namespace App\Modules\Thread\Repositories;

use App\Models\Master;
use App\Modules\Thread\Models\ThreadColor;
use DB;
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
            if (!isset($input['all'])) {
                /** @var Builder $thread */
                $thread->where('is_active', '=', true);
            }
            if (isset($input['type_id'])) {
                $thread->where('type_id', '=', $input['type_id']);
            }
        })->whereHas('color', function ($color) use ($input) {
            if (!isset($input['all'])) {
                /** @var Builder $color */
                $color->where('is_active', '=', true);
            }
        })->with(['thread:id,name,denier,price', 'color:id,name,code'])->get();

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
     * @param $statusIds
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
     * @param $statusIds
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
     * @param $threadColorId
     * @param $poCancel
     * @return mixed
     */
    public function stockCount($threadColorId) {

        $threadColors = $this->model->with($this->commonRelations())
                                    ->find($threadColorId);

        return $threadColors;
    }
}
