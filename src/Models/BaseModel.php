<?php

namespace Lewisqic\SHCommon\Models;

use Illuminate\Database\Eloquent\Model;
use Watson\Validating\ValidatingTrait;

abstract class BaseModel extends Model
{
    use ValidatingTrait;

    /**
     * Whether the model should throw a ValidationException if it
     * fails validation. If not set, it will default to false.
     *
     * @var bool
     */
    protected $throwValidationExceptions = true;

    /**
     * Default validation rules
     *
     * @var array
     */
    protected $rules = [
        //
    ];

    /**
     * The attributes that are not mass assignable.
     *
     * @var array
     */
    protected $guarded = [
        //
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'created_at', 'updated_at', 'deleted_at',
    ];

}
