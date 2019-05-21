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
    public function createCache(Request $request, $id)
    {
        $data = $request->all();
        Identity::createUserCache($id, $data);
        return \Output::message('che created successfully')->json();
    }

    /**
     * Update user data cache record
     *
     * @param Request $request
     * @param         $id
     *
     * @return json
     */
    public function updateCache(Request $request, $id)
    {
        $data = $request->all();
        Identity::updateUserCache($id, $data);
        return \Output::message('Cache updated successfully')->json();
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
        Identity::deleteUserCache($id);
        return \Output::message('Cache deleted successfully')->json();
    }

}
