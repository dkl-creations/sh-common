<?php

namespace Lewisqic\SHCommon\Middleware;

use Closure;
use Lewisqic\SHCommon\Helpers\Identity;
use Lewisqic\SHCommon\Helpers\Config;

class CheckPermissions
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
        if (!empty(app('user')) && app('user')['is_super_admin']) {
            return $next($request);
        }

        $has_permission = false;
        $user_permissions = app('permissions');

        // remove version prefix
        $path = preg_replace('/\/v\d+\//', '', $request->getPathInfo());
        // replace digits with id parameter
        $path = preg_replace('/\/\d+/', '/{id}', $path);

        // build permissions string
        $permission_string = strtolower($request->getMethod()) . '@' . $path;

        // check if permission string exists in users permissions array
        if (in_array($permission_string, $user_permissions)) {
            $has_permission = true;
        }

        if ($has_permission == false) {
            return \Output::code(403)->message('Insufficient permissions')->json();
        }

        return $next($request);
    }
}