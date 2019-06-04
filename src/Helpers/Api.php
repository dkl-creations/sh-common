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
            fail('Missing required API parameters');
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
            if (!empty($data['code']) && preg_match('/^4/', $data['code'])) {
                fail($data['message'], $data['code']);
            }
        } catch (BadResponseException $e) {
            fail($e->getMessage(), $e->getStatusCode());
        }

        return $data;

    }

    /**
     * Make REST HTTP call
     *
     * @param       $method
     * @param       $service
     * @param       $route
     * @param array $params
     * @param array $options
     *
     * @return mixed|null
     */
    protected static function makeCall($method, $service, $route, $params = [], $options = [])
    {

        $request = app('request');
        $url = api_url($service, $route);
        $data = null;

        try {
            $http = new Client;

            $request_data = ['http_errors' => false];
            if (isset($params) && !empty($params)) {
                if ( $method == 'GET' ) {
                    $request_data['query'] = $params;
                } else {
                    $request_data['form_params'] = $params;
                }
            }
            if (isset($options)) {
                $request_data = array_merge($request_data, $options);
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
            if (!empty($data['code']) && preg_match('/^4/', $data['code'])) {
                fail($data['message'], $data['code']);
            }
        } catch (BadResponseException $e) {
            fail($e->getMessage(), $e->getStatusCode());
        }

        return $data;

    }

    /*public static function get($service, $route, $params = [], $options = [])
    {
        return static::makeCall('GET', $service, $route, $params, $options);
    }

    public static function post($service, $route, $params = [], $options = [])
    {
        return static::makeCall('POST', $service, $route, $params, $options);
    }

    public static function put($service, $route, $params = [], $options = [])
    {
        return static::makeCall('PUT', $service, $route, $params, $options);
    }

    public static function patch($service, $route, $params = [], $options = [])
    {
        return static::makeCall('PATCH', $service, $route, $params, $options);
    }

    public static function delete($service, $route, $params = [], $options = [])
    {
        return static::makeCall('DELETE', $service, $route, $params, $options);
    }*/

}