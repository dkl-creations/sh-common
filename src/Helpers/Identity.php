<?php

namespace Lewisqic\SHCommon\Helpers;

use Illuminate\Support\Facades\Storage;
use Illuminate\Encryption\Encrypter;
use Illuminate\Support\Facades\Crypt;

class Identity
{

    /**
     * Generate a hashed token based on user id
     *
     * @param $id
     */
    public static function getPublicToken($id)
    {
        $config_map = include(base_path('../config_map.php'));
        $crypt = new Encrypter($config_map['master_key'], 'AES-256-CBC');
        $token = $crypt->encrypt($id);
        return $token;
    }

    /**
     * Get a users data from the cached filed
     *
     * @param $id
     */
    public static function getUserCache($id)
    {
        $filename = md5($id);
        if (Storage::exists('identity/' . $filename)) {
            $contents = Storage::get('identity/' . $filename);
            $data = json_decode(Crypt::decrypt($contents), true);
            if (is_array($data)) {
                return $data;
            }
        }
        return null;
    }

    /**
     * Create the cached identity record for a user
     *
     * @param $data
     */
    public static function createUserCache($id, $data)
    {
        $filename = md5($id);
        $contents = Crypt::encrypt(json_encode($data));
        Storage::delete('identity/' . $filename);
        Storage::put('identity/' . $filename, $contents);
    }

    /**
     * Delete the user cached data
     *
     * @param $data
     */
    public static function deleteUserCache($id)
    {
        $filename = md5($id);
        Storage::delete('identity/' . $filename);
    }

    /**
     * Create the token cache on each MS for a given user
     *
     * @param $user
     */
    public static function createCacheForAllServices($user)
    {
        $config_map = include(base_path('../config_map.php'));
        foreach ($config_map['services'] as $service => $data) {
            $crypt = new Encrypter($data['key'], 'AES-256-CBC');
            $token = $crypt->encrypt(time());
            $response = Api::post($service, 'v1/identity/cache/' . $user['id'], $user, [
                'headers' => [
                    'X-SH-Identity' => $token
                ]
            ]);
        }
    }

}