<?php


namespace App\Support;

use Illuminate\Database\Eloquent\Model;


/**
 * Trait HasModelEvent
 * @package App\Modules\Support
 */
trait HasModelEvent
{

    public static function boot() {
        parent::boot();
        self::createdEvent();
        self::deletingEvent();
    }

    public static function createdEvent() {
        static::created(function (Model $model) {
            $model->created_by = isset(auth()->user()->id) ? auth()->user()->id : null;
            $model->save();
        });
    }



    public static function deletingEvent() {
        static::deleting(function (Model $model) {
            $model->deleted_by = isset(auth()->user()->id) ? auth()->user()->id : null;
        });
    }

    public static function updatingEvent() {
        static::updating(function (Model $model) {
            $model->updated_by = isset(auth()->user()->id) ? auth()->user()->id : null;
        });
    }

    public static function creatingEvent() {
        static::creating(function (Model $model) {
            $model->created_by = isset(auth()->user()->id) ? auth()->user()->id : null;
        });
    }

}
