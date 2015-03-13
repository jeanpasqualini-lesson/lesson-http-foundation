<?php
/**
 * Created by PhpStorm.
 * User: darkilliant
 * Date: 3/13/15
 * Time: 10:15 AM
 */

namespace Test;


use Factory\RequestFactory;
use Interfaces\TestInterface;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class ResponseTest implements TestInterface {

    private $request;

    /**
     * @return Request
     */
    public function getRequest()
    {
        return $this->request;
    }

    public function runTest()
    {
        $this->request = RequestFactory::createRequest();

        $this->printSeparator("one");

        $this->testOne();

        $this->printSeparator("two");

        $this->testTwo();

        $this->printSeparator("three");

        $this->testThree();

        $this->printSeparator("foor");

        $this->testFoor();
    }

    public function printSeparator($name)
    {
        echo "===$name===\n";
    }

    public function testOne()
    {
        /** @var Request $request */
        $request = $this->getRequest();

        $request->headers->set("if_none_match", "abcdef");

        $response = new Response(
            "Content",
            Response::HTTP_OK,
            array("content-type" => "text/html")
        );

        $response->setContent("Hello word");

        $response->headers->set("Content-Type", "text/plain");

        $response->setStatusCode(Response::HTTP_NOT_FOUND);

        $response->setCharset("ISO-8859-1");

        $response->headers->setCookie(new Cookie("foo", "bar"));

        $response->setCache(array(
            "etag" => "abcdef",
            "last_modified" => new \DateTime(),
            "max_age" => 600,
            "s_maxage" => 600,
            "private" => false,
            "public" => true
        ));

        if($response->isNotModified($request))
        {
            echo "result cache : ".$response.PHP_EOL;
        }

        echo "result : ".$response.PHP_EOL;
    }

    public function testTwo()
    {
        $response = new Response();

        $response->headers->set("Content-Disposition", $response->headers->makeDisposition(
           ResponseHeaderBag::DISPOSITION_ATTACHMENT,
           "foo.pdf"
        ));

        echo "result : ".$response.PHP_EOL;
    }

    public function testThree()
    {
        $response = new BinaryFileResponse(ROOT_DIR.DIRECTORY_SEPARATOR."data".DIRECTORY_SEPARATOR."image.jpg");

        $response->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            "filename.jpg"
        );

        $response->deleteFileAfterSend(true);

        BinaryFileResponse::trustXSendfileTypeHeader();

        echo "result : ".$response.PHP_EOL;
    }

    public function testFoor()
    {
        $response = new JsonResponse();

        $response->setData(array(
            "data" => 123
        ));

        $response->setCallback("handleResponse");

        echo "result: ".$response.PHP_EOL;
    }
}