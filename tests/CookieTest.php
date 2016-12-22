<?php
namespace tests;

use Symfony\Component\HttpFoundation\Cookie;

class CookieTest extends \PHPUnit_Framework_TestCase
{
    use \CreateProviderTrait;

    /** @var Cookie */
    protected $cookie;

    public function setUp()
    {
        $this->cookie = new Cookie(
            $name = 'dark',
            $value = 'vador',
            $expire = 86400,
            $path = '/etoile-de-la-mort/',
            $domain = 'cote-obscur.com',
            $secure = true,
            $httpOnly = true
        );
    }

    /**
     * @dataProvider testToString
     *
     * @param null $expected
     * @param null $name
     * @param null $value
     * @param int $expire
     * @param string $path
     * @param null $domain
     * @param bool $secure
     * @param bool $httpOnly
     * @return mixed|null|\Provider
     */
    public function testToString(
        $expected = null,
        $name = null,
        $value = null,
        $expire = 0,
        $path = '/',
        $domain = null,
        $secure = false,
        $httpOnly = true
    ) {
        if ($provider = $this->startProvide(__METHOD__))
        {
            return $provider
                ->provide(
                    array(
                        // Expected result
                        $expected = 'dark=vador; path=/; httponly',
                        // Give parameters
                        $name = 'dark',
                        $value = 'vador'
                    )
                )
                ->provide(
                    array(
                        // Expected result
                        $expected = 'dark=vador; expires=Mon, 26-Dec-2016 10:09:02 GMT; path=/etoile-de-la-mort/; domain=cote-obscur.com; secure; httponly',
                        // Give parameters
                        $name = 'dark',
                        $value = 'vador',
                        $expire = 1482746942,
                        $path = '/etoile-de-la-mort/',
                        $domain = 'cote-obscur.com',
                        $secure = true,
                        $httpOnly = true
                    )
                )
                ->endProvide();
        }

        $cookie = new Cookie($name, $value, $expire, $path, $domain, $secure, $httpOnly);


        $this->assertEquals(
            $expected,
            (string) $cookie
        );
    }

    public function testGetName()
    {
        $this->assertEquals('dark', $this->cookie->getName());
    }

    public function testGetValue()
    {
        $this->assertEquals('vador', $this->cookie->getValue());
    }

    public function testGetDomain()
    {
        $this->assertEquals('cote-obscur.com', $this->cookie->getDomain());
    }

    public function testGetExpiresTime()
    {
        $this->assertEquals(86400, $this->cookie->getExpiresTime());
    }

    public function testGetPath()
    {
        $this->assertEquals('/etoile-de-la-mort/', $this->cookie->getPath());
    }

    public function testIsSecure()
    {
        $this->assertTrue($this->cookie->isSecure());
    }

    public function testIsHttpOnly()
    {
        $this->assertTrue($this->cookie->isHttpOnly());
    }

    public function testIsCleared()
    {
        $this->assertTrue($this->cookie->isCleared());
    }
}