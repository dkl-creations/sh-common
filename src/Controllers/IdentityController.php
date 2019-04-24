<?php

namespace Lewisqic\SHCommon\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class IdentityController extends Controller
{

    public function createCache(Request $request)
    {

        \Log::debug($_POST);

        // delete old cached files
        // create new cache file for the given user
        // file username is an md5 hash of user ID

        //Storage::delete('file.jpg');
        //Storage::put('identity/test.txt', 'foobar');

    }

}
