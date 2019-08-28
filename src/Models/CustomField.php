<?php

namespace DklCreations\SHCommon\Models;

use Illuminate\Database\Eloquent\Builder;

class CustomField extends BaseModel
{

    /******************************************************************
     * MODEL PROPERTIES
     ******************************************************************/

    public $timestamps = false;

    /**
     * Validation rules for creating/updating
     *
     * @var array
     */
    protected $rules = [
        'name' => 'required',
        'type' => 'required'
    ];

    /**
     * The attributes that are not mass assignable.
     *
     * @var array
     */
    protected $guarded = [
        'id', 'decimal'
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'select_values' => 'array',
    ];

    /**
     * Does this model use content object permissions
     *
     * @var bool
     */
    protected static $usesContentObjectPermissions = false;

    /**
     * The default order by clause for a model
     *
     * @var array
     */
    protected static $defaultOrder = ['display_order', 'ASC'];


    /******************************************************************
     * CUSTOM  PROPERTIES
     ******************************************************************/


    /******************************************************************
     * MODEL RELATIONSHIPS
     ******************************************************************/


    /******************************************************************
     * ATTRIBUTE ACCESSORS
     ******************************************************************/



    /******************************************************************
     * ATTRIBUTE MUTATORS
     ******************************************************************/



    /******************************************************************
     * MODEL BOOT METHOD
     ******************************************************************/

}
