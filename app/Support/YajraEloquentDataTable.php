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
     * @param $query
     * @param $columnName
     * @param $keyword
     * @param $boolean
     * @return mixed
     */
    protected function compileColumnQuerySearch($query, $columnName, $keyword, $boolean) {

        return $this->compileCommonSearch($query, $columnName, $keyword, $boolean, true);

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


    /**
     * Compile query builder where clause depending on configurations.
     *
     * @param mixed  $query
     * @param string $columnName
     * @param string $keyword
     * @param string $boolean
     * @return mixed
     */
    protected function compileQuerySearch($query, $columnName, $keyword, $boolean = 'or') {
        return $this->compileCommonSearch($query, $columnName, $keyword, $boolean, false);
    }


    /**
     * @param      $query
     * @param      $columnName
     * @param      $keyword
     * @param      $boolean
     * @param bool $columnSearch
     * @return mixed
     */
    private function compileCommonSearch(
        $query,
        $columnName,
        $keyword,
        $boolean,
        $columnSearch = false
    ) {
        $parts = explode('.', $columnName);
        $column = array_pop($parts);
        $relation = implode('.', $parts);

        if ($this->isNotEagerLoaded($relation)) {
            return $this->searchResultQuery($columnSearch, $query, $columnName, $keyword, $boolean);


        }
        $baseRelation = array_shift($parts);

        /** @var Builder $query */
        if ($query->getRelation($baseRelation) instanceof MorphTo) {
            return $query->{$boolean . 'whereHasMorph'}($baseRelation, '*',
                function (Builder $query) use ($column, $keyword, $parts, $columnSearch) {

                    if (!empty($parts)) {
                        $childRelation = implode('.', $parts);
                        $query->whereHas($childRelation,
                            function (Builder $query) use ($column, $keyword, $columnSearch) {
                                $this->searchResultQuery($columnSearch, $query, $column, $keyword);
                            });
                    } else {
                        $this->searchResultQuery($columnSearch, $query, $column, $keyword);
                    }
                });
        }

        return $query->{$boolean . 'WhereHas'}($relation,
            function (Builder $query) use ($column, $keyword, $columnSearch) {
                $this->searchResultQuery($columnSearch, $query, $column, $keyword);
            });
    }

    /**
     * @param        $search
     * @param        $query
     * @param        $column
     * @param        $keyword
     * @param string $boolean
     * @return mixed
     */
    private function searchResultQuery($search, $query, $column, $keyword, $boolean = '') {

        if ($search) {
            $this->querySearch($query, $column, $keyword, $boolean);
        } else {
            $this->allQuerySearch($query, $column, $keyword, $boolean);
        }

        return $query;
    }


    /**
     * Compile query builder where clause depending on configurations.
     *
     * @param mixed  $query
     * @param string $column
     * @param string $keyword
     * @param string $boolean
     */
    protected function allQuerySearch($query, $column, $keyword, $boolean = 'or') {
        $column = $this->addTablePrefix($query, $column);
        $column = $this->castColumn($column);
        $sql = $column . ' LIKE ?';

        if ($this->config->isCaseInsensitive()) {
            $sql = 'LOWER(' . $column . ') LIKE ?';
        }

        $query->{$boolean . 'WhereRaw'}($sql, [$this->prepareKeyword($keyword)]);
    }

}
