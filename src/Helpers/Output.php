<?php

namespace Lewisqic\SHCommon\Helpers;

class Output
{

    /**
     * HTTP status code
     * @var int
     */
    protected static $code = 200;

    /**
     * Message string
     * @var string
     */
    protected static $message = null;

    /**
     * Data array
     * @var array
     */
    protected static $data = [];

    /**
     * Links array
     * @var array
     */
    protected static $links = [];

    /**
     * Meta array
     * @var array
     */
    protected static $meta = [];

    /**
     * Set our response code
     * @param $code
     * @return self
     */
    public static function code($code)
    {
        self::$code = $code;
        return new static;
    }

    /**
     * Set our output message content
     * @param $message
     * @return self
     */
    public static function message($message)
    {
        self::$message = $message;
        return new static;
    }

    /**
     * Set our data content
     * @param $data
     * @return self
     */
    public static function data($data)
    {
        self::$data = $data;
        return new static;
    }

    /**
     * Set our links content
     * @param $links
     * @return self
     */
    public static function links($links)
    {
        self::$links = $links;
        return new static;
    }
    /**
     * Set our meta content
     * @param $meta
     * @return self
     */
    public static function meta($meta)
    {
        self::$meta = $meta;
        return new static;
    }

    /**
     * Send our output content via json
     */
    public static function json()
    {
        $response = [
            'message' => self::$message,
            'data' => self::$data,
            'links' => self::$links,
            'meta' => self::$meta,
        ];
        header('MS-Name: ' . env('APP_SERVICE'));
        header('MS-Version: 1.0');
        return response()->json($response, self::$code);
    }

}