<?php

namespace Lewisqic\SHCommon\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

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
        if (!$this->isCorsRequest($request)) {
            return $next($request);
        }
        if ($this->isPreflightRequest($request)) {
            $response = response('');
        } else {
            $response = $next($request);
        }
        $response->headers->set('Access-Control-Allow-Origin', '*');
        $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS');
        $response->headers->set('Access-Control-Allow-Headers', 'authorization');
        return $response;
    }

    /**
     * Check if request is preflight
     *
     * @param Request $request
     *
     * @return bool
     */
    public function isPreflightRequest(Request $request)
    {
        return $this->isCorsRequest($request)
            && $request->getMethod() === 'OPTIONS'
            && $request->headers->has('Access-Control-Request-Method');
    }

    /**
     * Check if request is cors request
     *
     * @param Request $request
     *
     * @return bool
     */
    public function isCorsRequest(Request $request)
    {
        return $request->headers->has('Origin') && !$this->isSameHost($request);
    }

    /**
     * Check if request is from same host
     *
     * @param Request $request
     *
     * @return bool
     */
    private function isSameHost(Request $request)
    {
        return $request->headers->get('Origin') === $request->getSchemeAndHttpHost();
    }

}