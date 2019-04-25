<?php

namespace Lewisqic\SHCommon\Middleware;

use Closure;
use Laravel\Lumen\Application as App;
use Illuminate\Auth\GenericUser;
use Illuminate\Encryption\Encrypter;
use Lewisqic\SHCommon\Helpers\Identity;

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
        $config_map = include(base_path('../config_map.php'));
        $crypt = new Encrypter($config_map['master_key'], 'AES-256-CBC');
        if (!empty($request->header('x-sh-token'))) {
            $token = $crypt->decrypt($request->header('x-sh-token'));
            if ($request->header('referer') == $token['host'] && strtotime($token['expires']) >= time()) {
                $is_authorized = true;
            }
        } elseif (!empty($request->header('authorization'))) {
            $token = preg_replace('/^Token\s/', '', $request->header('authorization'));
            $user_id = $crypt->decrypt($token);
            if (is_int($user_id)) {
                $cached_data = Identity::getUserCache($token, $user_id);
                if ( $cached_data && strtotime($cached_data['expires_at']) > time() ) {
                    $user = $cached_data['user'];
                    $is_authorized = true;
                }
            }
        }
        $this->app->singleton('user', function ($app) use ($user) {
            return $user;
        });

        if ($is_authorized == false) {
            return json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        return $next($request);
    }
}