<?php


namespace App\Modules\Thread\Repositories;

use App\Models\Master;
use App\Modules\Thread\Models\ThreadColor;
use DB;
use Illuminate\Database\Eloquent\Builder;
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
     * @return Model
     * @throws RepositoryException
     */
    public function getColorsList($statusId) {

        $this->applyCriteria();
        $threadColors = $this->model->whereHas('thread', function ($thread) use ($statusId) {
            $thread->whereIsActive(true);
            if (!is_null($statusId)) {
                $thread->whereTypeId($statusId);
            }

        })->with(['thread:id,name,denier,price', 'color:id,name,code'])->get();
        $this->resetModel();

        return $threadColors;
    }

    /**
     * @param $poPending
     * @param $poCancel
     * @return mixed
     * @throws RepositoryException
     * @throws \Exception
     */
    public function getStockOverview($poCancel) {
        $this->applyCriteria();
        $threadColors = $this->model->with($this->commonRelations($poCancel))
                                    ->has('stocks');

        $threadColors = datatables()->of($threadColors)->make(true);
        $this->resetModel();

        return $threadColors;
    }


    /**
     * @param $threadColorId
     * @param $poCancel
     * @return mixed
     */
    public function stockCount($threadColorId, $poCancel) {

        $threadColors = $this->model->with($this->commonRelations($poCancel))
                                    ->find($threadColorId);

        return $threadColors;
    }


    /**
     * @param Master $poCancel
     * @return array
     */
    private function commonRelations(Master $poCancel) {
        return [
            'thread:id,name,denier',
            'color:id,name,code',
            'inPurchaseQty',
            'availableStock' => function ($availableStock) use ($poCancel) {
                /** @var Builder $availableStock */
                $availableStock->whereNotIn('status_id', [$poCancel->id]);
            },
            'pendingStock',
            'manufacturingStock',
            'deliveredStock',
        ];
    }
}
