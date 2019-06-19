<?php

namespace DklCreations\SHCommon\Controllers;

use Laravel\Lumen\Routing\Controller as LumenController;

abstract class BaseController extends LumenController
{

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        // inject our services as class properties
        if ( isset($this->services) && is_array($this->services) ) {
            foreach ($this->services as $property => $class) {
                $this->{$property} = new $class;
            }
        }
    }


}