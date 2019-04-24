<?php

namespace Lewisqic\SHCommon\Helpers;

use Illuminate\Encryption\Encrypter;

class Identity
{

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

            //s($service);
            //s($token);

            $response = Api::post($service, 'v1/identity/cache/create', $user, [
                'headers' => [
                    'X-SH-Token' => $token
                ]
            ]);

        }

        die();

    }

}