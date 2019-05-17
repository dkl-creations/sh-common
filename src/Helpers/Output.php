<?php

namespace Lewisqic\SHCommon\Helpers;

class Output
{

    /**
     * Set our output message content
     * @param $message
     */
    public static function message($message)
    {
        die($message);
    }

}