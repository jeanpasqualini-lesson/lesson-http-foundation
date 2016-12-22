<?php

use Symfony\Component\HttpFoundation\ParameterBag;

class ParameterBagTest extends PHPUnit_Framework_TestCase
{
    use CreateProviderTrait;

    /** @var ParameterBag */
    protected $parameterBag;

    public function setUp()
    {
        $this->parameterBag = new ParameterBag();
    }

    public function testAll()
    {
        $this->parameterBag->replace(array('un', 'deux', 'trois'));

        $this->assertEquals(array('un', 'deux', 'trois'), $this->parameterBag->all());
    }

    public function testKeys()
    {
        $this->parameterBag->replace(array(
            'Color' => 'Red',
            'Material' => 'Wood'
        ));

        $this->assertEquals(array('Color', 'Material'), $this->parameterBag->keys());
        $this->assertNotEquals(array('color', 'material'), $this->parameterBag->keys());
    }

    public function testAdd()
    {
        $this->parameterBag->replace(array(
            'Color' => 'Red',
            'Material' => 'Wood'
        ));

        $this->parameterBag->add(array(
            'Color' => 'Green',
            'Size' => 'Large'
        ));

        $this->assertEquals(array(
            'Color' => 'Green',
            'Size' => 'Large',
            'Material' => 'Wood'
        ), $this->parameterBag->all());
    }

    /**
     * @dataProvider testGet
     *
     * @param null $expectedValue
     * @param null $gettedKey
     * @param null $defaultValueGetted
     */
    public function testGet($expectedValue = null, $gettedKey = null, $defaultValueGetted = null)
    {
        if ($provider = $this->startProvide(__METHOD__)) {
            return $provider
                ->provide(array('onevalue', 'onekey'))
                ->provide(array('twovalue', 'twokey', 'twovalue'))
                ->provide(array(null, 'threekey'))
                ->endProvide();
        }

        $this->parameterBag->replace(array(
            'onekey' => 'onevalue'
        ));

        $this->assertEquals($expectedValue, $this->parameterBag->get($gettedKey, $defaultValueGetted));
    }

    public function testSet()
    {
        $this->parameterBag->set('cat', 'perle');

        $this->assertEquals('perle', $this->parameterBag->get('cat'));
    }

    public function testHas()
    {
        $this->parameterBag->set('cat', 'perle');

        $this->assertTrue($this->parameterBag->has('cat'));
        $this->assertFalse($this->parameterBag->has('dog'));
    }

    public function testRemove()
    {
        $this->parameterBag->set('cat', 'perle');
        $this->parameterBag->remove('cat');

        $this->assertFalse($this->parameterBag->has('cat'));
    }

    public function testGetAlpha()
    {
        $this->parameterBag->set('address', '3 rue des hirondelles');

        $this->assertEquals('ruedeshirondelles', $this->parameterBag->getAlpha('address'));
        $this->assertInternalType('string', $this->parameterBag->getAlpha('address'));
        $this->assertEquals('', $this->parameterBag->getAlpha('unknow'));
    }

    public function testGetAlnum()
    {
        $this->parameterBag->set('address', '3 rue des hirondelles');

        $this->assertEquals('3ruedeshirondelles', $this->parameterBag->getAlnum('address'));
        $this->assertInternalType('string', $this->parameterBag->getAlnum('address'));
        $this->assertEquals('', $this->parameterBag->getAlnum('unknow'));
    }

    public function testGetDigitas()
    {
        $this->parameterBag->set('address', '3 rue des hirondelles');

        $this->assertEquals('3', $this->parameterBag->getDigits('address'));
        $this->assertInternalType('string', $this->parameterBag->getDigits('address'));
        $this->assertEquals('', $this->parameterBag->getDigits('unknow'));
    }

    public function testGetInt()
    {
        $this->parameterBag->set('address', '3 rue des hirondelles');
        $this->assertEquals(3, $this->parameterBag->getInt('address'));
        $this->assertInternalType('integer', $this->parameterBag->getInt('address'));
        $this->assertEquals(0, $this->parameterBag->getInt('unknow'));
    }

    public function testGetBoolean()
    {
        $this->parameterBag->set('flag', 1);

        $this->assertEquals(true, $this->parameterBag->getBoolean('flag'));
        $this->assertInternalType('boolean', $this->parameterBag->getBoolean('flag'));
        $this->assertEquals(false, $this->parameterBag->getBoolean('unknow'));
    }

    /**
     * @dataProvider testFilter
     *
     * @param null $expectedValue
     * @param null $getterKey
     * @param null $filter
     * @return mixed|null|Provider
     */
    public function testFilter($expectedValue = null, $originalValue = null, $filter = null)
    {
        if ($provider = $this->startProvide(__METHOD__)) {
            return $provider
                ->provide(array(false, 'http://www.google.fr/', FILTER_VALIDATE_EMAIL))
                ->provide(array('test@test.fr', 'test@test.fr', FILTER_VALIDATE_EMAIL))
                ->endProvide();
        }

        $this->parameterBag->set('original', $originalValue);

        $this->assertEquals($expectedValue, $this->parameterBag->filter('original', null, $filter), $this->parameterBag->filter('original', null, $filter));
    }
}