<?php
use Symfony\Component\HttpFoundation\HeaderBag;

class HeaderBagTest extends PHPUnit_Framework_TestCase
{
    /** @var HeaderBag */
    protected $headerBag;

    public function setUp()
    {
        $this->headerBag = new HeaderBag();
    }

    public function tearDown()
    {
        $this->headerBag = null;
    }

    public function testAll()
    {
        $this->headerBag->replace(array(
            'X_FORWARDED_HOST' => 'mami.com'
        ));

        $this->assertArrayHasKey('x-forwarded-host', $this->headerBag->all());
        $this->assertArrayNotHasKey('X_FORWARDED_HOST', $this->headerBag->all());
    }

    public function testSetWithReplaceString()
    {
        $this->headerBag->set('version', 1);

        $this->headerBag->set('version', 2);

        $this->assertEquals(2, $this->headerBag->get('version'));
    }

    public function testSetWithReplaceArray()
    {
        $this->headerBag->set('versions', array(1));

        $this->headerBag->set('versions', array(2));

        $this->assertEquals(array(2), $this->headerBag->get('versions', null, false));
    }

    public function testSetWithNotReplaceString()
    {
        $this->headerBag->set('version', 1);

        $this->headerBag->set('version', 2, false);

        $this->assertEquals(1, $this->headerBag->get('version'));
        $this->assertEquals(array(1, 2), $this->headerBag->get('version', null, false));
    }

    public function testSetWithNotReplaceArray()
    {
        $this->headerBag->set('versions', array(1));

        $this->headerBag->set('versions', array(2), false);

        $this->assertEquals(array(1, 2), $this->headerBag->get('versions', null, false));
    }

    public function testHas()
    {
        $this->headerBag->set('version', 1);

        $this->assertTrue($this->headerBag->has('version'));
    }

    public function testContains()
    {
        $this->headerBag->set('versions', [1, 2]);

        $this->assertTrue($this->headerBag->contains('versions', 1));
    }

    public function testGetDate()
    {
        // DATE_RFC2822(HTTP) : Format D, d M Y H:i:s O
        $this->headerBag->set('date', 'Mon, 15 Aug 2005 15:52:01 GMT');

        $this->assertEquals('15/08/2005', $this->headerBag->getDate('date')->format('d/m/Y'));
    }

    public function testGetIterator()
    {
        $headers = array('foo' => 'bar', 'hello' => 'world', 'third' => 'charm');
        $this->headerBag->replace($headers);

        $i = 0;
        foreach ($this->headerBag as $key => $val) {
            ++$i;
            $this->assertEquals(array($headers[$key]), $val);
        }

        $this->assertEquals(count($headers), $i);
    }

    public function testCount()
    {
        $headers = array('foo' => 'bar', 'hello' => 'world', 'third' => 'charm');
        $this->headerBag->replace($headers);

        $this->assertEquals(count($headers), $this->headerBag->count());
    }

    public function testAddWithExistentKey()
    {
        $headerA = array('foo' => 'bar');
        $headerB = array('bar' => 'foo');
        $expectAll = array('foo' => array('bar'), 'bar' => array('foo'));

        $this->headerBag->add($headerA);
        $this->headerBag->add($headerB);

        $this->assertEquals($expectAll, $this->headerBag->all());
    }

    public function testRemove()
    {
        $this->headerBag->replace(array('foo' => 'bar'));
        $this->headerBag->remove('FoO');

        $this->assertCount(0, $this->headerBag);
    }

    public function testCacheControlDirectiveAccessors()
    {
        $this->headerBag->addCacheControlDirective('public');
        $this->headerBag->addCacheControlDirective('max-age', 10);
        $this->headerBag->addCacheControlDirective('s-max-age', 10);

        // Cache control directory is automaticaly sort by ksort
        $this->assertEquals('max-age=10, public, s-max-age=10', $this->headerBag->get('cache-control'));
    }

    public function testCacheControlDirectiveParsing()
    {
        $this->headerBag->set('cache-control', 'public, Max-age=10');

        $this->assertEquals('public, Max-age=10', $this->headerBag->get('cache-control'));

        $this->headerBag->addCacheControlDirective('s-max-age=10');

        // Cache control directive is automaticaly sort by ksort
        // Cache control directive lowercase key, may not replace _ by -
        $this->assertEquals('max-age=10, public, s-max-age=10', $this->headerBag->get('cache-control'));
    }

    public function testCacheControlDirectiveParsingQuotedZero()
    {
        $this->headerBag->replace(array('cache-control' => 'max-age="0"'));

        $this->assertEquals(0, $this->headerBag->getCacheControlDirective('max-age'));
    }

    public function testCacheControlDirectiveOverrideWithReplace()
    {
        $this->headerBag->replace(array('cache-control' => 'private'));
        $this->headerBag->addCacheControlDirective('s-max-age', 10);
        $this->headerBag->replace(array('cache-control' => 'public'));
        $this->headerBag->addCacheControlDirective('max-age', 10);

        $this->assertEquals('max-age=10, public', $this->headerBag->get('cache-control'));
    }

    public function testToString()
    {
        $this->headerBag->replace(array(
           'Host' => 'mami.com',
           'location' => 'http://www.google.fr/',
        ));

        $actualToString = (string) $this->headerBag;
        $expectedToString =
            'Host:     mami.com'."\r\n".
            'Location: http://www.google.fr/'."\r\n"
        ;

        $this->assertEquals($expectedToString, $actualToString);
    }

    public function testKeys()
    {
        $this->headerBag->replace(array(
            'Host' => 'mami.com',
            'location' => 'http://www.google.fr/',
        ));

        $this->assertEquals(array('host', 'location'), $this->headerBag->keys());
        $this->assertNotEquals(array('Host', 'Location'), $this->headerBag->keys());
    }
}