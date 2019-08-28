<?php

namespace App\Exceptions;

/**
 * Class UnloadedRelationException
 * @package App\Exceptions
 */
class UnloadedRelationException extends \RuntimeException
{

    /**
     * @param $model
     * @param $relation
     * @return UnloadedRelationException
     */
    public static function make($model, $relation) {
        return new static('call to unloaded relationship on ' . $model);
    }

}