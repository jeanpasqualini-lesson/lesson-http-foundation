<?php
namespace tests;

use Symfony\Component\HttpFoundation\StreamedResponse;

class StreamedResponseTest extends \PHPUnit_Framework_TestCase
{
    /** @var StreamedResponse */
    protected $streamedResponse;

    public function setUp()
    {
        $this->streamedResponse = new StreamedResponse();
    }

    public function testGetContent()
    {
        $this->assertFalse($this->streamedResponse->getContent());
    }

    public function testSendContent()
    {
        $count = 0;

        $this->streamedResponse->setCallback(function() use (&$count) { ++$count; });

        $this->streamedResponse->sendContent();

        $this->assertEquals(1, $count, $count);
    }
}