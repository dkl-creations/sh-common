<?php

namespace DklCreations\SHCommon\Helpers;

use Illuminate\Support\Facades\Storage;

class Cloud
{

    /**
     * The org ID to use for connection
     *
     * @var null
     */
    protected static $orgId = null;

    /**
     * Get the file contents of a file on the server
     *
     * @param $path
     *
     * @return array
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public static function getFile($path)
    {
        static::setConfig();
        $contents = Storage::disk('sftp')->get(static::preparePath($path));
        $size = Storage::disk('sftp')->size(static::preparePath($path));

        $ext = pathinfo(basename($path), PATHINFO_EXTENSION);
        $mimes = new \Mimey\MimeTypes;
        $type = $mimes->getMimeType($ext);

        return [
            'contents' => $contents,
            'size' => $size,
            'type' => $type,
        ];
    }

    /**
     * Upload a file to a location on the server
     *
     * @param $path
     * @param $file
     *
     * @return bool
     */
    public static function uploadFile($path, $file)
    {
        // prevent uploads to root directory
        if (empty($path)) {
            fail('Cannot upload file to cloud root directory');
        }
        static::setConfig();
        $saved_path = Storage::disk('sftp')->putFile(static::preparePath($path), $file);
        return basename($saved_path);
    }

    /**
     * Save file contents to a location on the server
     *
     * @param $path
     * @param $contents
     *
     * @return bool
     */
    public static function saveFile($path, $contents)
    {
        // prevent uploads to root directory
        if (!preg_match('/\//', $path)) {
            fail('Cannot save file to cloud root directory');
        }
        static::setConfig();
        return Storage::disk('sftp')->put(static::preparePath($path), $contents);
    }

    /**
     * Delete a given file from the server
     *
     * @param $path
     *
     * @return bool
     */
    public static function deleteFile($path)
    {
        static::setConfig();
        return Storage::disk('sftp')->delete(static::preparePath($path));
    }

    /**
     * Make a new directory on the server
     *
     * @param $path
     *
     * @return bool
     */
    public static function makeDir($path)
    {
        static::setConfig();
        return Storage::disk('sftp')->makeDirectory(static::preparePath($path));
    }

    /**
     * Delete a directory and all contents from the server
     *
     * @param $path
     *
     * @return bool
     */
    public static function deleteDir($path)
    {
        static::setConfig();
        return Storage::disk('sftp')->deleteDirectory(static::preparePath($path));
    }

    /**
     * Return a list of all files for the given path
     *
     * @param      $path
     * @param bool $recursive
     *
     * @return array
     */
    public static function listFiles($path = '', $recursive = false)
    {
        static::setConfig();
        if ($recursive) {
            $files = Storage::disk('sftp')->allFiles(static::preparePath($path));
        } else {
            $files = Storage::disk('sftp')->files(static::preparePath($path));
        }
        return static::filterResults($files);
    }

    /**
     * Return list of all directories for a given path
     *
     * @param string $path
     * @param bool   $recursive
     *
     * @return array
     */
    public static function listDirs($path = '', $recursive = false)
    {
        static::setConfig();
        if ($recursive) {
            $directories = Storage::disk('sftp')->allDirectories(static::preparePath($path));
        } else {
            $directories = Storage::disk('sftp')->directories(static::preparePath($path));
        }
        return static::filterResults($directories);
    }

    /**
     * Set the org to be used for all operations
     *
     * @param $org_id
     *
     * @return Cloud
     */
    public static function org($org_id)
    {
        self::$orgId = $org_id;
        return new static;
    }

    /**
     * Prepare the path for the cloud filesystem
     *
     * @param $path
     *
     * @return string
     */
    protected static function preparePath($path)
    {
        return 'cloud/' . preg_replace('/^\//', '', $path);
    }

    /**
     * Filter file/dir results before we return them
     *
     * @param $results
     *
     * @return array
     */
    protected static function filterResults($results)
    {
        $results_filtered = [];
        foreach ($results as $index => $path) {
            $path = preg_replace('/^cloud\//', '', $path);
            if (
                !preg_match('/\.git/', $path) &&
                !in_array($path, ['.htaccess', 'filelist.php', 'protected.php', 'thumbs.php'])
            ) {
                $results_filtered[] = $path;
            }
        }
        return $results_filtered;
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
            'privateKey' => $config['sftp_private_key']
            /*'cache' => [
                'store' => 'memcached',
                'expire' => 60,
                'prefix' => 'lumen-sftp',
            ],*/
        ]]);
    }


}