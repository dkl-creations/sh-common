<?php

namespace DklCreations\SHCommon\Helpers;

use Illuminate\Support\Facades\Storage;

class Cdn
{

    public static function uploadFile($file)
    {

        Storage::disk('sftp')->put('cdn/avatars/test.txt', 'foobar');

    }

    public static function deleteFile($file)
    {
        Storage::disk('sftp')->delete('folder_path/file_name.jpg');
    }

    public static function createDir($path)
    {
        Storage::makeDirectory($path);
    }

    public static function deleteDir($path)
    {
        Storage::deleteDirectory($path);
    }

    public static function listFiles($path)
    {
        $files = Storage::files($path);
    }

    public static function listDirs($path, $recursive = false)
    {
        $directories = Storage::directories($path);
        if ($recursive) {
            $directories = Storage::allDirectories($path);
        }
    }

    protected static function setConfig()
    {
        config(['filesystems.disks.sftp' => [
            'driver' => 'sftp',
            'host' => '192.168.33.40',
            'username' => 'acme',
            'password' => 'foobar',
        ]]);
    }


}