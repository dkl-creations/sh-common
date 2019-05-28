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
    public function listAvailablePermissions(Request $request)
    {
        $permissions = config('permissions');
        return \Output::data($permissions)->json();
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
            ->where('model_id', $input['model_id'])
            ->where('model_type', $input['model_type'])
            ->delete();

        // create new permissions for each provided role
        if (is_array($input['roles'])) {
            foreach ($input['roles'] as $role_id) {
                DB::table('content_object_permissions')->insert([
                    'role_id' => $role_id,
                    'model_id' => $input['model_id'],
                    'model_type' => $input['model_type'],
                ]);
            }
        }

        return \Output::message('Permissions have been updated successfully')->json();

    }

}
