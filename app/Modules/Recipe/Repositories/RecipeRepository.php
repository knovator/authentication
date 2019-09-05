<?php

namespace App\Modules\Recipe\Repositories;

use App\Modules\Recipe\Models\Recipe;
use App\Support\OrderByUpdatedAt;
use Exception;
use Illuminate\Database\Eloquent\Builder;
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
        $this->pushCriteria(OrderByUpdatedAt::class);
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
     * @throws Exception
     */
    public function getRecipeList($input) {
        $this->applyCriteria();

        $recipes = $this->model->with([
            'fiddles.thread:id,name,denier,price',
            'fiddles.color:id,name,code'
        ])->withCount(['designBeams as beams_count', 'wastageOrderRecipe as wastage_count']);

        if (isset($input['is_active'])) {
            $recipes = $recipes->where('is_active', $input['is_active']);
        }

        if (isset($input['not_ids']) && (!empty($input['not_ids']))) {
            $recipes = $recipes->whereNotIn('id', $input['not_ids']);
        }

        if (!isset($input['wastage']) || $input['wastage'] == 'no') {
            $recipes = $recipes->where('type', '<>', 'wastage');
        }

        $recipes = datatables()->of($recipes)
                               ->addColumn('associated_count', function (Recipe $recipe) {
                                   if ($recipe->beams_count || $recipe->wastage_count) {
                                       return 1;
                                   }

                                   return 0;
                               })->removeColumn('beams_count', 'wastage_count')->make(true);
        $this->resetModel();

        return $recipes;
    }


    /**
     * @param $input
     * @return Recipe
     * @throws RepositoryException
     */
    public function findUniqueNesRecipe($input) {

        $recipe = $this->model->where(['total_fiddles' => $input['total_fiddles']]);

        if (isset($input['unchecked_id'])) {
            /** @var Builder $recipe */
            $recipe = $recipe->whereKeyNot($input['unchecked_id']);
        }
        foreach ($input['thread_color_ids'] as $fiddle) {
            /** @var Builder $recipe */
            $recipe = $recipe->whereExists(function ($query) use ($fiddle) {
                /** @var Builder $query */
                $query->from('recipes_fiddles')
                      ->whereRaw('recipes.id = recipes_fiddles.recipe_id')
                      ->where('fiddle_no', '=', $fiddle['fiddle_no'])
                      ->where('thread_color_id', '=', $fiddle['thread_color_id']);

            });
        }

        return $recipe->first();

    }


}
