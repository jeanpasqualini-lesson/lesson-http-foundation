<?php

use Symfony\Component\HttpFoundation\ServerBag;

class ServerBagTest extends PHPUnit_Framework_TestCase
{
    use CreateProviderTrait;

    /** @var  ServerBag */
    protected $serverBag;

    public function setUp()
    {
        $this->serverBag = new ServerBag();
    }

    /**
     * @dataProvider testGetHeaders
     *
     * @param null $expectedHeaders
     * @param null $serverVars
     * @return mixed|null|Provider
     */
    public function testGetHeaders($expectedHeaders = null, $serverVars = null)
    {
        if ($provider = $this->startProvide(__METHOD__)) {
            return $provider
                ->provide(array(array('ACCEPT' => 'text/html'), array('HTTP_ACCEPT' => 'text/html')))
                ->provide(array(array('CONTENT_TYPE' => 'text/html'), array('CONTENT_TYPE' => 'text/html')))
                ->provide(array(array(), array()))
                ->provide(array(array(), array('BODY_TYPE' => 'text/html')))
                ->provide(array(
                    array('PHP_AUTH_USER' => 'mario', 'PHP_AUTH_PW' => '', 'AUTHORIZATION' => 'Basic bWFyaW86'),
                    array('PHP_AUTH_USER' => 'mario')
                ))
                ->endProvide();
        }

        $this->serverBag->replace($serverVars);

        $this->assertEquals($expectedHeaders, $this->serverBag->getHeaders());
    }
}