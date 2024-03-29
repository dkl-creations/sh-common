<?php

namespace DklCreations\SHCommon\Middleware;

use Closure;
use DklCreations\SHCommon\Helpers\Identity;
use DklCreations\SHCommon\Helpers\Config;

class CheckPermissions
{

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  $proxy
     * @return mixed
     */
    public function handle($request, Closure $next, $proxy = false)
    {
        if (
            (isset(data('user')['super_admin_enabled']) && data('user')['super_admin_enabled']) ||
            preg_match('/^Admin/', $request->header('authorization'))
        ) {
            return $next($request);
        }

        $has_permission = false;
        $user_permissions = data('permissions');

        // remove version prefix
        $path = preg_replace('/\/v\d+\//', '', $request->getPathInfo());
        // replace digits with id parameter
        $path = preg_replace('/\/\d+/', '/{id}', $path);

        // if proxy is enabled, replace string in path now
        if ($proxy && preg_match('/=/', $proxy)) {
            $proxy_arr = explode('=', $proxy);
            $path = str_replace($proxy_arr[0], $proxy_arr[1], $path);
        }

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
