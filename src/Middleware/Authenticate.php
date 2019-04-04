<?php

namespace Lewisqic\SHCommon\Middleware;

use Closure;
use Illuminate\Contracts\Auth\Factory as Auth;
use Illuminate\Auth\GenericUser;
use Illuminate\Encryption\Encrypter;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;
use Dotenv\Dotenv;

class Authenticate
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
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        $is_authorized = false;
        
        if (!empty($request->header('x-sh-token'))) {

            $dotenv = Dotenv::create(base_path('../identity'));
            $identity_env = $dotenv->load();
            $crypt = new Encrypter($identity_env['APP_KEY'], 'AES-256-CBC');
            $token = $crypt->decrypt($request->header('x-sh-token'));
            if ( $request->header('referer') == $token['host'] && strtotime($token['expires']) >= time() ) {
                $is_authorized = true;
                $this->auth->viaRequest('api', function ($request) {
                    return null;
                });
            }
            
        } else {

            if (env('APP_NAME') == 'Identity') {
                if (!$this->auth->guard($guard)->guest()) {
                    $is_authorized = true;
                }
            } else {
                
                try {
                    $http = new Client;
                    $response = $http->get(api_url('v1/user', 'identity'), [
                        'headers' => [
                            'Accept' => $request->header('accept'),
                            'Authorization' => $request->header('authorization'),
                        ]
                    ]);
                    $user = json_decode((string) $response->getBody(), true);
                    if ( !empty($user['id']) ) {
                        $is_authorized = true;
                        $this->auth->viaRequest('api', function ($request) use ($user) {
                            return new GenericUser($user);
                        });
                    }
                } catch (BadResponseException $e) {
                    // do nothing, as we aren't authorized
                }

            }

        }

        if ($is_authorized == false) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        return $next($request);
    }
}