<?php

namespace Lewisqic\SHCommon\Middleware;

use Closure;
use Laravel\Lumen\Application as App;
use Lewisqic\SHCommon\Helpers\Identity;
use Lewisqic\SHCommon\Helpers\Config;

class CheckPermissions
{

    /**
     * The lumen application
     *
     * @var \Laravel\Lumen\Application
     */
    protected $app;

    /**
     * Create a new middleware instance.
     *
     * @param  \Laravel\Lumen\Application  $app
     * @return void
     */
    public function __construct(App $app)
    {
        $this->app = $app;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $has_permission = false;

        $permissions = app('permissions');
        
        sd($permissions);

        if ($has_permission == false) {
            return \Output::code(403)->message('Insufficient permissions')->json();
        }

        return $next($request);
    }
}