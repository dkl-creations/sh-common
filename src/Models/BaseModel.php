<?php

namespace DklCreations\SHCommon\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Watson\Validating\ValidatingTrait;
use Carbon\Carbon;
use Carbon\CarbonTimeZone;
use AjCastro\EagerLoadPivotRelations\EagerLoadPivotTrait;
use DklCreations\SHCommon\Scopes\ContentObjectPermissionsScope;
use DklCreations\SHCommon\Scopes\LimitResultsScope;
use DklCreations\SHCommon\Scopes\CheckEagerLoading;
use DklCreations\SHCommon\Helpers\Format;

abstract class BaseModel extends Model
{
    use ValidatingTrait, EagerLoadPivotTrait;


    /******************************************************************
     * MODEL PROPERTIES
     ******************************************************************/

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'created_at', 'updated_at', 'deleted_at',
    ];

    /**
     * User exposed observable events
     *
     * @var array
     */
    protected $observables = ['validating', 'validated'];

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

    /**
     * Additional role IDs for content object permissions
     *
     * @var bool
     */
    protected static $contentObjectPermissionsExtraRoleIds = [];

    /**
     * Does this model allow eager loading via param
     *
     * @var bool
     */
    protected static $allowParamEagerLoading = true;

    /**
     * Does this model have a slug column
     *
     * @var bool
     */
    protected static $hasSlug = false;

    /**
     * Set a group ID for the model
     *
     * @var null|int
     */
    protected static $modelGroupId = null;

    /**
     * The default limit for all queries
     *
     * @var array
     */
    protected static $defaultLimit = 100;


    /******************************************************************
     * MODEL METHODS
     ******************************************************************/

    /**
     * Cast an attribute to a native PHP type.
     *
     * @param  string  $key
     * @param  mixed  $value
     * @return mixed
     */
    protected function castAttribute($key, $value)
    {
        if ($this->getCastType($key) == 'array' && is_null($value)) {
            return [];
        }
        return parent::castAttribute($key, $value);
    }

    /**
     * Set the group ID for this model
     *
     * @param $id
     */
    public static function setGroupId($id)
    {
        self::$modelGroupId = $id;
    }

    /**
     * Get the group ID for this model
     *
     * @return int
     */
    public static function getGroupId()
    {
        return self::$modelGroupId;
    }


    /******************************************************************
     * ATTRIBUTE ACCESSORS
     ******************************************************************/

    /**
     * Global accessor for all model attributes
     *
     * @return array
     */
    protected function getArrayableAttributes()
    {
        // convert all timestamp columns to relevant timezone
        foreach ($this->attributes as $key => $value) {
            // convert all dates to proper timezone
            if (preg_match('/_at$/', $key) && preg_match('/:\d{2}$/', $value) && !empty($value) && !empty(data('user')['timezone'])) {
                try {
                    $date = Carbon::parse($value, 'UTC');
                    $this->attributes[$key] = $date->setTimezone(data('user')['timezone'])->format('Y-m-d H:i:s');
                } catch (\Exception $e) {
                    // do nothing if invalid date format
                }
            }
        }
        return $this->getArrayableItems($this->attributes);
    }


    /******************************************************************
     * ATTRIBUTE MUTATORS
     ******************************************************************/

    /**
     * Global mutator for all model attributes
     *
     * @param string $key
     * @param mixed  $value
     *
     * @return mixed|void
     */
    public function setAttribute($key, $value)
    {
        parent::setAttribute($key, $value);
        // convert all dates into UTC timezone
        if (preg_match('/_at$/', $key) && preg_match('/:\d{2}$/', $value) && !empty($value) && !empty(data('user')['timezone'])) {
            try {
                $date = Carbon::parse($value, data('user')['timezone']);
                $this->attributes[$key] = $date->setTimezone('UTC')->format('Y-m-d H:i:s');
            } catch (\Exception $e) {
                // do nothing if invalid date format
            }
        }
    }

    /**
     * Set the slug value
     *
     * @param  string  $value
     * @return void
     */
    public function setSlugAttribute($value)
    {
        if ( empty($value) && isset($this->attributes['title']) ) {
            $value = $this->attributes['title'];
        }
        if (!isset($this->attributes['slug']) || $this->attributes['slug'] != $value) {
            $slug = Format::slug($value);
            if ( !empty($slug) ) {
                $count = static::when(!empty($this->attributes['id']), function ($query) {
                    return $query->where('id', '!=', $this->attributes['id']);
                })
                    ->where(function ($query) use ($slug) {
                        $query->where('slug', "{$slug}")
                            ->orWhere('slug', 'LIKE', "{$slug}-duplicate%");
                    })
                    ->count();
                if ( $count > 0 ) {
                    $slug = $slug . "-duplicate-" . ($count + 1);
                }
            }
            $this->attributes['slug'] = $slug;
        }
    }


    /******************************************************************
     * MODEL BOOT METHOD
     ******************************************************************/


    /**
     * The "booting" method of the model.
     */
    protected static function boot()
    {
        parent::boot();

        // limit all results
        static::addGlobalScope(new LimitResultsScope(static::$defaultLimit));

        // check for eager loading requests
        if (static::$allowParamEagerLoading) {
            static::addGlobalScope(new CheckEagerLoading());
        }

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

        // set our slug to empty on create
        static::creating(function ($model) {
            if (static::$hasSlug && !isset($model->slug)) {
                $model->slug = '';
            }
        });

        // listen for model events
        static::created(function($model) {
            if (static::$usesContentObjectPermissions && !empty(data('role'))) {
                $type = get_class($model);
                $group_id = $type::getGroupId();
                $model_group_id = !empty($group_id) && $group_id > 0 ? $group_id : null;
                // create content object permission record
                $roles = [data('role')['id']];
                if (!empty(static::$contentObjectPermissionsExtraRoleIds)) {
                    $roles = array_merge($roles, static::$contentObjectPermissionsExtraRoleIds);
                }
                foreach (array_unique($roles) as $role_id) {
                    DB::table('content_object_permissions')->insert([
                        'role_id' => $role_id,
                        'model_id' => $model->id,
                        'model_group_id' => $model_group_id,
                        'model_type' => $type,
                    ]);
                }
            }
        });

    }

}
