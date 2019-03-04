<?php
/**
 * Created by PhpStorm.
 * User: knovator
 * Date: 22-08-2018
 * Time: 02:40 PM
 */

namespace App\Support;


use DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOneOrMany;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Exceptions\Exception;

/**
 * Class YajraEloquentDataTable
 * @package App\Support
 */
class YajraEloquentDataTable extends EloquentDataTable
{

    /**
     * Compile queries for column search.
     *
     * @param int    $i
     * @param string $column
     * @param string $keyword
     */
    protected function compileColumnSearch($i, $column, $keyword) {
        if ($this->request->isRegex($i)) {
            $column = strstr($column, '(') ? $this->connection->raw($column) : $column;
            $this->regexColumnSearch($column, $keyword);
        } else {
            $this->compileColumnQuerySearch($this->query, $column, $keyword, '');
        }
    }

    /**
     * @param $query
     * @param $columnName
     * @param $keyword
     * @param $boolean
     */
    protected function compileColumnQuerySearch($query, $columnName, $keyword, $boolean) {
        $parts = explode('.', $columnName);
        $column = array_pop($parts);
        $relation = implode('.', $parts);

        if ($this->isNotEagerLoaded($relation)) {
            return $this->querySearch($query, $columnName, $keyword, $boolean);
        }

        $query->{$boolean . 'WhereHas'}($relation,
            function (Builder $query) use ($column, $keyword) {
                $this->querySearch($query, $column, $keyword, '');
            });
    }

    /**
     * @param        $query
     * @param        $column
     * @param        $keyword
     * @param string $boolean
     */
    protected function querySearch($query, $column, $keyword, $boolean = 'or') {
        $column = $this->addTablePrefix($query, $column);
        $column = $this->castColumn($column);

        $keyword = explode(',', trim($keyword, '%'));

        $query->{$boolean . 'WhereIn'}(DB::raw($column), $keyword);
    }

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
                    return  $relation . '.' . $relationColumn;
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

                case $model instanceof HasOneOrMany:
                    $table = $model->getRelated()->getTable();
                    $foreign = $model->getQualifiedForeignKeyName();
                    $other = $model->getQualifiedParentKeyName();
                    $deletedAt = $this->checkSoftDeletesOnModel($model->getRelated());
                    break;

                case $model instanceof BelongsTo:
                    $table = $model->getRelated()->getTable();
                    $foreign = $model->getQualifiedForeignKey();
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