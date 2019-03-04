<?php


namespace App\Support;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Psy\Util\Json;
use Illuminate\Support\Arr;

/**
 * Trait APIResponse
 * @package App\Modules\Support
 */
trait APIResponse
{


    /**
     * Validation Fails and throw json response
     * @param Validator $validator
     */
    protected function failedValidation(Validator $validator) {
        throw new HttpResponseException($this->sendResponse(null,
            $validator->errors(),
            HTTPCode::UNPROCESSABLE_ENTITY));
    }

    /**
     * Send Response for current request in json format
     *
     * @param array | mixed $result
     * @param               $message
     * @param int           $code
     * @param null          $exception
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendResponse($result, $message, $code = 200, $exception = null) {
        if ($result instanceof JsonResponse) {
            return $result;
        }

        return JsonResponse::create($this->makeResponse($result, $message, $exception), $code);
    }

    /**
     * Make Response for current request: : Response is in json format
     *
     * @param array $result
     * @param       $message
     * @param null  $exception
     * @return array
     */
    public function makeResponse($result, $message, $exception = null) {

        $response = [
            'data'    => $result,
            'message' => $message
        ];
        if (config('app.env') !== 'production' && !is_null($exception)) {
            $response['error'] = $this->convertExceptionToArray($exception);
        }

        return $response;
    }

    /**
     * @param Exception $e
     * @return array
     */
    protected function convertExceptionToArray(Exception $e) {
        return config('app.debug') ? [
            'message'   => $e->getMessage(),
            'exception' => get_class($e),
            'file'      => $e->getFile(),
            'line'      => $e->getLine(),
            'trace'     => collect($e->getTrace())->map(function ($trace) {
                return Arr::except($trace, ['args']);
            })->all(),
        ] : [
            'message' => $this->isHttpException($e) ? $e->getMessage() : 'Server Error',
        ];
    }
}
