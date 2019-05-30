<?php

namespace Lewisqic\SHCommon\Scopes;

use Illuminate\Database\Eloquent\Scope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class ContentObjectPermissionsScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return void
     */
    public function apply(Builder $builder, Model $model)
    {
        if (isset(app('user')['super_admin_enabled']) && app('user')['super_admin_enabled']) {
            return;
        }
        $table = $model->getTable();
        $type = get_class($model);
        if (!empty(app('role'))) {
            $group_id = $type::getGroupId();
            $model_group_id = !empty($group_id) && $group_id > 0 ? $group_id : null;
            $builder->whereExists(function($query) use($table, $type, $model_group_id) {
                $query->select('*')
                    ->from('content_object_permissions')
                    ->where('content_object_permissions.role_id', app('role')['id'])
                    ->where('content_object_permissions.model_type', $type)
                    ->where('content_object_permissions.model_group_id', $model_group_id)
                    ->whereRaw("content_object_permissions.model_id = {$table}.id");
            });
        }
    }
}