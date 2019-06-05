<?php

namespace App\Modules\Recipe\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\PartiallyUpdateRequest;
use App\Modules\Recipe\Http\Requests\CreateRequest;
use App\Modules\Recipe\Http\Requests\UpdateRequest;
use App\Modules\Recipe\Http\Resources\Recipe as RecipeResource;
use App\Modules\Recipe\Models\Recipe;
use App\Modules\Recipe\Repositories\RecipeRepository;
use DB;
use Exception;
use Illuminate\Http\JsonResponse;
use Knovators\Support\Helpers\HTTPCode;
use Knovators\Support\Traits\DestroyObject;
use Log;

/**
 * Class RecipeController
 * @package App\Modules\Recipe\Http\Controllers
 */
class RecipeController extends Controller
{

    use DestroyObject;

    protected $recipeRepository;

    /**
     * RecipeController constructor.
     * @param RecipeRepository $recipeRepository
     */
    public function __construct(
        RecipeRepository $recipeRepository
    ) {
        $this->recipeRepository = $recipeRepository;
    }


    /**
     * @param CreateRequest $request
     * @return mixed
     * @throws Exception
     */
    public function store(CreateRequest $request) {
        $input = $request->all();
        try {
            DB::beginTransaction();
            $recipe = $this->recipeRepository->create($input);
            $recipe->fiddles()->attach($input['thread_color_ids']);
            DB::commit();

            return $this->sendResponse($this->makeResource($recipe->load([
                'fiddles.thread',
                'fiddles.color'
            ])),
                __('messages.created', ['module' => 'Recipe']),
                HTTPCode::CREATED);
        } catch (Exception $exception) {
            DB::rollBack();
            Log::error($exception);

            return $this->sendResponse(null, __('messages.something_wrong'),
                HTTPCode::UNPROCESSABLE_ENTITY, $exception);
        }
    }


    /**
     * @param Recipe        $recipe
     * @param UpdateRequest $request
     * @return JsonResponse
     * @throws Exception
     */
    public function update(Recipe $recipe, UpdateRequest $request) {
        $input = $request->all();
        try {
            DB::beginTransaction();
            $recipe->update($input);
            $recipe->fiddles()->sync($input['thread_color_ids']);
            DB::commit();
            $recipe->fresh();

            return $this->sendResponse($this->makeResource($recipe->load([
                'fiddles.thread',
                'fiddles.color'
            ])),
                __('messages.updated', ['module' => 'Recipe']),
                HTTPCode::OK);
        } catch (Exception $exception) {
            DB::rollBack();
            Log::error($exception);

            return $this->sendResponse(null, __('messages.something_wrong'),
                HTTPCode::UNPROCESSABLE_ENTITY, $exception);
        }
    }


    /**
     * @param Recipe                 $recipe
     * @param PartiallyUpdateRequest $request
     * @return JsonResponse
     */

    public function partiallyUpdate(Recipe $recipe, PartiallyUpdateRequest $request) {
        $recipe->update($request->all());
        $recipe->fresh();

        return $this->sendResponse($this->makeResource($recipe->load([
            'fiddles.thread',
            'fiddles.color'
        ])),
            __('messages.updated', ['module' => 'Recipe']),
            HTTPCode::OK);
    }


    /**
     * @param Recipe $recipe
     * @return JsonResponse
     * @throws Exception
     */
    public function destroy(Recipe $recipe) {
        try {
            // Recipe relations
            $relations = [
                'designBeams'
            ];
            return $this->destroyModelObject($relations, $recipe, 'Recipe');

        } catch (Exception $exception) {
            Log::error($exception);

            return $this->sendResponse(null, __('messages.something_wrong'),
                HTTPCode::UNPROCESSABLE_ENTITY);
        }
    }

    /**
     * @param Recipe $recipe
     * @return RecipeResource
     */
    private function makeResource($recipe) {
        return new RecipeResource($recipe);
    }


    /**
     * @return JsonResponse
     */
    public function index() {
        try {
            $recipes = $this->recipeRepository->getRecipeList();

            return $this->sendResponse($recipes,
                __('messages.retrieved', ['module' => 'Recipes']),
                HTTPCode::OK);
        } catch (Exception $exception) {
            Log::error($exception);

            return $this->sendResponse(null, __('messages.something_wrong'),
                HTTPCode::UNPROCESSABLE_ENTITY);
        }
    }


    /**
     * @param Recipe $recipe
     * @return JsonResponse
     */
    public function show(Recipe $recipe) {
        $recipe->load([
            'fiddles.thread',
            'fiddles.color'
        ]);

        return $this->sendResponse($this->makeResource($recipe),
            __('messages.retrieved', ['module' => 'Recipe']),
            HTTPCode::OK);
    }


}
