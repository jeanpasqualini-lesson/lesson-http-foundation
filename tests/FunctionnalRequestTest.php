<?php

use Symfony\Component\HttpFoundation\Request;

class FunctionnalRequestTest extends PHPUnit_Framework_TestCase
{
    /** @var WebServer */
    protected static $webServer;

    public static function setUpBeforeClass()
    {
        if (null === self::$webServer)
        {
            self::$webServer = new WebServer();
        }

        if (!self::$webServer->isRunning()) {
            if (WebServer::STOPPED === self::$webServer->start(__DIR__.'/../src/http-request-to-request-object.php')) {
                exit();
            }
        }
    }

    public function setUp()
    {
        if (!self::$webServer->isRunning()) {
            sleep(1);
        }
    }

    public static function tearDownAfterClass()
    {
        self::$webServer->stop();
    }

    /**
     * getRequestObjectByRawHttpRequest
     *
     * @param string $rawRequest the raw reqeust
     *
     * @return Request
     */
    protected function getRequestObjectByRawHttpRequest($rawRequest)
    {
        $fp = fsockopen('localhost', 8000, $errno, $errstr, 1);
        if (!$fp) {
            echo "$errstr ($errno)<br />\n";
        } else {
            $object = "";

            fwrite($fp, $rawRequest);
            while (!feof($fp)) {
                $object .= fgets($fp, 128);
            }
            fclose($fp);

            $requestSerialized = substr($object, strpos($object, 'O:'));

            $requestUnserialized = unserialize($requestSerialized);

            $this->assertInstanceOf(
                'Symfony\Component\HttpFoundation\Request',
                $requestUnserialized,
                'size('.strlen($object).') '.$object
            );

            return $requestUnserialized;
        }

        return null;
    }

    public function testIncompleteRequest()
    {
        $rawRequest =
            "GET / HTTP/1.1\r\n".
            "Host: mami.com\r\n".
            "Content-Lenght: 0\r\n".
            "Connection: Close\r\n"
        ;

        $request = null; //$this->getRequestObjectByRawHttpRequest($rawRequest);

        $this->assertNull($request);
    }

    public function testSimpleRequest()
    {
        $rawRequest =
            "GET / HTTP/1.1\r\n".
            "Host: mami.com\r\n".
            "Content-Lenght: 0\r\n".
            "Connection: Close\r\n\r\n"
        ;

        $request = $this->getRequestObjectByRawHttpRequest($rawRequest);

        $this->assertNotNull($request);
        $this->assertEquals('GET', $request->getMethod());
        $this->assertEquals('mami.com', $request->getHttpHost());
        $this->assertEquals('mami.com', $request->getHost());
        $this->assertEquals('80', $request->getPort());
    }

    /**
     * @dataProvider proxyProvider
     */
    public function proxyProvider()
    {
        return [
            [
                "GET / HTTP/1.1\r\n".
                "Host: mami.com\r\n".
                "Content-Lenght: 0\r\n".
                "X_FORWARDED_FOR: 1.2.3.4\r\n".
                "X_FORWARDED_PROTO: https\r\n".
                "X_FORWARDED_HOST: papi.com\r\n".
                "X_FORWARDED_PORT: 9000\r\n".
                "Connection: Close\r\n\r\n"
            ],
            [
                "GET / HTTP/1.1\r\n".
                "Host: mami.com\r\n".
                "Content-Lenght: 0\r\n".
                "FORWARDED: for=1.2.3.4\r\n".
                "X_FORWARDED_PROTO: https\r\n".
                "X_FORWARDED_HOST: papi.com\r\n".
                "X_FORWARDED_PORT: 9000\r\n".
                "Connection: Close\r\n\r\n"
            ],
            [
                "GET / HTTP/1.1\r\n".
                "Host: mami.com\r\n".
                "Content-Lenght: 0\r\n".
                "FORWARDED: for=1.2.3.4\r\n".
                "X_FORWARDED_PROTO: https\r\n".
                "X_FORWARDED_HOST: papi.com\r\n".
                "X_FORWARDED_PORT: 9000\r\n".
                "Connection: Close\r\n\r\n"
            ],
            [
                "GET / HTTP/1.1\r\n".
                "Host: mami.com\r\n".
                "Content-Lenght: 0\r\n".
                "FORWARDED: for=1.2.3.4\r\n".
                "X_FORWARDED_PROTO: https\r\n".
                "MINECRAFT: papi.com\r\n".
                "X_FORWARDED_PORT: 9000\r\n".
                "Connection: Close\r\n\r\n",
                [
                    'trustedHeaderName' => [
                        'client_host' => 'MINECRAFT'
                    ]
                ]
            ],
        ];
    }

    /**
     * @dataProvider proxyProvider
     */
    public function testProxyRequest($rawRequest, $options = array())
    {
        Request::setTrustedProxies(array('127.0.0.1'));
        Request::setTrustedHosts(array('^papi\.com$', '^mami\.com$'));
        if (!empty($options['trustedHeaderName'])) {
            foreach($options['trustedHeaderName'] as $headerAlias => $headerName)
            {
                Request::setTrustedHeaderName($headerAlias, 'MINECRAFT');
            }
        }

        $request = $this->getRequestObjectByRawHttpRequest($rawRequest);

        $this->assertNotNull($request);
        $this->assertEquals('1.2.3.4', $request->getClientIp());
        $this->assertEquals('https', $request->getScheme());
        $this->assertEquals('https://papi.com:9000', $request->getSchemeAndHttpHost());
    }

    public function testSimpleRequestWithSpecialPort()
    {
        $rawRequest =
            "GET / HTTP/1.1\r\n".
            "Host: mami.com:8000\r\n".
            "Connection: Close\r\n\r\n"
        ;

        $request = $this->getRequestObjectByRawHttpRequest($rawRequest);
        $this->assertNotNull($request);
        $this->assertEquals('mami.com:8000', $request->getHttpHost());
        $this->assertEquals('8000', $request->getPort());
    }

    public function testPostRequest()
    {
        $postData = "form[username]=mario&form[password]=luigi";

        $rawRequest =
            "POST / HTTP/1.1\r\n".
            "Host: mami.com:8000\r\n".
            "Content-Type: application/x-www-form-urlencoded\r\n".
            "Content-Length: ".strlen($postData)."\r\n".
            "Connection: Close\r\n\r\n".

            $postData
        ;

        $request = $this->getRequestObjectByRawHttpRequest($rawRequest);

        $this->assertNotNull($request);
        $this->assertEquals(array(
            'form' => array(
                'username' => 'mario',
                'password' => 'luigi'
            )
        ), $request->request->all());
    }

    public function testHttpAuthenticateRequest()
    {
        $rawRequest =
            "POST / HTTP/1.1\r\n".
            "Host: mami.com:8000\r\n".
            "Authorization: Basic QWxhZGRpbjpvcGVuIHNlc2FtZQ==\r\n\r\n"
        ;

        $request = $this->getRequestObjectByRawHttpRequest($rawRequest);

        $this->assertNotNull($request);
        $this->assertEquals('Aladdin', $request->getUser());
        $this->assertEquals('open sesame', $request->getPassword());
    }

    public function methodOverrideProvider()
    {
        $postData = "_method=PUT";

        return [
            [
                // enableHttpMethodOverride = false
                "POST / HTTP/1.1\r\n".
                "Host: mami.com:8000\r\n".
                "X-HTTP-METHOD-OVERRIDE: PUT\r\n".
                "Connection: Close\r\n\r\n"
            ],
            [
                "POST /?_method=PUT HTTP/1.1\r\n".
                "Host: mami.com:8000\r\n".
                "Connection: Close\r\n\r\n"
            ],
            [
                "POST / HTTP/1.1\r\n".
                "Host: mami.com:8000\r\n".
                "Content-Type: application/x-www-form-urlencoded\r\n".
                "Content-Length: ".strlen($postData)."\r\n".
                "Connection: Close\r\n\r\n".

                $postData
            ]
        ];
    }

    /**
     * @dataProvider methodOverrideProvider
     */
    public function testHttpMethodOverride($rawRequest)
    {
        $request = $this->getRequestObjectByRawHttpRequest($rawRequest);

        Request::enableHttpMethodParameterOverride();
        $this->assertNotNull($request);
        $this->assertEquals('PUT', $request->getMethod());
        $this->assertEquals('POST', $request->getRealMethod());
    }
}