<?php


namespace App\Repositories;


use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Knovators\Masters\Repository\MasterRepository as BaseRepository;

/**
 * Class MasterRepository
 * @package App\Repositories
 */
class MasterRepository extends BaseRepository
{


    /**
     * @param array $codes
     * @return array
     */
    public function getIdsByCode(array $codes) {
        return $this->model->whereIn('code', $codes)->pluck('id')->toArray();
    }

    /**
     * @param $colorMaster
     * @param $parentId
     * @return Model
     * @throws \Exception
     */
    public function subMasterList($parentId, $colorMaster) {
        $masters = $this->model
            ->where('parent_id', '=', $parentId)->with('image')->orderByDesc('id');

        if ($parentId == $colorMaster->id) {
            /** @var Builder $masters */
            $masters = $masters->withCount('threadColors as associated');
        }
        $dataTables = datatables()->of($masters);

        return $dataTables->make(true);
    }


}
