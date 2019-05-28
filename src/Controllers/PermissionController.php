<?php

namespace Lewisqic\SHCommon\Controllers;

use Illuminate\Http\Request;

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
        
        sd($request->all());
        
    }

}
