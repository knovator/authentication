<?php
/**
 * Created by PhpStorm.
 * User: knovator
 * Date: 23-08-2018
 * Time: 06:46 PM
 */

namespace App\Support;


use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOneOrMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Knovators\Support\Helpers\YajraEloquentDataTable as BaseDataTable;
use Yajra\DataTables\Exceptions\Exception;

/**
 * Class YajraEloquentDataTable
 * @package App\Support
 */
class YajraEloquentDataTable extends BaseDataTable
{

    /**
     * Join eager loaded relation and get the related column name.
     *
     * @param string $relation
     * @param string $relationColumn
     * @return string
     * @throws \Yajra\DataTables\Exceptions\Exception
     */
    protected function joinEagerLoadedColumn($relation, $relationColumn) {
        $table = '';
        $deletedAt = false;
        $lastQuery = $this->query;
        foreach (explode('.', $relation) as $eachRelation) {
            $model = $lastQuery->getRelation($eachRelation);
            switch (true) {
                case $model instanceof BelongsToMany:
                    return $relation . '.' . $relationColumn;
                // belongs to many relationship does not work properly.
                /*$pivot = $model->getTable();
                $pivotPK = $model->getExistenceCompareKey();
                $pivotFK = $model->getQualifiedParentKeyName();
                $this->performJoin($pivot, $pivotPK, $pivotFK);

                $related = $model->getRelated();
                $table = $related->getTable();
                // $tablePK = $related->getForeignKey() changed to $model->getRelatedPivotKeyName()
                $tablePK = $model->getRelatedPivotKeyName();
                $foreign = $pivot . '.' . $tablePK;
                $other = $related->getQualifiedKeyName();

                // removed conflict code when retrieving belongs to many relations data
                $lastQuery->addSelect($table . '.' . $relationColumn . ' as ' . $table . '_'
                    . $relationColumn );
                $this->performJoin($table, $foreign, $other);*/

                //break;

                case $model instanceof MorphTo:
                    return $relation . '.' . $relationColumn;
                    break;

                case $model instanceof HasOneOrMany:
                    $table = $model->getRelated()->getTable();
                    $foreign = $model->getQualifiedForeignKeyName();
                    $other = $model->getQualifiedParentKeyName();
                    $deletedAt = $this->checkSoftDeletesOnModel($model->getRelated());
                    break;

                case $model instanceof BelongsTo:
                    $table = $model->getRelated()->getTable();
                    $foreign = $model->getQualifiedForeignKeyName();
                    $other = $model->getQualifiedOwnerKeyName();
                    $deletedAt = $this->checkSoftDeletesOnModel($model->getRelated());
                    break;

                default:
                    throw new Exception('Relation ' . get_class($model) . ' is not yet supported.');
            }
            $this->performJoin($table, $foreign, $other, $deletedAt);
            $lastQuery = $model->getQuery();
        }

        return $table . '.' . $relationColumn;
    }

}
