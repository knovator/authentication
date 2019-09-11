<?php namespace App\Modules\Thread\Repositories;

use App\Modules\Thread\Models\Thread;
use Exception;
use Knovators\Support\Criteria\OrderByDescId;
use Knovators\Support\Traits\BaseRepository;
use Prettus\Repository\Exceptions\RepositoryException;

/**
 * Class ThreadRepository
 * @package App\Modules\Thread\Repository
 */
class ThreadRepository extends BaseRepository
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
        return Thread::class;
    }


    /**
     * @return mixed
     * @throws RepositoryException
     * @throws Exception
     */
    public function getThreadList() {
        $this->applyCriteria();
        $threads = datatables()->of($this->model->with([
            'type:id,name,code',
            'threadColors.color:id,name,code'
        ])->select('threads.*')->withCount([
            'fiddles',
            'beams',
            'wastage',
            'yarnPurchases as yarns_count'
        ]))
                               ->addColumn('beams_count', function (Thread $thread) {
                                   if ($thread->beams_count || $thread->wastage_count || $thread->yarns_count) {
                                       return 1;
                                   }

                                   return 0;
                               })->make(true);
        $this->resetModel();

        return $threads;
    }


}
