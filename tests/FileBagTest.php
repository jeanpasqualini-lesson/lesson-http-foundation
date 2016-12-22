<?php

use Symfony\Component\HttpFoundation\FileBag;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileBagTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var FileBag
     */
    protected $fileBag;

    /**
     * @var UploadedFile
     */
    protected $uploadedFile;

    public function setUp()
    {
        $this->fileBag = new FileBag();
        $this->uploadedFile = $this->getMockBuilder(UploadedFile::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function testSetWithUploaderFile()
    {
        $this->fileBag->set('file', $this->uploadedFile);

        $this->assertEquals($this->uploadedFile, $this->fileBag->get('file'));
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testSetWithNotSupportedValue()
    {
        $this->fileBag->set('file', 'fichier.jpg');
    }

    protected function createTempFile()
    {
        return tempnam(sys_get_temp_dir().'/form_test', 'FormTest');
    }

    public function testSetWithArrayPhpFile()
    {
        $tmpFile = $this->createTempFile();
        $file = new UploadedFile($tmpFile, basename($tmpFile), 'text/plain', 100, 0);

        $this->fileBag->set('file', array(
            'name' => basename($tmpFile),
            'type' => 'text/plain',
            'tmp_name' => $tmpFile,
            'error' => 0,
            'size' => 100,
        ));

        $uploadedFile = $this->fileBag->get('file');

        $this->assertInstanceOf(UploadedFile::class, $uploadedFile);
        $this->assertEquals($file, $uploadedFile);
    }
}