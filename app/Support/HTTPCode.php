<?php


namespace App\Support;


/**
 * Class HTTPCode
 * @package App\Modules\Support
 */
class HTTPCode
{
    const OK = 200; // success
    const CREATED = 201; // create
    const BAD_REQUEST = 400; // server couldn't understand
    const UNAUTHORIZED = 401; // unauthorized
    const FORBIDDEN = 403;  // permission denied
    const NOT_FOUND = 404; // page not found
    const UNPROCESSABLE_ENTITY = 422; // validation error
}
