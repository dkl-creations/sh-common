<?php

namespace Lewisqic\SHCommon\Middleware;

use Closure;
use Illuminate\Contracts\Auth\Factory as Auth;
use Illuminate\Auth\GenericUser;
use Illuminate\Encryption\Encrypter;

class AuthToken
{

    /**
     * The authentication guard factory instance.
     *
     * @var \Illuminate\Contracts\Auth\Factory
     */
    protected $auth;

    /**
     * Create a new middleware instance.
     *
     * @param  \Illuminate\Contracts\Auth\Factory  $auth
     * @return void
     */
    public function __construct(Auth $auth)
    {
        $this->auth = $auth;
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

        if (!empty($request->header('authorization'))) {

            $user = null;
            
            sd($request->header('authorization'));

            $config_map = include(base_path('../config_map.php'));
            $crypt = new Encrypter($config_map['master_key'], 'AES-256-CBC');
            $user_id = $crypt->decrypt($request->header('authorization'));
            
            sd($user_id);

            $this->auth->viaRequest('api', function ($request) use ($user) {
                return new GenericUser($user);
            });

        }

        if ($is_authorized == false) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        return $next($request);
    }
}