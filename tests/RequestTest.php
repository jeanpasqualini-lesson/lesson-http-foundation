<?php

use Symfony\Component\HttpFoundation\Request;

class RequestTest extends PHPUnit_Framework_TestCase
{
    use CreateProviderTrait;

    public function getRequestObjectByServerVariables($config)
    {
        $config = array_merge(
            array(
                'uri' => '/',
                'method' => 'GET',
                'parameters' => array(),
                'cookies' => array(),
                'files' => array(),
                'server' => array(),
                'attributes' => array()
            ),
            $config
        );

        $request = Request::create(
            $config['uri'],
            $config['method'],
            $config['parameters'],
            $config['cookies'],
            $config['files'],
            $config['server']
        );

        if (!empty($config['attributes'])) {
            $request->attributes->add($config['attributes']);
        }

        return $request;
    }

    public function resourceInfoProvider()
    {
        // Base url : Base url
        // FrontController dans document root + RewriteUrl = /
        // FrontController dans document root = /index.php
        // FrontController dans document root/web = /web/index.php
        //
        // Path info = Uri - BaseUrl - Query String
        // Base path : le chemin du filesystem à partir du document root (change dans le cas de l'usage d'un front controlleur)

        return [
            [
                array(
                    'uri' => '/list?p=2',
                    'server' => array(
                        'HTTP_HOST' => 'mami.com'
                    )
                ),
                array(
                    'pathInfo' => '/list',
                    'basePath' => '',
                    'baseUrl' => '',
                    'uriForPath' => 'http://mami.com/lost'
                ),
                'NO FRONT CONTROLLER / FOLDER ROOT / NO VIRTUAL FOLDER'
            ],
            [
                array(
                    'uri' => '/index.php/list?p=2',
                    'server' => array(
                        'HTTP_HOST' => 'mami.com',
                        'SCRIPT_NAME' => 'index.php',
                        'SCRIPT_FILENAME' => 'index.php',
                    )
                ),
                array(
                    'pathInfo' => '/list',
                    'basePath' => '',
                    'baseUrl' => '/index.php',
                    'uriForPath' => 'http://mami.com/index.php/lost'
                ),
                'GOOD FRONT CONTROLLER / FOLDER ROOT / NO VIRTUAL FOLDER'
            ],
            [
                array(
                    'uri' => '/index.php/list/archive?p=2',
                    'server' => array(
                        'HTTP_HOST' => 'mami.com',
                        'SCRIPT_NAME' => 'index.php',
                        'SCRIPT_FILENAME' => 'index.php',
                    )
                ),
                array(
                    'pathInfo' => '/list/archive',
                    'basePath' => '',
                    'baseUrl' => '/index.php',
                    'uriForPath' => 'http://mami.com/index.php/lost'
                ),
                'GOOD FRONT CONTROLLER / FOLDER ROOT / NO VIRTUAL FOLDER'
            ],
            // Dans un sous dossier
            [
                array(
                    'uri' => '/web/list?p=2',
                    'server' => array(
                        'HTTP_HOST' => 'mami.com',
                        'SCRIPT_NAME' => 'index.php',
                        'SCRIPT_FILENAME' => 'index.php',
                    )
                ),
                array(
                    'pathInfo' => '/web/list',
                    'basePath' => '',
                    'baseUrl' => '',
                    'uriForPath' => 'http://mami.com/lost'
                ),
                'NO FRONT CONTROLLER / FOLDER WEB / NO VIRTUAL FOLDER'
            ],
            [
                array(
                    'uri' => '/web/index.php/list?p=2',
                    'server' => array(
                        'HTTP_HOST' => 'mami.com',
                        'SCRIPT_NAME' => 'index.php',
                        'SCRIPT_FILENAME' => 'index.php',
                    )
                ),
                array(
                    'pathInfo' => '/list',
                    'basePath' => '/web',
                    'baseUrl' => '/web/index.php',
                    'uriForPath' => 'http://mami.com/web/index.php/lost'
                ),
                'GOOD FRONT CONTROLLER / FOLDER WEB / NO VIRTUAL FOLDER'
            ],
            [
                array(
                    'uri' => '/web/index.php/list/archive?p=2',
                    'server' => array(
                        'HTTP_HOST' => 'mami.com',
                        'SCRIPT_NAME' => 'index.php',
                        'SCRIPT_FILENAME' => 'index.php',
                    )
                ),
                array(
                    'pathInfo' => '/list/archive',
                    'basePath' => '/web',
                    'baseUrl' => '/web/index.php',
                    'uriForPath' => 'http://mami.com/web/index.php/lost'
                ),
                'GOOD FRONT CONTROLLER / FOLDER WEB / VIRTUAL FOLDER ARCHIVE'
            ],
        ];
    }

    /**
     * @dataProvider resourceInfoProvider
     */
    public function testResourceInfo($config, $expect, $dataTestDescribe)
    {
        $request = $this->getRequestObjectByServerVariables($config);

        $failMessage = function($message) use ($dataTestDescribe) {
            return $dataTestDescribe.' : '.$message;
        };

        $this->assertNotNull($request);
        $this->assertEquals($expect['pathInfo'], $request->getPathInfo(), $failMessage('test path info'));
        $this->assertEquals($expect['basePath'], $request->getBasePath(), $failMessage('test base path'));
        $this->assertEquals($expect['baseUrl'], $request->getBaseUrl(), $failMessage('test base url'));
        if (isset($expect['uriForPath'])) {
            $this->assertEquals(
                $expect['uriForPath'],
                $request->getUriForPath('/lost'),
                $failMessage('test uri for path')
            );
        }
        if (isset($expect['relativeUriForPath'])) {
            $this->assertEquals(
                $expect['relativeUriForPath'],
                $request->getRelativeUriForPath('/lost'),
                $failMessage('test relative uri for path')
            );
        }
    }

    /**
     * @dataProvider getRelativeUriForPathData()
     */
    public function testGetRelativeUriForPath($pathinfo, $path, $expected)
    {
        Request::setTrustedHosts(array('^localhost$'));

        $this->assertEquals($expected, Request::create($pathinfo)->getRelativeUriForPath($path));
    }

    public function getRelativeUriForPathData()
    {
        return array(
            //    PathInfo       To Path                = Expected
            array('/foo'        , '/me.png'             , 'me.png'                  ),
            array('/foo/bar'    , '/me.png'             , '../me.png'               ),
            array('/foo/bar'    , '/foo/me.png'         , 'me.png'                  ),
            array('/foo/bar/b'  , '/foo/baz/me.png'     , '../baz/me.png'           ),
            array('/foo/bar/b'  , '/fooz/baz/me.png'    , '../../fooz/baz/me.png'   ),
            array('/foo/bar/b'  , 'baz/me.png'          , 'baz/me.png'              ),
            array('/foo/bar/c'  , '/../foo/bar/c'       , '../../../foo/bar/c'      ),
        );
    }

    public function testGetQueryString()
    {
        $request = $this->getRequestObjectByServerVariables(array(
            'uri' => '/pagé?p=2é'
        ));

        $this->assertEquals('p=2%C3%A9', $request->getQueryString());
    }

    public function testIsSecure()
    {
        $request = $this->getRequestObjectByServerVariables(array(
            'server' => array(
                'HTTPS' => 'on'
            )
        ));

        $this->assertTrue($request->isSecure());
    }

    /**
     * @dataProvider testGetHost
     */
    public function testGetHost($server = null)
    {
        if ($provider = $this->startProvide(__METHOD__)) {
            return $provider
                ->provide(array(array('HTTP_HOST' => 'voiture.com')))
                ->provide(array(array('HTTP_HOST' => '', 'SERVER_NAME' => 'voiture.com')))
                ->provide(array(array('HTTP_HOST' => '', 'SERVER_NAME' => '', 'SERVER_ADDR' => 'voiture.com')))
                ->endProvide();
        }

        Request::setTrustedHosts(array());

        $request = $this->getRequestObjectByServerVariables(array(
            'server' => $server
        ));

        $this->assertEquals('voiture.com', $request->getHost());
    }

    /**
     * @dataProvider testGetMimeType
     *
     * @param null $mimeTypeExpected
     * @param null $format
     * @return array
     */
    public function testGetMimeType($mimeTypeExpected = null, $format = null)
    {
        if ($provider = $this->startProvide(__METHOD__)) {
            return $provider
                ->provide(array('text/xml', 'xml'))
                ->provide(array('text/html', 'html'))
                ->provide(array('application/x-www-form-urlencoded', 'form'))
                ->endProvide();
        }

        Request::setTrustedHosts(array());

        $request = $this->getRequestObjectByServerVariables(array());

        $this->assertEquals($mimeTypeExpected, $request->getMimeType($format));
    }

    /**
     * @dataProvider testGetFormat
     *
     * @param null $formatExpected
     * @param null $mimeType
     * @return null|RequestTest
     */
    public function testGetFormat($formatExpected = null, $mimeType = null)
    {
        if ($provider = $this->startProvide(__METHOD__)) {
            return $provider
                ->provide(array('form', 'application/x-www-form-urlencoded'))
                ->endProvide();
        }

        Request::setTrustedHosts(array());

        $request = $this->getRequestObjectByServerVariables(array());

        $this->assertEquals($formatExpected, $request->getFormat($mimeType));
    }

    /**
     * @dataProvider testGetRequestFormat
     *
     * @param null $formatExpected
     * @param $attributes
     * @return null|RequestTest
     */
    public function testGetRequestFormat($formatExpected = null, $attributes = array())
    {
        if ($provider = $this->startProvide(__METHOD__)) {
            return $provider
                ->provide(array('html', array()))
                ->provide(array('css', array('_format' => 'css')))
                ->endProvide();
        }

        $request = $this->getRequestObjectByServerVariables(array(
            'attributes' => $attributes
        ));

        $this->assertEquals($formatExpected, $request->getRequestFormat());
    }

    /**
     * @dataProvider testGetContentType
     *
     * @param $contentTypeExpected
     * @param $serverVars
     * @return null|RequestTest
     */
    public function testGetContentType($contentTypeExpected = null, $serverVars = null)
    {
        if ($provider = $this->startProvide(__METHOD__)) {
            return $provider
                ->provide(array('', array()))
                ->provide(array('css', array('CONTENT_TYPE' => 'text/css')))
                ->endProvide();
        }

        $request = $this->getRequestObjectByServerVariables(array(
            'server' => $serverVars
        ));

        $this->assertEquals($contentTypeExpected, $request->getContentType());
    }

    public function testGetDefaultLocale()
    {
        $request = $this->getRequestObjectByServerVariables(array());

        $this->assertEquals('en', $request->getDefaultLocale());
    }

    public function testGetLocale()
    {
        $request = $this->getRequestObjectByServerVariables(array());

        $this->assertEquals('en', $request->getLocale());
    }

    public function testIsMethod()
    {
        $request = $this->getRequestObjectByServerVariables(array());
        $request->setMethod('pOsT');

        $this->assertTrue($request->isMethod('PoSt'));
    }

    /**
     * @dataProvider testIsMethodSafe
     *
     * @param null $expected
     * @param null $method
     * @return mixed|null|Provider
     */
    public function testIsMethodSafe($expected = null, $method = null)
    {
        if ($provider = $this->startProvide(__METHOD__))
        {
            return $provider
                ->provide(array(true, 'GET'))
                ->provide(array(true, 'HEAD'))
                ->provide(array(false, 'POST'))
                ->provide(array(false, 'PUT'))
                ->provide(array(false, 'PATCH'))
                ->provide(array(false, 'DELETE'))
                ->provide(array(false, 'PURGE'))
                ->provide(array(true, 'OPTIONS'))
                ->provide(array(true, 'TRACE'))
                ->provide(array(false, 'CONNECT'))
                ->endProvide();
        }

        $request = $this->getRequestObjectByServerVariables(array(
            'method' => $method
        ));

        $this->assertEquals($expected, $request->isMethodSafe());
    }

    /**
     * @dataProvider testGetContent
     *
     * @param null $expectedType
     * @param null $content
     * @param null $asResource
     */
    public function testGetContent($expectedType = null, $content = null, $asResource = null)
    {
        if ($provider = $this->startProvide(__METHOD__)) {
            return $provider
                ->provide(array('string', 'body', false))
                ->provide(array('resource', 'body', true))
                ->provide(array('string', fopen('php://temp', 'r+'), false))
                ->provide(array('resource', fopen('php://temp', 'r+'), true))
                ->endProvide();
        }

        $request = $this->getRequestObjectByServerVariables(array(
            'content' => $content
        ));

        $content = $request->getContent($asResource);

        $this->assertInternalType($expectedType, $content, gettype($content));
    }

    /**
     * @dataProvider testGetEtags
     *
     * @param null $expectedEtags
     * @param null $ifNoneMatchValue
     * @return mixed|null|Provider
     */
    public function testGetEtags($expectedEtags = null, $ifNoneMatchValue = null)
    {
        if ($provider = $this->startProvide(__METHOD__)) {
            return $provider
                ->provide(array(array('maison', 'bleu', '@-^$'), 'maison, bleu, @-^$'))
                ->endProvide();
        }

        $request = $this->getRequestObjectByServerVariables(array(
            'server' => array(
                'HTTP_IF_NONE_MATCH' => $ifNoneMatchValue
            )
        ));

        $etags = $request->getETags();

        $this->assertEquals($expectedEtags, $etags);
    }

    /**
     * @dataProvider testIsNoCache
     *
     * @param null $expected
     * @param null $serverVars
     * @return mixed|null|Provider
     */
    public function testIsNoCache($expected = null, $headers = null)
    {
        if ($provider = $this->startProvide(__METHOD__)) {
            return $provider
                ->provide(array(false, array()))
                ->provide(array(true, array('pragma' => 'no-cache')))
                ->provide(array(true, array('cache-control' => 'no-cache')))
                ->endProvide();
        }

        $request = $this->getRequestObjectByServerVariables(array(
        ));

        $request->headers->add($headers);

        $this->assertEquals($expected, $request->isNoCache());
    }

    /**
     * @dataProvider testGetLanguages
     *
     * @param null $expectedLanguages
     * @param null $serverVars
     * @return mixed|null|Provider
     */
    public function testGetLanguages($expectedLanguages = null, $serverVars = null)
    {
        if ($provider = $this->startProvide(__METHOD__)) {
            return $provider
                ->provide(array(array(), array('HTTP_ACCEPT_LANGUAGE' => '')))
                ->provide(array(array('en_US', 'en'), array('HTTP_ACCEPT_LANGUAGE' => 'en-us,en;q=0.5')))
                ->provide(array(array('en_US', 'en'), array('HTTP_ACCEPT_LANGUAGE' => 'en;q=0.5,en-us;q=1.0')))
                ->provide(array(array('en_US', 'en'), array('HTTP_ACCEPT_LANGUAGE' => 'en-us,en')))
                ->endProvide();
        }

        $request = $this->getRequestObjectByServerVariables(array(
            'server' => $serverVars
        ));

        $this->assertEquals($expectedLanguages, $request->getLanguages());
    }

    /**
     * @dataProvider testGetPreferedLanguage
     *
     * @param null $expectedPreferedLanaguage
     * @param null $serverVars
     */
    public function testGetPreferedLanguage($expectedPreferedLanaguage = null, $serverVars = null)
    {
        if ($provider = $this->startProvide(__METHOD__)) {
            return $provider
                ->provide(array('en_US', array()))
                ->provide(array('fr', array('HTTP_ACCEPT_LANGUAGE' => 'fr, en')))
                ->provide(array('en', array('HTTP_ACCEPT_LANGUAGE' => 'en, fr')))
                ->provide(array('en', array('HTTP_ACCEPT_LANGUAGE' => array('fr;q=0.1,en;q=0.2,de;q=0.1,it;q=0.2'))))
                ->endProvide();
        }

        $request = $this->getRequestObjectByServerVariables(array(
            'server' => $serverVars
        ));

        $this->assertEquals($expectedPreferedLanaguage, $request->getPreferredLanguage());
    }

    /**
     * @dataProvider testGetCharsets
     *
     * @param null $expectedCharsets
     * @param null $serverVars
     * @return mixed|null|Provider
     */
    public function testGetCharsets($expectedCharsets = null, $serverVars = null)
    {
        if ($provider = $this->startProvide(__METHOD__)) {
            return $provider
                ->provide(array(array('ISO-8859-1', 'utf-8', '*'), array('HTTP_ACCEPT_CHARSER' => 'ISO-8859-1,utf-8;q=0.7,*;q=0.7')))
                ->provide(array(array('utf-8'), array('HTTP_ACCEPT_CHARSET' => 'utf-8')))
                ->provide(array(array('ISO-8859-1', 'utf-8'), array('HTTP_ACCEPT_CHARSET' => 'utf-8;q=0.5,ISO-8859-1;q=1.0')))
                ->endProvide();
        }

        $request = $this->getRequestObjectByServerVariables(array(
            'server' => $serverVars
        ));

        $this->assertEquals($expectedCharsets, $request->getCharsets());
    }

    /**
     * @dataProvider testGetEncodings
     *
     * @param null $expectedEncodings
     * @param null $serverVars
     * @return mixed|null|Provider
     */
    public function testGetEncodings($expectedEncodings = null, $serverVars = null)
    {
        if ($provider = $this->startProvide(__METHOD__)) {
            return $provider
                ->provide(array(array(), array()))
                ->provide(array(array('compress'), array('HTTP_ACCEPT_ENCODING' => 'compress')))
                ->endProvide();
        }

        $request = $this->getRequestObjectByServerVariables(array(
            'server' => $serverVars
        ));

        $this->assertEquals($expectedEncodings, $request->getEncodings());
    }

    /**
     * @dataProvider testGetAcceptableContentTypes
     *
     * @param null $expectedAccetableContentTypes
     * @param null $serverVars
     * @return mixed|null|Provider
     */
    public function testGetAcceptableContentTypes($expectedAccetableContentTypes = null, $serverVars = null)
    {
        if ($provider = $this->startProvide(__METHOD__)) {
            return $provider
                ->provide(array(array('text/html'), array('HTTP_ACCEPT' => 'text/html')))
                ->provide(array(array('text/css', 'text/html'), array('HTTP_ACCEPT' => 'text/html;q=0.5,text/css;q=1.0')))
                ->endProvide();
        }

        $request = $this->getRequestObjectByServerVariables(array(
            'server' => $serverVars
        ));

        $this->assertEquals($expectedAccetableContentTypes, $request->getAcceptableContentTypes());
    }

    /**
     * @dataProvider testIsXmlHttpRequest
     *
     * @param null $expectedIsXmlHttpRequest
     * @param null $serverVars
     * @return mixed|null|Provider
     */
    public function testIsXmlHttpRequest($expectedIsXmlHttpRequest = null, $serverVars = null)
    {
        if ($provider = $this->startProvide(__METHOD__)) {
            return $provider
                ->provide(array(false, array()))
                ->provide(array(true, array('HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest')))
                ->provide(array(false, array('HTTP_X_REQUESTED_WITH' => 'XML-HttpRequest')))
                ->endProvide();
        }

        $request = $this->getRequestObjectByServerVariables(array(
            'server' => $serverVars
        ));

        $this->assertEquals($expectedIsXmlHttpRequest, $request->isXmlHttpRequest());
    }
}