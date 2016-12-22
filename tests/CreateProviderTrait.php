<?php

/**
 * Created by PhpStorm.
 * User: aurore
 * Date: 23/12/2016
 * Time: 11:59
 */
trait CreateProviderTrait
{
    /**
     * @param $method
     * @return Provider
     */
    protected function startProvide($method)
    {
        static $providers = array();
        if (!isset($providers[$method]))
        {
            $providers[$method] = new Provider();
        }

        if (!$providers[$method]->isProvided()) {
            return $providers[$method];
        }

        return;
    }
}