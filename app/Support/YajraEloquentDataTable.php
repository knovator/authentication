<?php
/**
 * Created by PhpStorm.
 * User: knovator
 * Date: 23-08-2018
 * Time: 06:46 PM
 */

namespace App\Support;


use Illuminate\Database\Eloquent\Builder;
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
     * Compile query builder where clause depending on configurations.
     *
     * @param mixed  $query
     * @param string $columnName
     * @param string $keyword
     * @param string $boolean
     */
    protected function compileQuerySearch($query, $columnName, $keyword, $boolean = 'or') {
        return $this->commonSearch($query, $columnName, $keyword, $boolean);
    }

    /**
     * @param $query
     * @param $columnName
     * @param $keyword
     * @param $boolean
     */
    protected function compileColumnQuerySearch($query, $columnName, $keyword, $boolean) {

        return $this->commonSearch($query, $columnName, $keyword, $boolean);

    }


    /**
     * @param $query
     * @param $columnName
     * @param $keyword
     * @param $boolean
     */
    private function commonSearch($query, $columnName, $keyword, $boolean = '') {

        $parts = explode('.', $columnName);
        $column = array_pop($parts);
        $relation = implode('.', $parts);

        if ($this->isNotEagerLoaded($relation)) {
            return $this->querySearch($query, $columnName, $keyword, $boolean);
        }

        $baseRelation = array_shift($parts);

        /** @var Builder $query */
        if ($query->getRelation($baseRelation) instanceof MorphTo) {
            return $query->{$boolean . 'whereHasMorph'}($baseRelation, '*',
                function (Builder $query) use ($column, $keyword, $parts) {
                    if (!empty($parts)) {
                        $childRelation = implode('.', $parts);
                        $query->whereHas($childRelation,
                            function (Builder $query) use ($column, $keyword) {
                                $this->querySearch($query, $column, $keyword, '');
                            });
                    } else {
                        $this->querySearch($query, $column, $keyword, '');
                    }


                });
        }

        return $query->{$boolean . 'WhereHas'}($relation,
            function (Builder $query) use ($column, $keyword) {
                $this->querySearch($query, $column, $keyword, '');
            });
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
                    return $relation . '.' . $relationColumn;

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
