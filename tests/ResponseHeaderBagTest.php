<?php

namespace tests;

use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class ResponseHeaderBagTest extends \PHPUnit_Framework_TestCase
{
    /** @var ResponseHeaderBag */
    protected $responseHeaderBag;

    public function setUp()
    {
        $this->responseHeaderBag = new ResponseHeaderBag();
    }

    public function testAllPreserveCases()
    {
        $this->responseHeaderBag->replace(array('Accept' => 'text/html'));

        $this->assertArrayNotHasKey('Accept', $this->responseHeaderBag->all());
        $this->assertArrayHasKey('Accept', $this->responseHeaderBag->allPreserveCase());
    }

    public function testToString()
    {
        $this->responseHeaderBag->replace(array('Content-Type' => 'text/html', 'Accept' => 'text/html'));

        $this->assertEquals(
            'Accept:        text/html' ."\r\n".
            'Cache-Control: no-cache'  ."\r\n".
            'Content-Type:  text/html' ."\r\n",
            (string) $this->responseHeaderBag
        );
    }

    public function testHasCacheControl()
    {
        $this->assertTrue($this->responseHeaderBag->has('cache-control'));
    }

    public function testHasCacheControlDirective()
    {
        $this->responseHeaderBag->addCacheControlDirective('s-max-age', 86400);

        $this->assertTrue($this->responseHeaderBag->hasCacheControlDirective('s-max-age'));
    }

    public function testComputeCacheControl()
    {
        // cache-control empty and no etag and no last-modified and no expires
        $this->responseHeaderBag->replace(array());
        $this->assertEquals('no-cache', $this->responseHeaderBag->get('cache-control'));

        // cache-control empty may (etag or last-modified or expires) is present
        $this->responseHeaderBag->replace(array('expires' => 86400));
        $this->assertEquals('private, must-revalidate', $this->responseHeaderBag->get('cache-control'));

        // cache-control is not empty and max-age is defined
        $this->responseHeaderBag->replace(array('cache-control' => 'max-age=86400'));
        $this->assertEquals('max-age=86400, private', $this->responseHeaderBag->get('cache-control'));
    }

}