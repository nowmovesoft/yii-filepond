<?php

namespace models;

use nms\filepond\models\Session;
use Yii;
use yii\base\ErrorException;
//use yii\base\DynamicModel;
use yii\validators\Validator;
use yii\web\UploadedFile;

class SessionTest extends \Codeception\Test\Unit
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

    public function testInitId()
    {
        $session = new Session(['id' => '12345678']);
        $this->assertEquals('12345678', $session->id);
        $session->flush();
    }

    public function testInitIdViaHttpHeader()
    {
        Yii::$app->request->headers->set('X-Session-Id', '12345678');
        $session = new Session();
        $this->assertEquals('12345678', $session->id);
        $session->flush();
    }

    public function testInitIdGenerateId()
    {
        $session = new Session();
        $this->assertEquals(Session::SESSION_ID_LENGTH, strlen($session->id));
        $session->flush();
    }

    /**
     * @depends testInitId
     * @depends testInitIdViaHttpHeader
     * @depends testInitIdGenerateId
     */
    public function testGetPrefix()
    {
        $session = new Session();
        $this->assertEquals(strlen('filepond.') + Session::SESSION_ID_LENGTH, strlen($session->prefix));
        $session->flush();
    }

    public function testSaveParams()
    {
        $session = new Session();
        $session->saveParams();
        $params = Yii::$app->session[$session->prefix];
        $this->assertEmpty($params['validators']);
        $session->flush();

        $session = new Session([
            'validators' => [
                'required' => Validator::createValidator('required', null, ['attr']),
                'file' => null,
            ],
        ]);

        $session->saveParams();
        $params = Yii::$app->session[$session->prefix];
        $this->assertCount(1, $params['validators']);
        $session->flush();

        $session = new Session([
            'validators' => [
                'required' => Validator::createValidator('required', null, ['attr']),
                'file' => Validator::createValidator('file', null, ['attr']),
            ],
        ]);

        $session->saveParams();
        $params = Yii::$app->session[$session->prefix];
        $this->assertCount(2, $params['validators']);
        $session->flush();
    }

    /**
     * @depends testSaveParams
     */
    public function testLoadParams()
    {
        $session = new Session();
        $session->saveParams();
        $this->assertEmpty($session->loadParams());
        $session->flush();

        $session = new Session([
            'validators' => [
                'required' => Validator::createValidator('required', null, ['attr']),
            ],
        ]);

        $session->saveParams();
        $this->assertCount(1, $session->loadParams());
        $session->flush();
    }

    public function testCount()
    {
        $session = new Session();
        $params = Yii::$app->session[$session->prefix];
        $this->assertNull($params);
        $this->assertEquals(0, $session->count());
        $session->inc();
        $params = Yii::$app->session[$session->prefix];
        $this->assertArrayHasKey('count', $params);
        $this->assertEquals(1, $session->count());
        $session->dec();
        $this->assertEquals(0, $session->count());
        $session->flush();
    }

    public function testDecBeforeInc()
    {
        $session = new Session();
        $this->expectException(ErrorException::class);
        $session->dec();
        $session->flush();
    }

    public function testDecLessThanZero()
    {
        $session = new Session();
        $session->inc();
        $session->dec();
        $this->expectException(ErrorException::class);
        $session->dec();
        $session->flush();
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

    public function testAddFile()
    {
        $session = new Session();
        $session->addFile('phpQwerty.txt', $this->getFile());
        $params = Yii::$app->session[$session->prefix];
        $this->assertArrayHasKey('phpQwerty.txt', $params['files']);
        $this->assertCount(5, $params['files']['phpQwerty.txt']);
        $session->flush();
    }

    /**
     * @depends testAddFile
     */
    public function testGetFiles()
    {
        $session = new Session();
        $this->assertEmpty($session->getFiles());
        $session->addFile('phpQwerty.txt', $this->getFile());
        $this->assertCount(1, $session->getFiles());
        $session->flush();
    }

    /**
     * @depends testAddFile
     */
    public function testRemoveFile()
    {
        $session = new Session();
        $session->addFile('phpQwerty.txt', $this->getFile());
        $session->removeFile('phpAsdfgh.txt');
        $this->assertCount(1, $session->getFiles());
        $session->removeFile('phpQwerty.txt');
        $this->assertEmpty($session->getFiles());
        $session->flush();
    }

    public function testFlush()
    {
        $session = new Session();
        $session->saveParams();
        $params = Yii::$app->session[$session->prefix];
        $this->assertNotNull($params);
        $session->flush();
        $params = Yii::$app->session[$session->prefix];
        $this->assertNull($params);
    }
}
