<?php

namespace Lewisqic\SHCommon\Controllers;

use Lewisqic\SHCommon\Helpers\Identity;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class IdentityController extends Controller
{

    /**
     * Create user data cache record
     *
     * @param Request $request
     * @param         $id
     *
     * @return json
     */
    public function createCache(Request $request, $id = null)
    {
        $data = $request->all();
        if (empty($id)) {
            abort(403, 'Missing required user id');
        }
        Identity::createUserCache($id, $data);
        return response()->json(['success' => true]);
    }

    /**
     * Delete user data cache record
     *
     * @param Request $request
     * @param         $id
     *
     * @return json
     */
    public function deleteCache(Request $request, $id)
    {
        if (empty($id)) {
            abort(403, 'Missing required user id');
        }
        Identity::deleteUserCache($id);
        return response()->json(['success' => true]);
    }

}