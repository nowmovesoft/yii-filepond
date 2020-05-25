<?php

namespace nms\filepond\models;

use nms\filepond\helpers\ValidatorHelper;
use nms\filepond\Module;
use yii\base\Model;
use yii\helpers\FileHelper;
use yii\helpers\Json;
use yii\helpers\Url;
use yii\web\JsExpression;

/**
 * Adopts widget configuration to FilePond configuration.
 * @author Michael Naumov <vommuan@gmail.com>
 */
class ConfigAdapter extends Model
{
    /**
     * FilePond endpoints list
     */
    const FILEPOND_ENDPOINTS = [
        'process',
        'revert',
        'load',
        'restore',
        'fetch',
        'patch',
    ];

    /**
     * @var array FilePond configuration
     */
    public $filePond;

    /**
     * @var Session Saved state of upload field
     */
    private $session;

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        $this->addName();
    }

    /**
     * Adds `name` option for FilePond.
     */
    public function addName()
    {
        if (!isset($this->filePond['name'])) {
            $this->filePond['name'] = 'File[file]';
        }
    }

    /**
     * Adds validators options to FilePond instance.
     * @param Model $model
     * @param string $attribute
     */
    public function addValidators($model, $attribute)
    {
        $validators = [
            'required' => ValidatorHelper::get($model, $attribute, 'nms\filepond\validators\RequiredValidator'),
            'file' => ValidatorHelper::get($model, $attribute, 'nms\filepond\validators\FileValidator'),
            'image' => ValidatorHelper::get($model, $attribute, 'nms\filepond\validators\ImageValidator'),
        ];

        $this->session = new Session(['validators' => $validators]);
        $this->session->saveParams();

        $this->addRequiredValidator($validators['required']);
        $this->addFileValidator($validators['file']);
        $this->addImageValidator($validators['image']);
    }

    /**
     * Adds `RequiredValidator` rules for FilePond instance.
     * @param nms\filepond\validators\RequiredValidator $validator
     */
    private function addRequiredValidator($validator)
    {
        if (is_null($validator) || !$validator->enableClientValidation) {
            return;
        }

        if (!isset($this->filePond['required'])) {
            $this->filePond['required'] = true;
        }
    }

    /**
     * Adds `FileValidator` rules for FilePond instance.
     * @param nms\filepond\validators\FileValidator $validator
     */
    private function addFileValidator($validator)
    {
        if (is_null($validator) || !$validator->enableClientValidation) {
            return;
        }

        if (!isset($this->filePond['acceptedFileTypes'])) {
            $mimeTypes = $validator->mimeTypes;

            if (!empty($validator->extensions)) {
                foreach ($validator->extensions as $extension) {
                    $mimeTypes[] = FileHelper::getMimeTypeByExtension("file.{$extension}");
                }
            }

            if (!empty($mimeTypes)) {
                $this->filePond['acceptedFileTypes'] = $mimeTypes;
            }
        }

        if (!isset($this->filePond['minFileSize']) && isset($validator->minSize)) {
            $this->filePond['minFileSize'] = $validator->minSize;
        }

        if (!isset($this->filePond['maxFileSize'])) {
            $this->filePond['maxFileSize'] = $validator->getSizeLimit();
        }

        if (!isset($this->filePond['allowMultiple'], $this->filePond['maxFiles']) && 1 !== $validator->maxFiles) {
            $this->filePond['allowMultiple'] = true;

            if (0 !== $validator->maxFiles) {
                $this->filePond['maxFiles'] = $validator->maxFiles;
            }
        }
    }

    /**
     * Adds `ImageValidator` rules for FilePond instance.
     * @param nms\filepond\validators\ImageValidator $validator
     */
    private function addImageValidator($validator)
    {
        if (is_null($validator) || !$validator->enableClientValidation) {
            return;
        }

        $this->addFileValidator($validator);

        if (!isset($this->filePond['imageValidateSizeMaxHeight']) && isset($validator->maxHeight)) {
            $this->filePond['imageValidateSizeMaxHeight'] = $validator->maxHeight;
        }

        if (!isset($this->filePond['imageValidateSizeMaxWidth']) && isset($validator->maxHeight)) {
            $this->filePond['imageValidateSizeMaxWidth'] = $validator->maxWidth;
        }

        if (!isset($this->filePond['imageValidateSizeMinHeight']) && isset($validator->minHeight)) {
            $this->filePond['imageValidateSizeMinHeight'] = $validator->minHeight;
        }

        if (!isset($this->filePond['imageValidateSizeMinWidth']) && isset($validator->minWidth)) {
            $this->filePond['imageValidateSizeMinWidth'] = $validator->minWidth;
        }
    }

    /**
     * Adds server options for FilePond
     * @param string $fieldId FilePond field identifier
     */
    public function addServerOptions($fieldId)
    {
        foreach (self::FILEPOND_ENDPOINTS as $endpoint) {
            if (isset($this->filePond['server'][$endpoint])) {
                continue;
            }

            $options = [
                'url' => urldecode(Url::to(['/' . Module::getInstance()->uniqueId . '/filepond/' . $endpoint])),
                'headers' => [
                    'X-CSRF-Token' => new JsExpression('yii.getCsrfToken()'),
                    'X-Session-Id' => $this->session->id,
                ],
                'onerror' => new JsExpression(
                    "(response) => {
                        response = JSON.parse(response);
                        $('#{$fieldId}').siblings('.help-block').text(response.message);
                        return response.message;
                    }"
                ),
            ];

            switch ($endpoint) {
                case 'process':
                    $options['onload'] = new JsExpression(
                        "(response) => {
                            response = JSON.parse(response);
                            $('#{$fieldId}').siblings('.help-block').text('');
                            return response.key;
                        }"
                    );

                    break;
            }

            $this->filePond['server'][$endpoint] = $options;
        }
    }

    /**
     * Gets session identifier.
     * @return string
     */
    public function getSessionId()
    {
        return $this->session->id;
    }

    /**
     * Makes JSON configuration for FilePond connection
     * @return json
     */
    public function make()
    {
        return Json::encode($this->filePond);
    }
}
