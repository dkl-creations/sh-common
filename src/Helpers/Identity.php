<?php

namespace Lewisqic\SHCommon\Helpers;

use Illuminate\Support\Facades\Storage;
use Illuminate\Encryption\Encrypter;

class Identity
{

    /**
     * Generate a hashed token based on user id
     *
     * @param $user_id
     */
    public static function getUserToken($user_id)
    {
        $config_map = include(base_path('../config_map.php'));
        $crypt = new Encrypter($config_map['master_key'], 'AES-256-CBC');
        $token = $crypt->encrypt($user_id);
        return $token;
    }

    /**
     * Create the token cache on each MS for a given user
     *
     * @param $user
     */
    public static function createTokenCache($user)
    {
        $config_map = include(base_path('../config_map.php'));

        foreach ($config_map['services'] as $service => $data) {

            $crypt = new Encrypter($data['key'], 'AES-256-CBC');
            $token = $crypt->encrypt(time());
            $response = Api::post($service, 'v1/identity/cache/create', $user, [
                'headers' => [
                    'X-SH-Cache-Token' => $token
                ]
            ]);

        }

        die();

    }

}