<?php

namespace Lewisqic\SHCommon\Helpers;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
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
        $config_map = get_config_map();
        $crypt = new Encrypter($config_map['master_key'], 'AES-256-CBC');
        $token = $crypt->encrypt($id);
        return $token;
    }

    /**
     * Get a users data from the cached filed
     *
     * @param $id
     */
    public static function getUserCache($client_token, $id)
    {
        $filename = md5($id) . '-' . md5($client_token);
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
     * @param $id
     * @param $data
     */
    public static function createUserCache($id, $data)
    {
        $client_token = $data['token'];
        $filename = md5($id) . '-' . md5($client_token);
        $token_data = $data['data'];
        $token_data['expires_at'] = date('Y-m-d H:i:s', strtotime('+1 year'));
        $contents = Crypt::encrypt(json_encode($token_data));
        self::deleteUserCache($id);
        Storage::put('identity/' . $filename, $contents);
    }

    /**
     * Update the cached identity record for a user
     *
     * @param $id
     * @param $data
     */
    public static function updateUserCache($id, $data)
    {
        $new_data = $data['data'];
        $client_token = $data['token'];
        $old_cache = self::getUserCache($client_token, $id);
        $new_cache = array_merge($old_cache, $new_data);
        $new_cache['expires_at'] = date('Y-m-d H:i:s', strtotime('+1 year'));
        $filename = md5($id) . '-' . md5($client_token);
        $contents = Crypt::encrypt(json_encode($new_cache));
        Storage::put('identity/' . $filename, $contents);
    }

    /**
     * Delete the user cached data
     *
     * @param $data
     */
    public static function deleteUserCache($id)
    {
        $path = storage_path('app/identity/' . md5($id) . '-*');
        File::delete(File::glob($path));
    }

    /**
     * Create the token cache on each MS for a given user
     *
     * @param $user
     */
    public static function createCacheOnAllServices($data)
    {
        self::runOnAllServices('post', $data['user_id'], $data);
    }

    /**
     * Update the token cache on each MS for a given user
     *
     * @param $user
     */
    public static function updateCacheOnAllServices($data)
    {
        self::runOnAllServices('put', $data['user_id'], $data);
    }

    /**
     * Delete token cache files on each MS for a given user
     *
     * @param $user
     */
    public static function deleteCacheFromAllServices($user_id)
    {
        self::runOnAllServices('delete', $user_id, []);
    }

    /**
     * Run specified call on all services via API call
     *
     * @param $method
     * @param $user_id
     * @param $data
     */
    private static function runOnAllServices($method, $user_id, $cache_data)
    {
        $config_map = get_config_map();
        foreach ($config_map['keys'] as $service => $key) {
            $crypt = new Encrypter($key, 'AES-256-CBC');
            $identity_token = $crypt->encrypt(strtotime('+5 minutes'));
            $response = Api::{$method}($service, 'v1/identity/cache/' . $user_id, $cache_data, [
                'headers' => [
                    'X-SH-Identity' => $identity_token
                ]
            ]);
        }
    }

}