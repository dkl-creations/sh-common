<?php

namespace DklCreations\SHCommon\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class Logger
{

    private $startTime;

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $this->startTime = microtime(true);
        return $next($request);
    }

    /**
     * @param  \Illuminate\Http\Request  $request
     * @param  \Illuminate\Http\Response  $response
     */
    public function terminate($request, $response)
    {
        if (env('APP_LOGGER', false) && $request->method() != 'OPTIONS') {
            $endTime = microtime(true);
            $dataToLog  = "\n";
            $dataToLog .= 'URL: '    . $request->fullUrl() . "\n";
            $dataToLog .= 'Method: ' . $request->method() . "\n";
            $dataToLog .= 'Input: '  . $request->getContent() . "\n";
            $dataToLog .= 'Output: ' . $response->getContent() . "\n";
            $dataToLog .= 'Code: ' . $response->getStatusCode() . "\n";
            \Log::info($dataToLog);
        }
    }

}