<?php

namespace DklCreations\SHCommon\Helpers;

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
        $request = \Illuminate\Http\Request::capture();

        $data = [];
        if (self::$data instanceof \Illuminate\Database\Eloquent\Collection) {
            if ($request->input('only')) {
                $only = $request->input('only');
                foreach (self::$data as $row) {
                    $row = $row->toArray();
                    $new_row = [];
                    foreach ($only as $key) {
                        if (preg_match('/\./', $key)) {
                            $parts = explode('.', $key);
                            $sub_arr = array_get($row, $parts[0]);
                            $new_sub_row = [];
                            if (isset($sub_arr) && is_array($sub_arr)) {
                                foreach ($sub_arr as $sub) {
                                    $value = array_get($sub, $parts[1]);
                                    if (isset($value)) {
                                        $new_row[$parts[0]][$parts[1]] = $value;
                                    }
                                }
                            }
                        } else {
                            $value = array_get($row, $key);
                            if (isset($value)) {
                                $new_row[$key] = $value;
                            }
                        }
                    }
                    $data[] = $new_row;
                }
            } elseif ($request->input('except')) {
                $except = $request->input('except');
                foreach (self::$data as $row) {
                    $row = $row->toArray();
                    foreach ($except as $key) {
                        if (preg_match('/\./', $key)) {
                            $parts = explode('.', $key);
                            $sub_arr = array_get($row, $parts[0]);
                            if (isset($sub_arr) && is_array($sub_arr)) {
                                foreach ($sub_arr as $index => $sub) {
                                    unset($row[$parts[0]][$index][$parts[1]]);
                                }
                            }
                        } else {
                            unset($row[$key]);
                        }
                    }
                    $data[] = $row;
                }
            }
        }

        $response = [
            'message' => self::$message,
            'data' => !empty($data) ? $data : self::$data,
            'links' => self::$links,
            'meta' => self::$meta,
        ];
        return response()->json($response, self::$code)
            ->withHeaders([
                'MS-Name' => env('APP_SERVICE'),
                'MS-Version' => '1.0',
            ]);
    }

}