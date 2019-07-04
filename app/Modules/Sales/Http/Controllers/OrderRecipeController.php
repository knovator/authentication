<?php

namespace App\Modules\Sales\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Sales\Http\Resources\SalesOrderRecipe as SalesOrderRecipeResource;
use App\Modules\Sales\Models\SalesOrder;
use App\Modules\Sales\Repositories\SalesRecipeRepository;
use Exception;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Knovators\Support\Helpers\HTTPCode;
use Log;

/**
 * Class OrderRecipeController
 * @package App\Modules\Sales\Http\Controllers
 */
class OrderRecipeController extends Controller
{

    protected $orderRecipeRepository;

    /**
     * OrderRecipeController constructor.
     * @param SalesRecipeRepository $orderRecipeRepository
     */
    public function __construct(
        SalesRecipeRepository $orderRecipeRepository
    ) {
        $this->orderRecipeRepository = $orderRecipeRepository;
    }


    /**
     * @param SalesOrder $salesOrder
     * @return mixed
     */
    public function index(SalesOrder $salesOrder) {
        try {
            $orderRecipes = $this->orderRecipeRepository->getOrderRecipeList($salesOrder->id);
            return $this->sendResponse($this->makeResourceCollection($orderRecipes),
                __('messages.retrieved', ['module' => 'Order recipes']),
                HTTPCode::OK);
        } catch (Exception $exception) {
            Log::error($exception);

            return $this->sendResponse(null, __('messages.something_wrong'),
                HTTPCode::UNPROCESSABLE_ENTITY, $exception);
        }
    }

    /**
     * @param $salesOrder
     * @return SalesOrderRecipeResource
     */
    private function makeResource($salesOrder) {
        return new SalesOrderRecipeResource($salesOrder);
    }

    /**
     * @param $collection
     * @return AnonymousResourceCollection
     */
    private function makeResourceCollection($collection) {
        return SalesOrderRecipeResource::collection($collection);
    }
}
