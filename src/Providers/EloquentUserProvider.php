<?php


namespace Knovators\Authentication\Providers;

use Illuminate\Auth\EloquentUserProvider as BaseEloquentUserProvider;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * Class EloquentUserProvider
 * @package Knovators\Authentication\Providers
 */
class EloquentUserProvider extends BaseEloquentUserProvider
{

    /**
     * @param array $credentials
     * @return Authenticatable|Model|null|object|void|static
     */
    public function retrieveByCredentials(array $credentials) {
        if (empty($credentials) ||
            (count($credentials) === 1 &&
                array_key_exists('password', $credentials))) {
            return;
        }
        // First we will add each credential element to the query as a where clause.
        // Then we can execute the query and, if we found a user, return it in a
        // Eloquent User "model" that will be utilized by the Guard instances.
        $query = $this->createModel()->newQuery();
        foreach ($credentials as $key => $value) {
            if (Str::contains($key, 'password')) {
                continue;
            }



            // Allow to check credential on multiple column
            if (Str::contains($key, ',')) {
                $columns = explode(',', $key);
                if (is_array($columns) || $columns instanceof Arrayable) {
                    $query->where(function ($query) use ($columns, $value) {
                        foreach ($columns as $index => $column) {
                            if ($index) {
                                $query->orWhere($column, $value);
                            } else {
                                $query->where($column, $value);
                            }
                        }
                    });
                }
            }
        }

        return $query->first();
    }

}
