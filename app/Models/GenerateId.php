<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class GenerateId
 * @package App\Models
 */
class GenerateId extends Model
{

    public $timestamps = false;

    protected $table = 'generate_unique_ids';

    protected $fillable = ['code', 'prefix', 'count'];

}
