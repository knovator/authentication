<?php

namespace App\Modules\Recipe\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Recipe\Repositories\RecipeRepository;
use Knovators\Support\Traits\DestroyObject;

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







}
