<?php

namespace DklCreations\SHCommon\Helpers;

class Format
{

    /**
     * Format a slug value
     *
     * @param $value
     *
     * @return string
     */
    public static function slug($content)
    {
        $content = strtolower(preg_replace('/\%/',' percentage',preg_replace('/\@/',' at ',preg_replace('/\&/',' and ',preg_replace('/\s[\s]+/','-',preg_replace('/[\s\W]+/','-',preg_replace('/^[\-]+/','',preg_replace('/[\-]+$/','',$content))))))));
        return $content;
    }



}