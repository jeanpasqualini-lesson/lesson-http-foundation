<?php
namespace tests;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ResponseTest extends \PHPUnit_Framework_TestCase
{
    use \CreateProviderTrait;

    /** @var Response */
    protected $response;

    public function setUp()
    {
        $this->response = new Response('content');
        $this->response->setProtocolVersion('1.1');
    }

    /**
     * @dataProvider testSetGoodStatusCode
     *
     * @param null $statusCode
     * @return mixed|null|\Provider
     */
    public function testSetGoodStatusCode($statusCode = null)
    {
        if ($provider = $this->startProvide(__METHOD__))
        {
            return $provider
                ->provide(array(100))
                ->provide(array(101))
                ->endProvide();
        }

        $this->response->setStatusCode($statusCode);

        $this->assertEquals($statusCode, $this->response->getStatusCode());
    }

    /**
     * @dataProvider testSetBadStatusCode
     * @expectedException \InvalidArgumentException
     *
     * @param null $statusCode
     * @return mixed|null|\Provider
     */
    public function testSetBadStatusCode($statusCode = null)
    {
        if ($provider = $this->startProvide(__METHOD__))
        {
            return $provider
                ->provide(array(99))
                ->provide(array(600))
                ->endProvide();
        }

        $this->response->setStatusCode($statusCode);

        $this->assertEquals($statusCode, $this->response->getStatusCode());
    }

    /**
     * @dataProvider testIsInformational
     *
     * @param null $statusCode
     */
    public function testIsInformational($statusCode = null)
    {
        if ($provider = $this->startProvide(__METHOD__))
        {
            return $provider
                ->provide(array(100))
                ->provide(array(101))
                ->endProvide();
        }

        $this->response->setStatusCode($statusCode);

        $this->assertTrue($this->response->isInformational());
    }

    /**
     * @dataProvider testIsSuccessful
     *
     * @param null $statusCode
     */
    public function testIsSuccessful($statusCode = null)
    {
        if ($provider = $this->startProvide(__METHOD__))
        {
            return $provider
                ->provide(array(200))
                ->provide(array(201))
                ->endProvide();
        }

        $this->response->setStatusCode($statusCode);

        $this->assertTrue($this->response->isSuccessful());
    }

    /**
     * @dataProvider testIsRedirection
     *
     * @param null $statusCode
     */
    public function testIsRedirection($statusCode = null)
    {
        if ($provider = $this->startProvide(__METHOD__))
        {
            return $provider
                ->provide(array(300))
                ->provide(array(301))
                ->endProvide();
        }

        $this->response->setStatusCode($statusCode);

        $this->assertTrue($this->response->isRedirection());
    }

    /**
     * @dataProvider testIsClientError
     *
     * @param null $statusCode
     */
    public function testIsClientError($statusCode = null)
    {
        if ($provider = $this->startProvide(__METHOD__))
        {
            return $provider
                ->provide(array(400))
                ->provide(array(401))
                ->endProvide();
        }

        $this->response->setStatusCode($statusCode);

        $this->assertTrue($this->response->isClientError());
    }

    /**
     * @dataProvider testIsServerError
     *
     * @param null $statusCode
     */
    public function testIsServerError($statusCode = null)
    {
        if ($provider = $this->startProvide(__METHOD__))
        {
            return $provider
                ->provide(array(500))
                ->provide(array(501))
                ->endProvide();
        }

        $this->response->setStatusCode($statusCode);

        $this->assertTrue($this->response->isServerError());
    }

    /**
     * @dataProvider testIsOk
     *
     * @param null $expected
     * @param null $statusCode
     * @return mixed|null|\Provider
     */
    public function testIsOk($expected = null, $statusCode = null)
    {
        if ($provider = $this->startProvide(__METHOD__))
        {
            return $provider
                ->provide(array(true, 200))
                ->provide(array(false, 201))
                ->endProvide();
        }

        $this->response->setStatusCode($statusCode);

        $this->assertEquals($expected, $this->response->isOk());
    }

    /**
     * @dataProvider testIsNotFound
     *
     * @param null $expected
     * @param null $statusCode
     * @return mixed|null|\Provider
     */
    public function testIsNotFound($expected = null, $statusCode = null)
    {
        if ($provider = $this->startProvide(__METHOD__))
        {
            return $provider
                ->provide(array(true, 404))
                ->provide(array(false, 405))
                ->endProvide();
        }

        $this->response->setStatusCode($statusCode);

        $this->assertEquals($expected, $this->response->isNotFound());
    }


    /**
     * @dataProvider testIsForbidden
     *
     * @param null $expected
     * @param null $statusCode
     * @return mixed|null|\Provider
     */
    public function testIsForbidden($expected = null, $statusCode = null)
    {
        if ($provider = $this->startProvide(__METHOD__))
        {
            return $provider
                ->provide(array(true, 403))
                ->provide(array(false, 401))
                ->endProvide();
        }

        $this->response->setStatusCode($statusCode);

        $this->assertEquals($expected, $this->response->isForbidden());
    }

    /**
     * @dataProvider testIsEmpty
     *
     * @param null $expected
     * @param null $statusCode
     * @return mixed|null|\Provider
     */
    public function testIsEmpty($expected = null, $statusCode = null)
    {
        if ($provider = $this->startProvide(__METHOD__))
        {
            return $provider
                ->provide(array(true, 204))
                ->provide(array(true, 304))
                ->provide(array(false, 305))
                ->endProvide();
        }

        $this->response->setStatusCode($statusCode);

        $this->assertEquals($expected, $this->response->isEmpty());
    }

    /**
     * @dataProvider testIsRedirect
     *
     * @param null $expected
     * @param null $statusCode
     * @return mixed|null|\Provider
     */
    public function testIsRedirect($expected = null, $statusCode = null, $location = null)
    {
        if ($provider = $this->startProvide(__METHOD__))
        {
            return $provider
                ->provide(array(true, 201))
                ->provide(array(true, 301))
                ->provide(array(true, 302))
                ->provide(array(true, 303))
                ->provide(array(true, 307))
                ->provide(array(true, 308))
                ->provide(array(true, 201, '/new'))
                ->provide(array(false, 201, '/badnew'))
                ->endProvide();
        }

        $this->response->setStatusCode($statusCode);
        $this->response->headers->set('location', '/new');

        $this->assertEquals($expected, $this->response->isRedirect($location));
    }

    /**
     * @dataProvider testIsCacheable
     *
     * @param null $expected
     * @param null $statusCode
     * @return mixed|null|\Provider
     */
    public function testIsCacheable($expected = null, $statusCode = null)
    {
        if ($provider = $this->startProvide(__METHOD__))
        {
            return $provider
                ->provide(array(true, 200))
                ->provide(array(true, 203))
                ->provide(array(true, 300))
                ->provide(array(true, 301))
                ->provide(array(true, 302))
                ->provide(array(true, 404))
                ->provide(array(true, 410))
                ->endProvide();
        }

        $this->response->setSharedMaxAge(86400);
        $this->response->setStatusCode($statusCode);

        $this->assertEquals($expected, $this->response->isCacheable(), $statusCode);
    }

    public function testIsNotModifiedWithLastModifiedInFuture()
    {
        $this->response->setLastModified(new \DateTime('2016-12-15'));

        $request = Request::create('/test');
        $request->headers->set('if-modified-since', 'Mon, 17 Dec 2016 15:00:00 GMT');

        $this->assertTrue($this->response->isNotModified($request));
        $this->assertEquals(304, $this->response->getStatusCode());
        $this->assertEquals('', $this->response->getContent());
    }

    public function testIsNotModifiedWithLastModifiedInPast()
    {
        $this->response->setLastModified(new \DateTime('2016-12-15'));
        $request = Request::create('/test');
        $request->headers->set('if-modified-since', 'Tue, 13 Dec 2016 15:00:00 GMT');

        $this->assertFalse($this->response->isNotModified($request));
        $this->assertEquals(200, $this->response->getStatusCode());
        $this->assertEquals('content', $this->response->getContent());
    }

    public function testIsNotModifiedWithEtag()
    {
        $this->response->setEtag('minecraft');

        $request = Request::create('/test');
        $request->headers->set('if_none_match', '"minecraft"');

        $this->assertTrue($this->response->isNotModified($request));
        $this->assertEquals(304, $this->response->getStatusCode());
        $this->assertEquals('', $this->response->getContent());
    }

    public function testIsNotModifiedWithEtagAsterix()
    {
        $this->response->setEtag('minecraft');

        $request = Request::create('/test');
        $request->headers->set('if_none_match', '*');

        $this->assertTrue($this->response->isNotModified($request));
        $this->assertEquals(304, $this->response->getStatusCode());
        $this->assertEquals('', $this->response->getContent());
    }

    public function testIsValidateable()
    {
        $this->response->headers->replace(array('last-modified' => 'last'));
        $this->assertTrue($this->response->isValidateable());

        $this->response->headers->replace(array('etag' => 'etag'));
        $this->assertTrue($this->response->isValidateable());

        $this->response->headers->replace(array());
        $this->assertFalse($this->response->isValidateable());
    }

    public function testMustRevalidate()
    {
        $this->response->headers->replace(array('cache-control' => 'private, must-revalidate'));
        $this->assertTrue($this->response->mustRevalidate());

        $this->response->headers->replace(array('cache-control' => 'private, proxy-revalidate'));
        $this->assertTrue($this->response->mustRevalidate());

        $this->response->headers->replace(array());
        $this->assertFalse($this->response->mustRevalidate());
    }

    public function testSetDate()
    {
        $date = new \DateTime();
        $this->response->setDate($date);

        $this->assertEquals($date, $this->response->getDate());
    }

    public function testExpires()
    {
        $this->response->setMaxAge(86400);

        $this->response->expire();
        $this->assertEquals(86400, $this->response->getAge());
    }

    public function testSetExpires()
    {
        $date = new \DateTime();

        $this->response->setExpires($date);
        $this->assertEquals($date->format('d-m-Y'), $this->response->getExpires()->format('d-m-Y'));

        $this->response->setExpires();
        $this->assertEquals(null, $this->response->getExpires());
    }

    public function testGetMaxAge()
    {
        $this->response->headers->set('cache-control', 'public, s-maxage=86400');
        $this->assertEquals(86400, $this->response->getMaxAge());

        $this->response->headers->set('cache-control', 'private, max-age=86400');
        $this->assertEquals(86400, $this->response->getMaxAge());

        $this->response->headers->set('expires', '86400');
        $this->assertEquals(86400, $this->response->getMaxAge());
    }

    public function testSetTtl()
    {

    }

    public function testSetClientTtl()
    {

    }

    public function testGetVary()
    {
        $this->response->setVary(array('Accept', 'Accept-Charset'));
        $this->assertEquals(array('Accept', 'Accept-Charset'), $this->response->getVary());

        $this->response->setVary('Accept, Accept-Charset');
        $this->assertEquals(array('Accept', 'Accept-Charset'), $this->response->getVary());
    }

    /**
     * @dataProvider testSetCache
     *
     * @param null $expected
     * @param null $method
     * @param null $optionkey
     * @param null $optionValue
     * @return mixed|null|\Provider
     */
    public function testSetCache($expected = null, $method = null, $optionkey = null, $optionValue = null)
    {
        if ($provider = $this->startProvide(__METHOD__)) {
            return $provider
                ->provide(array(
                    $expected       = '"minecraft"',
                    $method         = 'getEtag',
                    $optionkey      = 'etag',
                    $optionValue    = 'minecraft'
                ))
                ->provide(array(
                    $expected       = '12-12-2012',
                    $method         = 'getLastModified',
                    $optionkey      = 'last_modified',
                    $optionValue    = new \DateTime('2012-12-12 12:00:00')
                ))
                ->provide(array(
                    $expected       = 86400,
                    $method         = 'getMaxAge',
                    $optionkey      = 'max_age',
                    $optionValue    = 86400
                ))
                ->provide(array(
                    $expected       = 86400,
                    $method         = 'getMaxAge',
                    $optionkey      = 's_maxage',
                    $optionValue    = 86400
                ))
                ->provide(array(
                    $expected       =
                        'HTTP/1.1 200 OK'."\r\n".
                        'Cache-Control: public'."\r\n\r\n".
                        'content',
                    $method         = '__toString',
                    $optionkey      = 'public',
                    $optionValue    = true
                ))
                ->provide(array(
                    $expected       =
                        'HTTP/1.1 200 OK'."\r\n".
                        'Cache-Control: private'."\r\n\r\n".
                        'content',
                    $method         = '__toString',
                    $optionkey      = 'private',
                    $optionValue    = true
                ))
                ->endProvide();
        }

        // etag, last_modified, max_age, s_maxage, public, private
        $this->response->setCache(array(
            $optionkey => $optionValue
        ));

        $value = $this->response->$method();

        if ($value instanceof \DateTime) {
            $value = $value->format('d-m-Y');
        }

        $this->assertEquals($expected, $value, $method);
    }

    public function testSetNotModified()
    {
        $this->response->headers->replace(array(
            'Allow' => '',
            'Content-Encoding' => '',
            'Content-Language' => '',
            'Content-Length' => '',
            'Content-MD5' => '',
            'Content-Type' => '',
            'Last-Modified' => ''
        ));

        $expected = array('cache-control' => array('private, must-revalidate'));
        $debug = print_r($this->response->headers->all(), true);

        $this->assertNotEquals(
            $expected,
            $this->response->headers->all(),
            $debug
        );

        $this->response->setNotModified();

        $this->assertEquals(
            $expected,
            $this->response->headers->all(),
            $debug
        );
    }
}