<?php


namespace App\Modules\Thread\Repositories;

use App\Modules\Thread\Models\ThreadColor;
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
}
