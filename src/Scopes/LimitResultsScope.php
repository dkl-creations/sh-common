<?php

namespace DklCreations\SHCommon\Scopes;

use Illuminate\Database\Eloquent\Scope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class LimitResultsScope implements Scope
{

    /**
     * Default limit
     *
     * @var int
     */
    protected $limit;

    /**
     * LimitResults constructor.
     *
     * @param $limit
     */
    public function __construct($limit)
    {
        $this->limit = $limit;
    }

    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return void
     */
    public function apply(Builder $builder, Model $model)
    {
        $request = \Illuminate\Http\Request::capture();
        $input = $request->all();

        if (empty($builder->getQuery()->limit)) {
            $limit = isset($input['limit']) && $input['limit'] <= $this->limit ? $input['limit'] : $this->limit;
            $builder->limit($limit);
        }
        if (empty($builder->getQuery()->offset) && isset($input['offset'])) {
            $builder->offset($input['offset']);
        }

    }
}