<?php

namespace DklCreations\SHCommon\Controllers;

use DklCreations\SHCommon\Helpers\Identity;
use Illuminate\Http\Request;

class IdentityController extends BaseController
{

    /**
     * Create user data cache record
     *
     * @param Request $request
     * @param         $id
     *
     * @return json
     */
    public function createUserCache(Request $request, $id)
    {
        $data = $request->all();
        Identity::createUserCache($id, $data);
        return \Output::message('Cache created successfully')->json();
    }

    /**
     * Update user data cache record
     *
     * @param Request $request
     * @param         $id
     *
     * @return json
     */
    public function updateUserCache(Request $request, $id)
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
    public function deleteUserCache(Request $request, $id)
    {
        Identity::deleteUserCache($id);
        return \Output::message('Cache deleted successfully')->json();
    }

    /**
     * Create user data cache record
     *
     * @param Request $request
     * @param         $id
     *
     * @return json
     */
    public function createOrgConfig(Request $request, $id)
    {
        $data = $request->all();
        Identity::createOrgConfig($id, $data);
        return \Output::message('Config created successfully')->json();
    }

    /**
     * Update user data cache record
     *
     * @param Request $request
     * @param         $id
     *
     * @return json
     */
    public function updateOrgConfig(Request $request, $id)
    {
        $data = $request->all();
        Identity::updateOrgConfig($id, $data);
        return \Output::message('Config updated successfully')->json();
    }

    /**
     * Delete user data cache record
     *
     * @param Request $request
     * @param         $id
     *
     * @return json
     */
    public function deleteOrgConfig(Request $request, $id)
    {
        Identity::deleteOrgConfig($id);
        return \Output::message('Config deleted successfully')->json();
    }

}
