<?php

namespace Lewisqic\SHCommon\Controllers;

use Lewisqic\SHCommon\Helpers\Identity;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Http\Request;

class IdentityController extends Controller
{

    public function createCache(Request $request)
    {
        $data = $request->all();
        if (!isset($data['id'])) {
            abort(403, 'Missing required user id');
        }

        $filename = md5($data['id']);
        $contents = Crypt::encrypt(json_encode($data));

        Storage::delete('identity/' . $filename);
        Storage::put('identity/' . $filename, $contents);

    }

}
