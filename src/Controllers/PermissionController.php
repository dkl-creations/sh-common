<?php

namespace Lewisqic\SHCommon\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
    public function assignedContentPermissions(Request $request)
    {
        
        sd($request->all());
        
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
            ->where('model_type', $input['model_type'])
            ->delete();

        // create new permissions for each provided role
        if (isset($input['roles']) && is_array($input['roles'])) {
            foreach ($input['roles'] as $role_id) {
                DB::table('content_object_permissions')->insert([
                    'role_id' => $role_id,
                    'model_id' => $input['model_id'],
                    'model_group_id' => !empty($input['model_group_id']) ? $input['model_group_id'] : null,
                    'model_type' => $input['model_type'],
                ]);
            }
        }

        return \Output::message('Permissions have been updated successfully')->json();

    }

}
