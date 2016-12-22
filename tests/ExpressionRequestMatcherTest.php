<?php
namespace tests;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\HttpFoundation\ExpressionRequestMatcher;
use Symfony\Component\HttpFoundation\Request;

/**
 * Created by PhpStorm.
 * User: aurore
 * Date: 26/12/2016
 * Time: 11:18
 */
class ExpressionRequestMatcherTest extends \PHPUnit_Framework_TestCase
{
    use \CreateProviderTrait;

    /** @var ExpressionRequestMatcher */
    protected $expressionRequestMatcher;

    /** @var Request */
    protected $request;

    public function setUp()
    {
        $this->expressionRequestMatcher = new ExpressionRequestMatcher();

        $this->request = Request::create('/bad', 'POST', array(
            'address' => '3 Avenue Victor Hugo'
        ), array(), array(), array());
    }

    /**
     * @dataProvider testMatches
     *
     * @param null $expected
     * @param null $expression
     * @return mixed|null|\Provider
     */
    public function testMatches($expected = null, $expression = null)
    {
        if ($provider = $this->startProvide(__METHOD__))
        {
            return $provider
                ->provide(array(true, 'request.isMethod("POST")'))
                ->provide(array(true, 'method === "POST"'))
                ->provide(array(true, 'method matches "/PoSt/i"'))
                ->provide(array(false, 'request.isMethod("GET")'))
                ->provide(array(true, 'path === "/bad"'))
                ->provide(array(true, 'path matches "/ad/"'))
                ->provide(array(true, 'host === "localhost"'))
                ->provide(array(true, 'ip === "127.0.0.1"'))
                ->provide(array(true, 'request.request.getInt("address") === 3'))
                ->endProvide();
        }

        $this->expressionRequestMatcher->setExpression(new ExpressionLanguage(), $expression);

        $this->assertEquals($expected, $this->expressionRequestMatcher->matches($this->request));
    }
}