<?php

use nms\filepond\helpers\ValidatorHelper;
use yii\base\DynamicModel;

class ValidatorHelperTest extends \Codeception\Test\Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    /**
     * @var DinamicModel Stub model
     */
    protected $model;

    protected function _before()
    {
        $this->model = new DynamicModel(['attr' => 'value']);
        $this->model
            ->addRule(['attr'], 'required')
            ->addRule(['attr'], 'string');
    }

    protected function _after()
    {
    }

    public function testGetMethod()
    {
        $this->assertInstanceOf('yii\validators\RequiredValidator', ValidatorHelper::get($this->model, 'attr', 'yii\validators\RequiredValidator'));
        $this->assertInstanceOf('yii\validators\StringValidator', ValidatorHelper::get($this->model, 'attr', 'yii\validators\StringValidator'));
        $this->assertNull(ValidatorHelper::get(null, 'attr', 'yii\validators\Validator'));
        $this->assertNull(ValidatorHelper::get($this->model, null, 'yii\validators\Validator'));
        $this->assertNull(ValidatorHelper::get($this->model, 'attr', ''));
        $this->assertNull(ValidatorHelper::get($this->model, 'attr', 'yii\validators\FileValidator'));
    }
}
