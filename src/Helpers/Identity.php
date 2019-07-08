<?php

namespace DklCreations\SHCommon\Helpers;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Encryption\Encrypter;
use Illuminate\Support\Facades\Crypt;
use DklCreations\SHCommon\Helpers\Api;

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
        if (Storage::exists('identity/user/' . $filename)) {
            $contents = Storage::get('identity/user/' . $filename);
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
        Storage::put('identity/user/' . $filename, $contents);
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
        if (empty($old_cache)) {
            return;
        }
        $new_cache = array_merge($old_cache, $new_data);
        $new_cache['expires_at'] = date('Y-m-d H:i:s', strtotime('+1 year'));
        $filename = md5($id) . '-' . md5($client_token);
        $contents = Crypt::encrypt(json_encode($new_cache));
        Storage::put('identity/user/' . $filename, $contents);
    }

    /**
     * Delete the user cached data
     *
     * @param $data
     */
    public static function deleteUserCache($id)
    {
        $path = storage_path('app/identity/user/' . md5($id) . '-*');
        File::delete(File::glob($path));
    }

    /**
     * Create the token cache on each MS for a given user
     *
     * @param $user
     */
    public static function createCacheOnAllServices($data)
    {
        self::runCacheOnAllServices('post', $data['user_id'], $data);
    }

    /**
     * Update the token cache on each MS for a given user
     *
     * @param $user
     */
    public static function updateCacheOnAllServices($data)
    {
        self::runCacheOnAllServices('put', $data['user_id'], $data);
    }

    /**
     * Delete token cache files on each MS for a given user
     *
     * @param $user
     */
    public static function deleteCacheOnAllServices($user_id)
    {
        self::runCacheOnAllServices('delete', $user_id, []);
    }

    /**
     * Run specified call on all services via API call
     *
     * @param $method
     * @param $user_id
     * @param $data
     */
    private static function runCacheOnAllServices($method, $user_id, $cache_data)
    {
        $api = app(Api::class);
        $config_map = get_config_map();
        foreach ($config_map['keys'] as $service => $key) {
            $crypt = new Encrypter($key, 'AES-256-CBC');
            $timestamp_token = $crypt->encrypt(strtotime('+5 minutes'));
            $response = $api->{$method}($service, 'v1/identity/user-cache/' . $user_id, $cache_data, [
                'headers' => [
                    'X-SH-Timestamp' => $timestamp_token
                ]
            ]);
        }
    }







    /**
     * Get a users data from the cached filed
     *
     * @param $id
     */
    public static function getOrgConfig($client_token, $id)
    {
        $filename = md5($id) . '-' . md5($client_token);
        if (Storage::exists('identity/org/' . $filename)) {
            $contents = Storage::get('identity/org/' . $filename);
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
    public static function createOrgConfig($id, $data)
    {
        $token_data = $data['data'];
        $token_data['expires_at'] = date('Y-m-d H:i:s', strtotime('+1 year'));
        $contents = Crypt::encrypt(json_encode($token_data));
        self::deleteOrgConfig($id);
        Storage::put('identity/org/' . md5($id), $contents);
    }

    /**
     * Update the cached identity record for a user
     *
     * @param $id
     * @param $data
     */
    public static function updateOrgConfig($id, $data)
    {
        $new_data = $data['data'];
        $client_token = $data['token'];
        $old_cache = self::getOrgConfig($client_token, $id);
        if (empty($old_cache)) {
            return;
        }
        $new_cache = array_merge($old_cache, $new_data);
        $new_cache['expires_at'] = date('Y-m-d H:i:s', strtotime('+1 year'));
        $filename = md5($id) . '-' . md5($client_token);
        $contents = Crypt::encrypt(json_encode($new_cache));
        Storage::put('identity/org/' . $filename, $contents);
    }

    /**
     * Delete the user cached data
     *
     * @param $data
     */
    public static function deleteOrgConfig($id)
    {
        $path = storage_path('app/identity/org/' . md5($id));
        File::delete(File::glob($path));
    }

    /**
     * Create the token cache on each MS for a given user
     *
     * @param $user
     */
    public static function createConfigOnAllServices($data)
    {
        self::runConfigOnAllServices('post', $data['org_id'], $data);
    }

    /**
     * Update the token cache on each MS for a given user
     *
     * @param $user
     */
    public static function updateConfigOnAllServices($data)
    {
        self::runConfigOnAllServices('put', $data['org_id'], $data);
    }

    /**
     * Delete token cache files on each MS for a given user
     *
     * @param $user
     */
    public static function deleteConfigOnAllServices($org_id)
    {
        self::runConfigOnAllServices('delete', $org_id, []);
    }

    /**
     * Run specified call on all services via API call
     *
     * @param $method
     * @param $user_id
     * @param $data
     */
    private static function runConfigOnAllServices($method, $org_id, $cache_data)
    {
        $api = app(Api::class);
        $config_map = get_config_map();
        foreach ($config_map['keys'] as $service => $key) {
            $crypt = new Encrypter($key, 'AES-256-CBC');
            $timestamp_token = $crypt->encrypt(strtotime('+5 minutes'));
            $response = $api->{$method}($service, 'v1/identity/org-config/' . $org_id, $cache_data, [
                'headers' => [
                    'X-SH-Timestamp' => $timestamp_token
                ]
            ]);
        }
    }

}