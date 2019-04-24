<?php

namespace Lewisqic\SHCommon\Middleware;

use Closure;

class BeforeCors
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
        if ($request->getMethod() == 'OPTIONS') {
            $response = response('');
        } else {
            $response = $next($request);
        }
        $response->headers->set('Access-Control-Allow-Origin', '*');
        $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS');
        $response->headers->set('Access-Control-Allow-Headers', 'x-sh-token');
        return $response;
    }
}