<?php

namespace App\Modules\Recipe\Repositories;

use App\Modules\Recipe\Models\Recipe;
use Knovators\Support\Criteria\OrderByDescId;
use Knovators\Support\Traits\BaseRepository;
use Knovators\Support\Traits\StoreWithTrashedRecord;
use Prettus\Repository\Exceptions\RepositoryException;

/**
 * Class RecipeRepository
 * @package App\Modules\Recipe\Repository
 */
class RecipeRepository extends BaseRepository
{

    use StoreWithTrashedRecord;

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
        return Recipe::class;
    }


    /**
     * @return mixed
     * @throws RepositoryException
     * @throws \Exception
     */
    public function getRecipeList() {
        $this->applyCriteria();
        $recipes = datatables()->of($this->model->with([
            'fiddles.thread:id,name,denier,price',
            'fiddles.color:id,name,code'
        ])->withCount('designBeams as associated_count'))->make(true);
        $this->resetModel();

        return $recipes;
    }


}
