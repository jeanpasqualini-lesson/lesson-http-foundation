<?php
namespace tests;

use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Request;
use PHPUnit_Framework_TestCase;
use Exception;
use LogicException;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class BinaryFileResponseTest extends PHPUnit_Framework_TestCase
{
    /** @var BinaryFileResponse */
    protected $binaryFileResponse;

    /** @var string */
    protected $tempFile;

    public function setUp()
    {
        $this->tempFile = $this->createTempFile();

        $this->binaryFileResponse = BinaryFileResponse::create($this->tempFile);
    }

    public function tearDown()
    {
        unlink($this->tempFile);
    }

    protected function createTempFile()
    {
        return tempnam(sys_get_temp_dir().'/form_test', 'FormTest');
    }

    /**
     * @expectedException Exception
     */
    public function testCreateWithNotExistFile()
    {
        BinaryFileResponse::create('notexistfile.txt');
    }

    public function testCreateWithExistFile()
    {
        BinaryFileResponse::create($this->createTempFile());
    }

    public function testSetFile()
    {
        $filePath = $this->createTempFile();
        touch($filePath, 123456789);
        $file = new File($filePath);

        $this->binaryFileResponse->setFile(
            $filePath,
            $contentDisposition = null,
            $autoEtag = true,
            $autoLastModified = true)
        ;

        $this->assertEquals($file, $this->binaryFileResponse->getFile());
        $this->assertEquals('"da39a3ee5e6b4b0d3255bfef95601890afd80709"', $this->binaryFileResponse->getEtag());
        $this->assertEquals(123456789, $this->binaryFileResponse->getLastModified()->getTimestamp());
    }

    /**
     * @expectedException LogicException
     */
    public function testSetContent()
    {
        $this->binaryFileResponse->setContent('truc');
    }

    public function testDeleteFileAfterSend()
    {
        $filePath = $this->createTempFile();
        $this->binaryFileResponse->setFile($filePath);

        $this->binaryFileResponse->deleteFileAfterSend(false);
        $this->binaryFileResponse->send();
        $this->assertFileExists($filePath);

        $this->binaryFileResponse->deleteFileAfterSend(true);
        $this->binaryFileResponse->send();
        $this->assertFileNotExists($filePath);
    }

    public function testTrustXSendfileTypeHeader()
    {
        BinaryFileResponse::trustXSendfileTypeHeader();

        $request = Request::create('/');
        $request->headers->set('X-Sendfile-Type', 'X-Sendfile');

        $this->binaryFileResponse->prepare($request);

        $this->assertEquals($this->tempFile, $this->binaryFileResponse->headers->get('X-Sendfile'));
    }

    public function testSetContentDispositionGeneratesSafeFallbackFilename()
    {
        $response = new BinaryFileResponse(__FILE__);
        $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, 'föö.html');

        $this->assertSame(
            'attachment; filename="f__.html"; filename*=utf-8\'\'f%C3%B6%C3%B6.html',
            $response->headers->get('Content-Disposition')
        );
    }

    public function testSetContentInline()
    {
        $response = new BinaryFileResponse(__FILE__);
        $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_INLINE, 'föö.html');

        $this->assertSame(
            'inline; filename="f__.html"; filename*=utf-8\'\'f%C3%B6%C3%B6.html',
            $response->headers->get('Content-Disposition')
        );
    }
}