<?php

namespace Lewisqic\SHCommon\Middleware;

use Closure;
use Laravel\Lumen\Application as App;
use Illuminate\Encryption\Encrypter;
use Lewisqic\SHCommon\Helpers\Identity;
use Lewisqic\SHCommon\Helpers\Config;

class AuthToken
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
    public function handle($request, Closure $next, $guard = null)
    {
        $is_authorized = false;

        $user = null;
        $org = null;
        $config_map = get_config_map();
        $crypt = new Encrypter($config_map['master_key'], 'AES-256-CBC');
        if (!empty($request->header('x-sh-token'))) {
            $token = $crypt->decrypt($request->header('x-sh-token'));
            if ($request->header('referer') == $token['host'] && strtotime($token['expires_at']) >= time()) {
                Config::loadDatabaseCredentials();
                $is_authorized = true;
            }
        } elseif (!empty($request->header('authorization'))) {
            $token = get_current_token();
            $user_id = $crypt->decrypt($token);
            if (is_int($user_id)) {
                $cached_data = Identity::getUserCache($token, $user_id);
                if ( $cached_data && strtotime($cached_data['expires_at']) >= time() ) {
                    $user = $cached_data['user'];
                    $org = $cached_data['org'];
                    Config::loadDatabaseCredentials($org);
                    $is_authorized = true;
                }
            }
        }
        $this->app->singleton('user', function ($app) use ($user) {
            return $user;
        });
        $this->app->singleton('org', function ($app) use ($org) {
            return $org;
        });

        if ($is_authorized == false) {
            return \Output::code(401)->message('Unauthorized')->json();
        }

        return $next($request);
    }
}