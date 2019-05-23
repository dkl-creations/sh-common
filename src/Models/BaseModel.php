<?php

namespace Lewisqic\SHCommon\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Watson\Validating\ValidatingTrait;
use Lewisqic\SHCommon\Scopes\ContentObjectPermissionsScope;

abstract class BaseModel extends Model
{
    use ValidatingTrait;

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'created_at', 'updated_at', 'deleted_at',
    ];

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
     * Whether the model should throw a ValidationException if it
     * fails validation. If not set, it will default to false.
     *
     * @var bool
     */
    protected $throwValidationExceptions = true;

    /**
     * The default order by clause for a model
     *
     * @var array
     */
    protected static $defaultOrder = [];

    /**
     * Does this model use content object permissions
     *
     * @var bool
     */
    protected static $usesContentObjectPermissions = false;


    /******************************************************************
     * MODEL BOOT METHOD
     ******************************************************************/

    /**
     * The "booting" method of the model.
     */
    protected static function boot()
    {
        parent::boot();

        // check for content object permissions
        if (static::$usesContentObjectPermissions) {
            static::addGlobalScope(new ContentObjectPermissionsScope);
        }

        // apply our default order by clause
        if (!empty(static::$defaultOrder) && is_array(static::$defaultOrder)) {
            static::addGlobalScope('order_by', function (Builder $builder) {
                if ( empty($builder->getQuery()->orders) ) {
                    $builder->orderBy(static::$defaultOrder[0], static::$defaultOrder[1]);
                }
            });
        }

        // listen for model events
        static::created(function($model) {
            if (static::$usesContentObjectPermissions && !empty(app('role'))) {
                // create content object permission record
                DB::table('content_object_permissions')->insert([
                    'role_id' => app('role')['id'],
                    'model_id' => $model->id,
                    'model_type' => get_class($model),
                ]);
            }
        });

    }

}
