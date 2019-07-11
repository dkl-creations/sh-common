<?php

namespace DklCreations\SHCommon\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class Logger
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

        \Log::debug('first!');

        return $next($request);
    }

    /**
     * @param  \Illuminate\Http\Request  $request
     * @param  \Illuminate\Http\Response  $response
     */
    public function terminate($request, $response)
    {

        \Log::debug('last!');

    }



}