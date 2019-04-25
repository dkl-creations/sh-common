<?php

namespace Lewisqic\SHCommon\Middleware;

use Closure;
use Illuminate\Support\Facades\Crypt;

class AuthIdentity
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

        if (!empty($request->header('x-sh-identity'))) {
            $timestamp = Crypt::decrypt($request->header('x-sh-identity'));
            if (is_int($timestamp) && $timestamp >= time()) {
                $is_authorized = true;
            }
        }

        if ($is_authorized == false) {
            return json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        return $next($request);
    }
}