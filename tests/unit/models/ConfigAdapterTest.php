<?php

use nms\filepond\models\ConfigAdapter;
use yii\base\DynamicModel;

class ConfigAdapterTest extends \Codeception\Test\Unit
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

    public function testInit()
    {
        $adapter = new ConfigAdapter();
        $this->assertEquals('File[file]', $adapter->filePond['name']);
    }

    public function testAddName()
    {
        $adapter = new ConfigAdapter([
            'filePond' => [
                'name' => 'pond',
            ],
        ]);

        $this->assertEquals('pond', $adapter->filePond['name']);
    }

    protected function getModel($rules = [])
    {
        $model = new DynamicModel(['file']);

        foreach ($rules as $rule) {
            $model->addRule($rule[0], $rule[1], array_slice($rule, 2));
        }

        return $model;
    }

    protected function filePondDefaultConfig()
    {
        return ['name' => 'File[file]'];
    }

    /**
     * @depends testAddRequiredValidator
     * @depends testAddValidatorEquals
     * @depends testAddFileValidatorArrayKeys
     */
    public function testAddValidators()
    {
        //$adapter = new ConfigAdapter();
    }

    public function testAddRequiredValidator()
    {
        $model = $this->getModel();

        $adapter = new ConfigAdapter();
        $adapter->addValidators($model, 'file');
        $this->assertArrayNotHasKey('required', $adapter->filePond);

        $model = $this->getModel([
            ['file', 'nms\filepond\validators\RequiredValidator', 'enableClientValidation' => false],
        ]);

        $adapter = new ConfigAdapter();
        $adapter->addValidators($model, 'file');
        $this->assertArrayNotHasKey('required', $adapter->filePond);

        $model = $this->getModel([
            ['file', 'nms\filepond\validators\RequiredValidator']
        ]);

        $adapter = new ConfigAdapter();
        $adapter->addValidators($model, 'file');
        $this->assertTrue($adapter->filePond['required']);

        $adapter = new ConfigAdapter([
            'filePond' => [
                'required' => false,
            ],
        ]);

        $adapter->addValidators($model, 'file');
        $this->assertFalse($adapter->filePond['required']);
    }

    public function addValidatorEqualsProvider()
    {
        return [
            [
                [],
                [],
                null,
                $this->filePondDefaultConfig(),
            ],
            [
                [
                    ['file', 'nms\filepond\validators\FileValidator', 'enableClientValidation' => false],
                ],
                [],
                null,
                $this->filePondDefaultConfig(),
            ],
            [
                [
                    ['file', 'nms\filepond\validators\FileValidator', 'mimeTypes' => ['plain/text'], 'extensions' => ['csv']],
                ],
                [
                    'filePond' => [
                        'acceptedFileTypes' => ['image/*'],
                    ],
                ],
                'acceptedFileTypes',
                ['image/*'],
            ],
            [
                [
                    ['file', 'nms\filepond\validators\FileValidator', 'mimeTypes' => ['image/*']],
                ],
                [],
                'acceptedFileTypes',
                ['image/*'],
            ],
            [
                [
                    ['file', 'nms\filepond\validators\FileValidator', 'extensions' => ['png']],
                ],
                [],
                'acceptedFileTypes',
                ['image/png'],
            ],
            [
                [
                    ['file', 'nms\filepond\validators\FileValidator', 'mimeTypes' => ['image/jpeg'], 'extensions' => ['png']],
                ],
                [],
                'acceptedFileTypes',
                ['image/jpeg', 'image/png'],
            ],
            [
                [
                    ['file', 'nms\filepond\validators\FileValidator', 'mimeTypes' => ['image/png'], 'extensions' => ['png', 'jpg']],
                ],
                [],
                'acceptedFileTypes',
                ['image/png', 'image/jpeg'],
            ],
            [
                [
                    ['file', 'nms\filepond\validators\FileValidator', 'minSize' => 1024],
                ],
                [
                    'filePond' => [
                        'minFileSize' => 2048,
                    ],
                ],
                'minFileSize',
                2048,
            ],
            [
                [
                    ['file', 'nms\filepond\validators\FileValidator', 'minSize' => 1024],
                ],
                [],
                'minFileSize',
                1024,
            ],
            [
                [
                    ['file', 'nms\filepond\validators\FileValidator', 'maxSize' => 1024],
                ],
                [
                    'filePond' => [
                        'maxFileSize' => 2048,
                    ],
                ],
                'maxFileSize',
                2048,
            ],
            [
                [
                    ['file', 'nms\filepond\validators\FileValidator', 'maxSize' => 1024],
                ],
                [],
                'maxFileSize',
                1024,
            ],
            [
                [
                    ['file', 'nms\filepond\validators\FileValidator', 'minFiles' => 1],
                ],
                [
                    'filePond' => [
                        'minFiles' => 2,
                    ],
                ],
                'minFiles',
                2,
            ],
            [
                [
                    ['file', 'nms\filepond\validators\FileValidator', 'minFiles' => 1],
                ],
                [],
                'minFiles',
                1,
            ],
            [
                [
                    ['file', 'nms\filepond\validators\FileValidator', 'maxFiles' => 2],
                ],
                [],
                'allowMultiple',
                true,
            ],
            [
                [
                    ['file', 'nms\filepond\validators\FileValidator', 'maxFiles' => 2],
                ],
                [],
                'maxFiles',
                2,
            ],
            [
                [
                    ['file', 'nms\filepond\validators\ImageValidator', 'enableClientValidation' => false],
                ],
                [],
                null,
                $this->filePondDefaultConfig(),
            ],
            [
                [
                    ['file', 'nms\filepond\validators\ImageValidator', 'maxHeight' => 100],
                ],
                [
                    'filePond' => [
                        'imageValidateSizeMaxHeight' => 200,
                    ],
                ],
                'imageValidateSizeMaxHeight',
                200,
            ],
            [
                [
                    ['file', 'nms\filepond\validators\ImageValidator', 'maxHeight' => 100],
                ],
                [],
                'imageValidateSizeMaxHeight',
                100,
            ],
            [
                [
                    ['file', 'nms\filepond\validators\ImageValidator', 'maxWidth' => 100],
                ],
                [
                    'filePond' => [
                        'imageValidateSizeMaxWidth' => 200,
                    ],
                ],
                'imageValidateSizeMaxWidth',
                200,
            ],
            [
                [
                    ['file', 'nms\filepond\validators\ImageValidator', 'maxWidth' => 100],
                ],
                [],
                'imageValidateSizeMaxWidth',
                100,
            ],
            [
                [
                    ['file', 'nms\filepond\validators\ImageValidator', 'minHeight' => 100],
                ],
                [
                    'filePond' => [
                        'imageValidateSizeMinHeight' => 200,
                    ],
                ],
                'imageValidateSizeMinHeight',
                200,
            ],
            [
                [
                    ['file', 'nms\filepond\validators\ImageValidator', 'minHeight' => 100],
                ],
                [],
                'imageValidateSizeMinHeight',
                100,
            ],
            [
                [
                    ['file', 'nms\filepond\validators\ImageValidator', 'minWidth' => 100],
                ],
                [
                    'filePond' => [
                        'imageValidateSizeMinWidth' => 200,
                    ],
                ],
                'imageValidateSizeMinWidth',
                200,
            ],
            [
                [
                    ['file', 'nms\filepond\validators\ImageValidator', 'minWidth' => 100],
                ],
                [],
                'imageValidateSizeMinWidth',
                100,
            ],
        ];
    }

    /**
     * @dataProvider addValidatorEqualsProvider
     */
    public function testAddValidatorEquals($modelConfig, $adapterConfig, $index, $expected)
    {
        $model = $this->getModel($modelConfig);
        $adapter = new ConfigAdapter($adapterConfig);
        $adapter->addValidators($model, 'file');

        if (is_null($index)) {
            $this->assertEquals($expected, $adapter->filePond);
        } else {
            $this->assertEquals($expected, $adapter->filePond[$index]);
        }
    }

    public function testAddFileValidatorArrayKeys()
    {
        $model = $this->getModel([
            ['file', 'nms\filepond\validators\FileValidator'],
        ]);

        $adapter = new ConfigAdapter();
        $adapter->addValidators($model, 'file');
        $this->assertArrayHasKey('maxFileSize', $adapter->filePond);
        $this->assertArrayNotHasKey('acceptedFileTypes', $adapter->filePond);
        $this->assertArrayNotHasKey('minFileSize', $adapter->filePond);
        $this->assertArrayNotHasKey('minFiles', $adapter->filePond);
        $this->assertArrayNotHasKey('labelTooFew', $adapter->filePond);
        $this->assertArrayNotHasKey('allowMultiple', $adapter->filePond);
        $this->assertArrayNotHasKey('maxFiles', $adapter->filePond);

        $model = $this->getModel([
            ['file', 'nms\filepond\validators\FileValidator', 'minFiles' => 1],
        ]);

        $adapter = new ConfigAdapter();
        $adapter->addValidators($model, 'file');
        $this->assertArrayHasKey('labelTooFew', $adapter->filePond);

        $model = $this->getModel([
            ['file', 'nms\filepond\validators\FileValidator', 'maxFiles' => 0],
        ]);

        $adapter = new ConfigAdapter();
        $adapter->addValidators($model, 'file');
        $this->assertArrayNotHasKey('maxFiles', $adapter->filePond);
    }

    public function testAddMessages()
    {
        $adapter = new ConfigAdapter([
            'filePond' => [
                'labelIdle' => 'text-1',
                'labelInvalidField' => 'text-2',
                'labelFileWaitingForSize' => 'text-3',
                'labelFileSizeNotAvailable' => 'text-4',
                'labelFileLoading' => 'text-5',
                'labelFileLoadError' => 'text-6',
                'labelFileProcessing' => 'text-7',
                'labelFileProcessingComplete' => 'text-8',
                'labelFileProcessingAborted' => 'text-9',
                'labelFileProcessingError' => 'text-10',
                'labelFileProcessingRevertError' => 'text-11',
                'labelFileRemoveError' => 'text-12',
                'labelTapToCancel' => 'text-13',
                'labelTapToRetry' => 'text-14',
                'labelTapToUndo' => 'text-15',
                'labelButtonRemoveItem' => 'text-16',
                'labelButtonAbortItemLoad' => 'text-17',
                'labelButtonRetryItemLoad' => 'text-18',
                'labelButtonAbortItemProcessing' => 'text-19',
                'labelButtonUndoItemProcessing' => 'text-20',
                'labelButtonRetryItemProcessing' => 'text-21',
                'labelButtonProcessItem' => 'text-22',
            ],
        ]);

        $adapter->addMessages();
        $this->assertEquals('text-1', $adapter->filePond['labelIdle']);
        $this->assertEquals('text-2', $adapter->filePond['labelInvalidField']);
        $this->assertEquals('text-3', $adapter->filePond['labelFileWaitingForSize']);
        $this->assertEquals('text-4', $adapter->filePond['labelFileSizeNotAvailable']);
        $this->assertEquals('text-5', $adapter->filePond['labelFileLoading']);
        $this->assertEquals('text-6', $adapter->filePond['labelFileLoadError']);
        $this->assertEquals('text-7', $adapter->filePond['labelFileProcessing']);
        $this->assertEquals('text-8', $adapter->filePond['labelFileProcessingComplete']);
        $this->assertEquals('text-9', $adapter->filePond['labelFileProcessingAborted']);
        $this->assertEquals('text-10', $adapter->filePond['labelFileProcessingError']);
        $this->assertEquals('text-11', $adapter->filePond['labelFileProcessingRevertError']);
        $this->assertEquals('text-12', $adapter->filePond['labelFileRemoveError']);
        $this->assertEquals('text-13', $adapter->filePond['labelTapToCancel']);
        $this->assertEquals('text-14', $adapter->filePond['labelTapToRetry']);
        $this->assertEquals('text-15', $adapter->filePond['labelTapToUndo']);
        $this->assertEquals('text-16', $adapter->filePond['labelButtonRemoveItem']);
        $this->assertEquals('text-17', $adapter->filePond['labelButtonAbortItemLoad']);
        $this->assertEquals('text-18', $adapter->filePond['labelButtonRetryItemLoad']);
        $this->assertEquals('text-19', $adapter->filePond['labelButtonAbortItemProcessing']);
        $this->assertEquals('text-20', $adapter->filePond['labelButtonUndoItemProcessing']);
        $this->assertEquals('text-21', $adapter->filePond['labelButtonRetryItemProcessing']);
        $this->assertEquals('text-22', $adapter->filePond['labelButtonProcessItem']);

        Yii::$app->getModule('filepond');
        $adapter = new ConfigAdapter();
        $adapter->addMessages();
        $this->assertArrayHasKey('labelIdle', $adapter->filePond);
        $this->assertArrayHasKey('labelInvalidField', $adapter->filePond);
        $this->assertArrayHasKey('labelFileWaitingForSize', $adapter->filePond);
        $this->assertArrayHasKey('labelFileSizeNotAvailable', $adapter->filePond);
        $this->assertArrayHasKey('labelFileLoading', $adapter->filePond);
        $this->assertArrayHasKey('labelFileLoadError', $adapter->filePond);
        $this->assertArrayHasKey('labelFileProcessing', $adapter->filePond);
        $this->assertArrayHasKey('labelFileProcessingComplete', $adapter->filePond);
        $this->assertArrayHasKey('labelFileProcessingAborted', $adapter->filePond);
        $this->assertArrayHasKey('labelFileProcessingError', $adapter->filePond);
        $this->assertArrayHasKey('labelFileProcessingRevertError', $adapter->filePond);
        $this->assertArrayHasKey('labelFileRemoveError', $adapter->filePond);
        $this->assertArrayHasKey('labelTapToCancel', $adapter->filePond);
        $this->assertArrayHasKey('labelTapToRetry', $adapter->filePond);
        $this->assertArrayHasKey('labelTapToUndo', $adapter->filePond);
        $this->assertArrayHasKey('labelButtonRemoveItem', $adapter->filePond);
        $this->assertArrayHasKey('labelButtonAbortItemLoad', $adapter->filePond);
        $this->assertArrayHasKey('labelButtonRetryItemLoad', $adapter->filePond);
        $this->assertArrayHasKey('labelButtonAbortItemProcessing', $adapter->filePond);
        $this->assertArrayHasKey('labelButtonUndoItemProcessing', $adapter->filePond);
        $this->assertArrayHasKey('labelButtonRetryItemProcessing', $adapter->filePond);
        $this->assertArrayHasKey('labelButtonProcessItem', $adapter->filePond);
    }
}
