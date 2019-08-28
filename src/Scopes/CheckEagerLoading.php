<?php

namespace DklCreations\SHCommon\Scopes;

use Illuminate\Database\Eloquent\Scope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class CheckEagerLoading implements Scope
{

    /**
     * CheckEagerLoading constructor.
     */
    public function __construct()
    {
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
        if (!empty($input['with']) && empty($builder->getEagerLoads())) {
            $model_name = strtolower(class_basename($model));
            foreach ($input['with'] as $with) {
                $with_parts = explode('.', $with);
                if (count($with_parts) > 1) {
                    $first = array_shift($with_parts);
                    if (strtolower($first) == $model_name) {
                        $with_cleaned = implode('.', $with_parts);
                        $builder->with($with_cleaned);
                    }
                }
            }
        }
    }
}
