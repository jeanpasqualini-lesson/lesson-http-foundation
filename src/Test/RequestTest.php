<?php
/**
 * Created by PhpStorm.
 * User: darkilliant
 * Date: 3/13/15
 * Time: 9:00 AM
 */

namespace Test;

use Factory\RequestFactory;
use Interfaces\TestInterface;
use Symfony\Component\HttpFoundation\AcceptHeader;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class RequestTest implements TestInterface {

    private $request;

    public function runTest()
    {
        $this->request = RequestFactory::createRequest();

        $this->printSeparator("one");

        $this->testOne();

        $this->printSeparator("two");

        $this->testTwo();

        $this->printSeparator("three");

        $this->testThree();
    }

    public function printSeparator($name)
    {
        echo "===$name===\n";
    }

    /**
     * @return Request
     */
    public function getRequest()
    {
        return $this->request;
    }

    public function testOne()
    {
        $request = $this->getRequest();

        $keys = array("request", "query", "cookies", "attributes", "files", "server", "headers");

        foreach($keys as $key)
        {
            echo "$key : ".implode(",", array_keys($request->{$key}->all())).PHP_EOL;
        }
    }

    public function testTwo()
    {
        $attributes = new ParameterBag();

        $attributes->replace(array(
            "init" => "init",
            "other" => "other",
            "cinq" => "5",
            "alphanum" => "abcdefghijklmnopqrstuvwxyz0123456789",
            "email" => array(
                "valid" => "dsqdqs@qsdqsd.fr",
                "notvalid" => "sqdqsdsd"
            )
        ));

        $attributes->add(array(
            "plus" => "+"
        ));

        $attributes->remove("init");

        echo "has : ".var_export($attributes->has("plus"), true).PHP_EOL;

        echo "all : ".print_r($attributes->all(), true).PHP_EOL;

        echo "typebeforfeint : ".gettype($attributes->get("cinq")).PHP_EOL;

        echo "typeafterinit : ".gettype($attributes->getInt("cinq")).PHP_EOL;

        echo "alpha : ".$attributes->getAlpha("alphanum").PHP_EOL;

        echo "num : ".$attributes->getDigits("alphanum").PHP_EOL;

        echo "filtervalid : ".$attributes->get("email[valid]", null, true, FILTER_VALIDATE_EMAIL).PHP_EOL;

        echo "filterinvalid : ".$attributes->get("email[invalid]", "invalid email", true, FILTER_VALIDATE_EMAIL).PHP_EOL;

    }

    public function testThree()
    {
        $request = $this->getRequest();

        echo "pathinfo : ".$request->getPathInfo().PHP_EOL;

        $request->overrideGlobals();

        echo "acceptable content type : ".implode(",", $request->getAcceptableContentTypes()).PHP_EOL;

        echo "acceptable language : ".implode(",", $request->getLanguages()).PHP_EOL;

        echo "acceptables charset : ".implode(",", $request->getCharsets()).PHP_EOL;

        echo "acceptables encoding : ".implode(",", $request->getEncodings()).PHP_EOL;

        $accept = AcceptHeader::fromString(
            $request->headers->get("Accept")
        );

        if($accept->has("text/html"))
        {
            $item = $accept->get("text/html");
            $charset = $item->getAttribute("charset", "utf-8");
            $quality = $item->getQuality();
        }
    }


}