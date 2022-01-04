<?php

namespace App;

use Jenssegers\Mongodb\Eloquent\Model;

class StaticContent extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'static_content';

    /**
     * The guarded attributes on the model.
     *
     * @var array
     */
    protected $guarded = [];
}
