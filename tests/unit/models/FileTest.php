<?php

namespace models;

use nms\filepond\models\File;
use nms\filepond\models\Session;
use Yii;
use yii\base\ErrorException;
use yii\base\Exception;
use yii\web\UploadedFile;

class FileTest extends \Codeception\Test\Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    protected function _before()
    {
    }

    protected function _after()
    {
    }

    protected function getFileStub($sessionStub)
    {
        $fileStub = $this->getMockBuilder(File::class)
            ->setMethods(['initSession', 'getSession'])
            ->getMock();

        $fileStub->method('getSession')->willReturn($sessionStub);

        return $fileStub;
    }

    protected function getFile()
    {
        return new UploadedFile([
            'name' => 'file.txt',
            'type' => 'plain/text',
            'size' => 1024,
            'tempName' => '/tmp/phpQwetry',
            'error' => UPLOAD_ERR_OK,
        ]);
    }

    public function testValidateFileWithoutSavedParams()
    {
        $sessionStub = $this->createMock(Session::class);
        $sessionStub->method('loadParams')->willReturn([]);
        $fileStub = $this->getFileStub($sessionStub);
        $fileStub->validateFile('file', []);
        $this->assertFalse($fileStub->hasErrors());
    }

    public function testValidateFileSavedParamsAreCorrupted()
    {
        $sessionStub = $this->createMock(Session::class);
        $sessionStub->method('loadParams')->willReturn(['file' => false]);
        $fileStub = $this->getFileStub($sessionStub);
        $fileStub->validateFile('file', []);
        $this->assertNotEmpty($fileStub->getFirstError('file'));
    }

    public function testValidateFileLoadMoreFilesThanSpecified()
    {
        $sessionStub = $this->createMock(Session::class);
        $sessionStub->method('loadParams')->willReturn(['file' => ['maxFiles' => 1, 'tooMany' => 'message']]);
        $sessionStub->method('count')->willReturn(1);
        $fileStub = $this->getFileStub($sessionStub);
        $fileStub->validateFile('file', []);
        $this->assertNotEmpty($fileStub->getFirstError('file'));
    }

    public function testValidateFileTypeFile()
    {
        $sessionStub = $this->createMock(Session::class);
        $sessionStub->method('loadParams')->willReturn(['file' => ['maxFiles' => 0]]);
        $fileStub = $this->getFileStub($sessionStub);
        $fileStub->file = $this->getFile();
        $fileStub->validateFile('file', []);
        $this->assertFalse($fileStub->hasErrors());
    }

    public function testValidateFileTypeImage()
    {
        $sessionStub = $this->createMock(Session::class);
        $sessionStub->method('loadParams')->willReturn(['image' => ['maxFiles' => 0, 'maxSize' => 512]]);
        $fileStub = $this->getFileStub($sessionStub);
        $fileStub->file = $this->getFile();
        $fileStub->validateFile('file', []);
        $this->assertNotEmpty($fileStub->getFirstError('file'));
    }

    public function testProcessValidateError()
    {
        $sessionStub = $this->createMock(Session::class);
        $sessionStub->method('loadParams')->willReturn(['file' => false]);
        $fileStub = $this->getFileStub($sessionStub);
        $this->expectException(ErrorException::class);
        $fileStub->process();
    }

    public function testProcessSaveError()
    {
        // TODO: test, that file can't saved, when there is not system permitions
        /*$sessionStub = $this->createMock(Session::class);
        $sessionStub->method('loadParams')->willReturn(['file' => ['maxFiles' => 0]]);
        $fileStub = $this->getFileStub($sessionStub);
        $tmpFileName = tempnam('/tmp', 'php');
        $fileStub->file = new UploadedFile([
            'name' => 'file.txt',
            'type' => 'plain/text',
            'size' => filesize($tmpFileName),
            'tempName' => $tmpFileName,
            'error' => UPLOAD_ERR_OK,
        ]);

        chmod(Yii::getAlias('@runtime'), 0000);

        sleep(5);

        $this->expectException(Exception::class);
        $fileStub->process();

        chmod(Yii::getAlias('@runtime'), 0775);
        unlink($tmpFileName);*/
    }
}
