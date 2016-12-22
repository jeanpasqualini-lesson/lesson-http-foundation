<?php
namespace tests;

use Symfony\Component\HttpFoundation\JsonResponse;

class JsonResponseTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var JsonResponse
     */
    protected $jsonResponse;

    public function setUp()
    {
        $this->jsonResponse = new JsonResponse();
    }

    public function testSetDataWithArray()
    {
        $this->jsonResponse->setData(array('foo' => 'bar'));

        $this->assertEquals('{"foo":"bar"}', $this->jsonResponse->getContent());
    }

    public function testSetDataWithArrayAndEncodingOptions()
    {
        $this->jsonResponse->setEncodingOptions(JSON_PRETTY_PRINT);

        $this->jsonResponse->setData(array('foo' => 'bar'));

        $this->assertEquals(
            '{'.PHP_EOL.
            '    "foo": "bar"'.PHP_EOL.
            '}'
            , $this->jsonResponse->getContent());
    }

    public function testSetDataWithString()
    {
        $this->jsonResponse->setData('dsqdqs');

        $this->assertEquals('"dsqdqs"', $this->jsonResponse->getContent());
    }

    public function testSetDataWithObject()
    {
        $this->jsonResponse->setData(new \stdClass());

        $this->assertEquals('{}', $this->jsonResponse->getContent());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testSetDataWithRessource()
    {
        $this->jsonResponse->setData(fopen('php://temp', 'r+'));
    }

    public function testSetCallback()
    {
        $this->jsonResponse->setCallback('process');

        $this->jsonResponse->setData(array('foo' => 'bar'));

        $this->assertEquals('/**/process({"foo":"bar"});', $this->jsonResponse->getContent());
    }
}