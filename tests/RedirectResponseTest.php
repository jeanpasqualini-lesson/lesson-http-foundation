<?php
namespace tests;

use Symfony\Component\HttpFoundation\RedirectResponse;

class RedirectResponseTest extends \PHPUnit_Framework_TestCase
{
    /** @var RedirectResponse */
    protected $redirectResponse;

    public function setUp()
    {
        $this->redirectResponse = new RedirectResponse('/new');
    }

    public function testSetTargetUrl()
    {
        $this->redirectResponse->setTargetUrl('/chocapic"');

        $this->assertEquals('/chocapic"', $this->redirectResponse->headers->get('location'));
        $this->assertEquals('<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8" />
        <meta http-equiv="refresh" content="1;url=/chocapic&quot;" />

        <title>Redirecting to /chocapic&quot;</title>
    </head>
    <body>
        Redirecting to <a href="/chocapic&quot;">/chocapic&quot;</a>.
    </body>
</html>', $this->redirectResponse->getContent());
    }
}