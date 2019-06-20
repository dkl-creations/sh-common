<?php

namespace DklCreations\SHCommon\Middleware;

use Closure;
use Laravel\Lumen\Application as App;
use Illuminate\Encryption\Encrypter;
use DklCreations\SHCommon\Helpers\Identity;
use DklCreations\SHCommon\Helpers\Config;

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
    public function handle($request, Closure $next)
    {
        $is_authorized = false;
        $config_map = get_config_map();
        $crypt = new Encrypter($config_map['master_key'], 'AES-256-CBC');
        if (!empty($request->header('x-sh-token'))) {
            try {
                $token = $crypt->decrypt($request->header('x-sh-token'));
            } catch (\Exception $e) {
                fail('Invalid sh token provided.');
            }
            if ($request->header('referer') == $token['host'] && strtotime($token['expires_at']) >= time()) {
                Config::loadDatabaseCredentials();
                $is_authorized = true;
            }
        } elseif (!empty($request->header('authorization'))) {
            // allow admin token
            if (preg_match('/^Admin/', $request->header('authorization'))) {
                try {
                    $data = $crypt->decrypt(preg_replace('/^Admin\s+/', '', $request->header('authorization')));
                    if (
                        !isset($data['code']) ||
                        $data['code'] != 'eoWE2RzwUhd8U3tzfOzQftqJ6Z9E4g1i' ||
                        !isset($data['expires_at']) ||
                        strtotime($data['expires_at']) < time()
                    ) {
                        fail('Invalid authorization token provided.');
                    }
                    return $next($request);
                } catch (\Exception $e) {
                    fail('Invalid authorization token provided.');
                }
            }
            $token = get_current_token();
            try {
                $user_id = $crypt->decrypt($token);
            } catch (\Exception $e) {
                fail('Invalid authorization token provided.');
            }
            if (is_int($user_id)) {
                $cached_data = Identity::getUserCache($token, $user_id);
                if ( $cached_data && strtotime($cached_data['expires_at']) >= time() ) {
                    $is_authorized = true;

                    $this->app->singleton('user', function ($app) use ($cached_data) {
                        return $cached_data['user'];
                    });
                    $this->app->singleton('org', function ($app) use ($cached_data) {
                        return isset($cached_data['org']) ? $cached_data['org'] : null;
                    });
                    $this->app->singleton('orgs', function ($app) use ($cached_data) {
                        return $cached_data['orgs'];
                    });
                    $this->app->singleton('role', function ($app) use ($cached_data) {
                        return isset($cached_data['role']) ? $cached_data['role'] : null;
                    });
                    $this->app->singleton('roles', function ($app) use ($cached_data) {
                        return isset($cached_data['roles']) ? $cached_data['roles'] : [];
                    });
                    $this->app->singleton('permissions', function ($app) use ($cached_data) {
                        return isset($cached_data['permissions']) ? $cached_data['permissions'] : [];
                    });

                    if (isset($cached_data['org'])) {
                        Config::loadDatabaseCredentials($cached_data['org']);
                    }
                }
            }
        }

        if ($is_authorized == false) {
            return \Output::code(401)->message('Unauthorized')->json();
        }

        return $next($request);
    }
}