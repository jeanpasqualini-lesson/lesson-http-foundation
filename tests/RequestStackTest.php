<?php

namespace tests;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class RequestStackTest extends \PHPUnit_Framework_TestCase
{
    /** @var RequestStack */
    protected $requestStack;

    public function setUp()
    {
        $this->requestStack = new RequestStack();
        $this->requestStack->push(Request::create('/master'));
        $this->requestStack->push(Request::create('/sub-request'));
    }

    public function testPush()
    {
        $this->requestStack->push(Request::create('/push'));

        $this->assertEquals('/push', $this->requestStack->getCurrentRequest()->getPathInfo());
    }

    public function testPop()
    {
        $this->requestStack->push(Request::create('/pop'));
        $this->requestStack->pop();

        $this->assertEquals('/sub-request', $this->requestStack->getCurrentRequest()->getPathInfo());
    }

    public function testGetMasterRequest()
    {
        $this->requestStack->push(Request::create('/get-master-request'));

        $this->assertEquals('/master', $this->requestStack->getMasterRequest()->getPathInfo());
    }

    public function testGetParentRequest()
    {
        $this->requestStack->push(Request::create('/get-parent-request'));

        $this->assertEquals('/sub-request', $this->requestStack->getParentRequest()->getPathInfo());
    }
}