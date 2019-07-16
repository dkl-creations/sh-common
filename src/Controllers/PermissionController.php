<?php

namespace DklCreations\SHCommon\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use DklCreations\SHCommon\Helpers\Api;

class PermissionController extends BaseController
{

    /**
     * Return list of all available permissions for the current service
     *
     * @param Request $request
     * @param         $id
     *
     * @return json
     */
    public function listServicePermissions(Request $request)
    {
        $permissions = config('permissions');
        return \Output::data($permissions)->json();
    }

    /**
     * Return a list of assigned content object permissions
     *
     * @param Request $request
     */
    public function assignedContentPermissions(Request $request, Api $api)
    {
        $this->validate($request, [
            'model_id' => 'required|integer',
            'model_type' => 'required',
        ]);
        $input = $request->all();

        $result = $api->get('identity', 'v1/roles/all');

        $available_roles = [];
        if (isset($result['data']) && is_array($result['data'])) {
            foreach ($result['data'] as $role) {
                if (is_array($role['permissions']) && isset($input['key']) && array_key_exists($input['key'], prepare_cache_permissions($role['permissions']))) {
                    $available_roles[$role['id']] = $role;
                }
            }
        } else {
            fail('No roles available');
        }

        $rows = DB::table('content_object_permissions')
            ->when(!empty($input['model_group_id']), function ($query, $input) {
                return $query->where('group_id', $input['group_id']);
            })
            ->where('model_id', $input['model_id'])
            ->where('model_type', 'App\Models\\' . $input['model_type'])
            ->get();

        $assigned_roles = [];
        foreach ($rows as $row) {
            if (isset($available_roles[$row->role_id])) {
                $assigned_roles[] = ['id' => $row->role_id, 'name' => $available_roles[$row->role_id]['name']];
            }
        }

        return \Output::data([
            'available_roles' => $available_roles,
            'assigned_roles' => $assigned_roles,
        ])->json();
    }

    /**
     * Update the content object permissions for a given record
     *
     * @param Request $request
     */
    public function updateContentPermissions(Request $request)
    {
        $this->validate($request, [
            'model_id' => 'required|integer',
            'model_type' => 'required',
            'roles' => 'array',
        ]);
        $input = $request->all();

        // delete previous permissions
        DB::table('content_object_permissions')
            ->when(!empty($input['model_group_id']), function ($query, $input) {
                return $query->where('model_group_id', $input['model_group_id']);
            })
            ->where('model_id', $input['model_id'])
            ->where('model_type', 'App\Models\\' . $input['model_type'])
            ->delete();

        // create new permissions for each provided role
        if (isset($input['roles']) && is_array($input['roles'])) {
            foreach ($input['roles'] as $role_id) {
                DB::table('content_object_permissions')->insert([
                    'role_id' => $role_id,
                    'model_id' => $input['model_id'],
                    'model_group_id' => !empty($input['model_group_id']) ? $input['model_group_id'] : null,
                    'model_type' => 'App\Models\\' . $input['model_type'],
                ]);
            }
        }

        return \Output::message('Access permissions have been updated')->json();

    }

}
