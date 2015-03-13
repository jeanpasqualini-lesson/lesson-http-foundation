<?php
/**
 * Created by PhpStorm.
 * User: darkilliant
 * Date: 3/13/15
 * Time: 10:16 AM
 */

namespace Factory;

use Symfony\Component\HttpFoundation\Request;

class RequestFactory {

    public static function createRequest()
    {
        $gen = function($name)
        {
            $data = array();

            for($i = 0; $i < 10; $i++)
            {
                $data[$name.$i] = $name.$i;
            }

            return $data;
        };

        $request = Request::create(
            "/test/three",
            "GET",
            $gen("parameters"),
            $gen("cookie"),
            array(),
            array(),
            null
        );

        return $request;
    }

}