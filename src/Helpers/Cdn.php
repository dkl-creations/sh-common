<?php

namespace DklCreations\SHCommon\Helpers;

use Illuminate\Support\Facades\Storage;

class Cdn
{

    /**
     * The org ID to use for connection
     *
     * @var null
     */
    protected static $orgId = null;

    /**
     * Upload a file to a location on the server
     *
     * @param $path
     * @param $contents
     *
     * @return bool
     */
    public static function uploadFile($path, $contents)
    {
        static::setConfig();
        return Storage::disk('sftp')->put(static::preparePath($path), $contents);
    }

    public static function deleteFile($path)
    {
        static::setConfig();
        return Storage::disk('sftp')->delete(static::preparePath($path));
    }

    public static function createDir($path)
    {
        static::setConfig();
        Storage::disk('sftp')->makeDirectory($path);
    }

    public static function deleteDir($path)
    {
        static::setConfig();
        Storage::disk('sftp')->deleteDirectory($path);
    }

    public static function listFiles($path)
    {
        static::setConfig();
        $files = Storage::disk('sftp')->files($path);
    }

    public static function listDirs($path, $recursive = false)
    {
        static::setConfig();
        $directories = Storage::disk('sftp')->directories($path);
        if ($recursive) {
            $directories = Storage::disk('sftp')->allDirectories($path);
        }
    }

    /**
     * Set the org to be used for all operations
     *
     * @param $org_id
     *
     * @return Cdn
     */
    public static function org($org_id)
    {
        self::$orgId = $org_id;
        return new static;
    }

    /**
     * Set our sftp connection credentials
     */
    protected static function setConfig()
    {
        if (self::$orgId != null) {
            $config = Identity::getOrgConfig(self::$orgId);
        } else {
            $config = data('org_config');
        }
        if (empty($config['sftp_username'])) {
            fail('Org does not have valid SFTP credentials set');
        }
        config(['filesystems.disks.sftp' => [
            'driver' => 'sftp',
            'host' => $config['sftp_host'],
            'username' => $config['sftp_username'],
            'password' => $config['sftp_password'],
            'cache' => [
                'store' => 'memcached',
                'expire' => 600,
                'prefix' => 'lumen-sftp',
            ],
        ]]);
    }

    /**
     * Prepare the path for the cdn filesystem
     *
     * @param $path
     *
     * @return string
     */
    protected static function preparePath($path)
    {
        return 'cdn/' . preg_replace('/^\//', '', $path);
    }


}