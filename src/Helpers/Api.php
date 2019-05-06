<?php

namespace Lewisqic\SHCommon\Helpers;

use Illuminate\Encryption\Encrypter;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;

class Api
{

    /**
     * Magic method that maps the REST verb to the proper guzzle call
     *
     * @param $method
     * @param $args
     * @return mixed
     */
    public static function __callStatic($method, $args) {
        
        $request = app('request');
        $method = strtoupper($method);
        if (empty($args[0] || empty($args[1]))) {
            abort(403, 'Missing required API parameters');
        }
        $url = api_url($args[0], $args[1]);
        $data = null;

        try {
            $http = new Client;

            $request_data = ['http_errors' => false];
            if (isset($args[2]) && !empty($args[2])) {
                if ( $method == 'GET' ) {
                    $request_data['query'] = $args[2];
                } else {
                    $request_data['form_params'] = $args[2];
                }
            }
            if (isset($args[3])) {
                $request_data = array_merge($request_data, $args[3]);
            }

            if (!isset($request_data['headers']['Authorization'])) {

                if (!empty($request->header('authorization'))) {
                    $request_data['headers']['Authorization'] = $request->header('authorization');
                    $request_data['headers']['Accept'] = $request->header('accept');
                } else {
                    $config_map = get_config_map();
                    $crypt = new Encrypter($config_map['master_key'], 'AES-256-CBC');
                    $token = $crypt->encrypt([
                        'host'    => $_SERVER['HTTP_HOST'] ?? '',
                        'expires_at' => date('Y-m-d H:i:s', strtotime('+5 minutes')),
                    ]);
                    $request_data['headers']['Referer'] = $_SERVER['HTTP_HOST'] ?? '';
                    $request_data['headers']['X-SH-Token'] = $token;
                }

            }

            $response = $http->request($method, $url, $request_data);
            $data = json_decode((string)$response->getBody(), true);
            if (isset($data['success']) && $data['success'] == false) {
                abort(!empty($data['code']) ? $data['code'] : 403, $data['message']);
            }
        } catch (BadResponseException $e) {
            abort($e->getStatusCode(), $e->getMessage());
        }

        return $data;

    }

}