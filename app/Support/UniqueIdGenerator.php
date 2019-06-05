<?php
/**
 * Created by PhpStorm.
 * User: knovator
 * Date: 23-08-2018
 * Time: 06:46 PM
 */

namespace App\Support;

use App\Repositories\GenerateIdRepository;
use Illuminate\Container\Container;
use Prettus\Repository\Exceptions\RepositoryException;


/**
 * Class UniqueIdGenerator
 * @package App\Support
 */
trait UniqueIdGenerator
{

    /**
     * @param $code
     * @return string
     * @throws RepositoryException
     */
    public function generateUniqueId($code) {
        $generateIdRepo = new GenerateIdRepository(new Container());
        $index = $generateIdRepo->findBy('code', $code);
        $count = ++$index->count;
        $index->update([
            'count' => $count
        ]);

        return $index->prefix . $count;

    }
}
