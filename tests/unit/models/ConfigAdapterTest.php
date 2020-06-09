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
}
