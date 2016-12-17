<?php

require __DIR__."/../vendor/autoload.php";

$tests = array(
    new \Test\RequestTest(),
    new \Test\ResponseTest(),
    //new \Test\SessionTest()
);

define("ROOT_DIR", __DIR__);

foreach($tests as $test)
{
    echo "===".get_class($test)."===".PHP_EOL;

    $test->runTest();
}
