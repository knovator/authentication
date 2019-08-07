<?php

namespace App\Modules\Design\Repositories;

use App\Modules\Design\Models\Design;
use Knovators\Support\Criteria\IsActiveCriteria;
use Knovators\Support\Criteria\OrderByDescId;
use Knovators\Support\Traits\BaseRepository;
use Prettus\Repository\Exceptions\RepositoryException;

/**
 * Class DesignRepository
 * @package App\Modules\Design\Repository
 */
class DesignRepository extends BaseRepository
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
        return Design::class;
    }

    /**
     * @return mixed
     * @throws RepositoryException
     * @throws \Exception
     */
    public function getDesignList() {
        $this->applyCriteria();
        $designs = datatables()->of($this->model->select('designs.*')->with([
            'detail',
            'mainImage.file:id,uri'
        ])->with('beamRecipes')->withCount(['beams as total_beams', 'recipes as total_recipes']))
                               ->make(true);
        $this->resetModel();

        return $designs;
    }

    /**
     * @return mixed
     * @throws RepositoryException
     * @throws \Exception
     */
    public function getActiveDesigns() {
        $this->pushCriteria(IsActiveCriteria::class);
        $this->applyCriteria();
        $designs = $this->model->select('id', 'quality_name', 'design_no')->with([
            'mainImage.file:id,uri'
        ])->where('is_approved', true)->get();
        $this->resetModel();

        return $designs;
    }

}
