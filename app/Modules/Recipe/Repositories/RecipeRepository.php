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
     * @param $input
     * @return mixed
     * @throws RepositoryException
     */
    public function getRecipeList($input) {
        $this->applyCriteria();

        $recipes = $this->model->with([
            'fiddles.thread:id,name,denier,price',
            'fiddles.color:id,name,code'
        ])->withCount('designBeams as associated_count');

        if (isset($input['not_ids']) && (!empty($input['not_ids']))) {
            $recipes = $recipes->whereNotIn('id', $input['not_ids']);
        }

        $recipes = datatables()->of($recipes)->make(true);
        $this->resetModel();

        return $recipes;
    }


}
