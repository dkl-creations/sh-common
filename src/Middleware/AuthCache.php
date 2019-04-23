<?php

namespace Lewisqic\SHCommon\Middleware;

use Closure;
use Illuminate\Encryption\Encrypter;

class AuthCache
{

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $is_authorized = false;

        if (!empty($request->header('x-sh-token'))) {
            $crypt = new Encrypter(env('APP_KEY'), 'AES-256-CBC');
            $timestamp = $crypt->decrypt($request->header('x-sh-token'));
            if ( is_int($timestamp) && time() <= $timestamp + 60 ) {
                $is_authorized = true;
            }
        }

        if ($is_authorized == false) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        return $next($request);
    }
}