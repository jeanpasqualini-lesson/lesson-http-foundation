<?php
/**
 * Created by PhpStorm.
 * User: darkilliant
 * Date: 3/13/15
 * Time: 10:44 AM
 */

namespace Test;


use Interfaces\TestInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\Handler\MemcachedSessionHandler;
use Symfony\Component\HttpFoundation\Session\Storage\Handler\MemcacheSessionHandler;
use Symfony\Component\HttpFoundation\Session\Storage\Handler\NativeFileSessionHandler;
use Symfony\Component\HttpFoundation\Session\Storage\Handler\NullSessionHandler;
use Symfony\Component\HttpFoundation\Session\Storage\NativeSessionStorage;

class SessionTest implements TestInterface {

    public function getStorages()
    {
        return array(
            new NativeSessionStorage(
                array(),
                new NullSessionHandler()
            )
        );

        /**
         * new NullSessionHandler(),
        new MemcacheSessionHandler(new \Memcache())
         */
    }

    public function runTest()
    {
        $storages = $this->getStorages();

        foreach($storages as $storage)
        {
            $this->printSeparator(get_class($storage));

            $session = new Session($storage);

            $session->start();

            $this->printSeparator("one");

            $this->testOne($session);
        }
    }

    public function printSeparator($name)
    {
        echo "===$name===\n";
    }

    public function testOne(Session $session)
    {
        echo "created : ".$session->getMetadataBag()->getCreated().PHP_EOL;

        echo "lastuser : ".$session->getMetadataBag()->getLastUsed().PHP_EOL;
    }
}