<?php


namespace App\Support;

use Knovators\Support\Helpers\HTTPCode;


/**
 * Trait DestroyObject
 * @package App\Support\Traits
 */
trait DestroyObject
{


    /**
     * @param      $relations
     * @param      $model
     * @param      $moduleLabel
     * @param bool $moduleName
     * @return mixed
     */
    public function destroyModelObject($relations, $model, $moduleLabel, $moduleName = false) {
        foreach ($relations as $relation) {
            $column = $relation . '_count';
            $model->loadCount($relation . ' as ' . $column);
            if ($model->$column) {
                return $this->sendResponse(null,
                    __('messages.associated', [
                        'module'  => $moduleLabel,
                        'related' => preg_replace('/(?<!\ )[A-Z]/', ' $0', ucwords($relation))
                    ]),
                    HTTPCode::UNPROCESSABLE_ENTITY);
            }
        }
        $model->delete();


        $moduleName = $moduleName ? $moduleName . '::' : '';

        return $this->sendResponse(null, trans($moduleName . 'messages.deleted', [
            'module' =>
                $moduleLabel
        ]),
            HTTPCode::OK);
    }
}
