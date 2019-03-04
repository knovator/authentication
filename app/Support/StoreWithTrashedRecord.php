<?php


namespace App\Support;


/**
 * Trait StoreWithTrashedRecord
 * @package App\Modules\Support
 */
trait StoreWithTrashedRecord
{

    /**
     * @param $column
     * @param $value
     * @param $input
     * @return
     */
    public function createOrUpdateTrashed($column, $value, $input) {
        $input['deleted_at'] = null;

        return $this->model->onlyTrashed()->updateOrCreate([$column => $value], $input);
    }
}
