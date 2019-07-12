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
     *
     * @return string
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
     * @param $client_token
     * @param $id
     *
     * @return string|null
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
        if (isset($data['user_ids']) && is_array($data['user_ids'])) {
            $user_ids = $data['user_ids'];
        } else {
            $user_ids = [$id];
        }
        $new_data = $data['data'];
        $client_token = $data['token'];
        foreach ($user_ids as $id) {
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
    }

    /**
     * Delete the user cached data
     *
     * @param $id
     */
    public static function deleteUserCache($id)
    {
        $path = storage_path('app/identity/user/' . md5($id) . '-*');
        File::delete(File::glob($path));
    }

    /**
     * Create the token cache on each MS for a given user
     *
     * @param $data
     *
     * @return array
     */
    public static function createCacheOnAllServices($data)
    {
        return self::runCacheOnAllServices('post', $data['user_id'], $data);
    }

    /**
     * Update the token cache on each MS for a given user
     *
     * @param $data
     *
     * @return array
     */
    public static function updateCacheOnAllServices($data)
    {
        return self::runCacheOnAllServices('put', $data['user_id'], $data);
    }

    /**
     * Delete token cache files on each MS for a given user
     *
     * @param $user_id
     *
     * @return array
     */
    public static function deleteCacheOnAllServices($user_id)
    {
        return self::runCacheOnAllServices('delete', $user_id, []);
    }

    /**
     * Run specified call on all services via API call
     *
     * @param $method
     * @param $user_id
     * @param $data
     *
     * @return array
     */
    private static function runCacheOnAllServices($method, $user_id, $cache_data)
    {
        $api = app(Api::class);
        $config_map = get_config_map();
        if (is_array($user_id)) {
            $cache_data['user_ids'] = $user_id;
            $user_id = 0;
        }
        $failed_services = [];
        foreach ($config_map['keys'] as $service => $key) {
            try {
                $crypt = new Encrypter($key, 'AES-256-CBC');
                $timestamp_token = $crypt->encrypt(strtotime('+5 minutes'));
                $response = $api->{$method}($service, 'v1/identity/user-cache/' . $user_id, $cache_data, [
                    'headers' => [
                        'X-SH-Timestamp' => $timestamp_token
                    ]
                ]);
            } catch (\Exception $e) {
                $failed_services[] = $service;
            }
        }
        return $failed_services;
    }

    /**
     * Get orgs data from the cached filed
     *
     * @param $id
     */
    public static function getOrgConfig($id)
    {
        $filename = md5($id);
        if (Storage::exists('identity/org/' . $filename)) {
            $contents = Storage::get('identity/org/' . $filename);
            $data = json_decode(Crypt::decrypt($contents), true);
            if (is_array($data)) {
                if (isset($data['db_databases']) && empty($data['db_databases'])) {
                    $data['db_databases'] = [];
                }
                if (isset($data['services']) && empty($data['services'])) {
                    $data['services'] = [];
                }
                if (isset($data['ui']) && empty($data['ui'])) {
                    $data['ui'] = [];
                }
                return $data;
            }
        }
        return null;
    }

    /**
     * Create the cached config record for a org
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
     * Update the cached config record for a org
     *
     * @param $id
     * @param $data
     */
    public static function updateOrgConfig($id, $data)
    {
        $new_data = $data['data'];
        $old_config = self::getOrgConfig($id);
        if (empty($old_config)) {
            $old_config = self::createOrgConfig($id, $new_data);
        }
        $new_cache = array_merge($old_config, $new_data);
        $new_cache['expires_at'] = date('Y-m-d H:i:s', strtotime('+1 year'));
        $contents = Crypt::encrypt(json_encode($new_cache));
        Storage::put('identity/org/' . md5($id), $contents);
    }

    /**
     * Delete the org cached data
     *
     * @param $data
     */
    public static function deleteOrgConfig($id)
    {
        $path = storage_path('app/identity/org/' . md5($id));
        File::delete(File::glob($path));
    }

    /**
     * Create the config cache on each MS for a given org
     *
     * @param $data
     */
    public static function createConfigOnAllServices($data)
    {
        self::runConfigOnAllServices('post', $data['org_id'], $data);
    }

    /**
     * Update the config cache on each MS for a given org
     *
     * @param $data
     */
    public static function updateConfigOnAllServices($data)
    {
        self::runConfigOnAllServices('put', $data['org_id'], $data);
    }

    /**
     * Delete token cache files on each MS for a given org
     *
     * @param $org_id
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