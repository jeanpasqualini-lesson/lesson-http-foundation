<?php

namespace tests;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestMatcher;

class RequestMatcherTest extends \PHPUnit_Framework_TestCase
{
    use \CreateProviderTrait;

    /** @var RequestMatcher */
    protected $requestMatcher;

    /** @var Request */
    protected $request;

    public function setUp()
    {
        $this->requestMatcher = new RequestMatcher();

        $this->request = Request::create('/bad', 'POST', array(
            'address' => '3 Avenue Victor Hugo'
        ), array(), array(), array());
    }

    /**
     * @dataProvider testMatches
     *
     * @param null $expected
     * @param array $conditions
     * @return mixed|null|\Provider
     */
    public function testMatches($expected = null, $conditions = array())
    {
        if ($provider = $this->startProvide(__METHOD__))
        {
            return $provider
                ->provide(array(true, array('method' => 'PoST')))
                ->provide(array(true, array('method' => array('PoST'))))
                ->provide(array(false, array('method' => array('GET'))))
                ->provide(array(true, array('path' => '/bad')))
                ->provide(array(true, array('path' => 'ad')))
                ->provide(array(true, array('host' => 'localhost')))
                ->provide(array(true, array('ip' => '127.0.0.1')))
                ->provide(array(true, array('scheme' => 'http')))
                ->endProvide();
        }

        if (isset($conditions['method'])) {
            $this->requestMatcher->matchMethod($conditions['method']);
        }

        if (isset($conditions['path']))
        {
            $this->requestMatcher->matchPath($conditions['path']);
        }

        if (isset($conditions['host']))
        {
            $this->requestMatcher->matchHost($conditions['host']);
        }

        if (isset($conditions['ip']))
        {
            $this->requestMatcher->matchIps($conditions['ip']);
        }

        if (isset($conditions['scheme']))
        {
            $this->requestMatcher->matchScheme($conditions['scheme']);
        }

        if (isset($conditions['attribute']))
        {
            $this->requestMatcher->matchAttribute($conditions['attribute']['key'], $conditions['attribute']['value']);
        }

        $this->assertEquals($expected, $this->requestMatcher->matches($this->request), print_r($conditions, true));
    }
}