<?php

namespace nms\filepond\models;

use nms\filepond\helpers\ValidatorHelper;
use nms\filepond\models\File;
use nms\filepond\models\Session;
use nms\filepond\Module;
use yii\base\Model;
use yii\helpers\FileHelper;
use yii\helpers\Html;
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
     * @var FilepondWidget
     */
    public $widget;

    /**
     * Adds server options for FilePond
     * @param array $connetion Connection options
     */
    public function addServerOptions($connection)
    {
        foreach (self::FILEPOND_ENDPOINTS as $endpoint) {
            if (isset($this->filePond['server'][$endpoint])) {
                continue;
            }

            $options = [
                'url' => urldecode(Url::to(['/' . Module::getInstance()->uniqueId . '/filepond/' . $endpoint])),
                'headers' => [
                    'X-CSRF-Token' => new JsExpression('yii.getCsrfToken()'),
                ],
                'onerror' => new JsExpression(
                    '(response) => {
                        response = JSON.parse(response);
                        $("#' . $connection['formId'] . '").yiiActiveForm("updateAttribute", "' . $connection['fieldId'] . '", [response.message]);
                        return response.message;
                    }'
                ),
            ];

            switch ($endpoint) {
                case 'process':
                    $options['onload'] = new JsExpression(
                        '(response) => {
                            response = JSON.parse(response);
                            return response.key;
                        }'
                    );

                    break;
            }

            $this->filePond['server'][$endpoint] = $options;
        }
    }

    /**
     * Adds validation rules for FilePond by model.
     */
    public function addValidatorOptions()
    {
        $validator = ValidatorHelper::getValidator($this->widget->model, $this->widget->attribute, 'nms\filepond\validators\FilepondValidator');

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

        if (!isset($this->filePond['minFileSize'])) {
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
     * Initialize connection type and data for each of these types.
     */
    public function initConnection()
    {
        $connection['model'] = new File();
        $connection['multiple'] = isset($this->filePond['maxFiles']);

        if (!isset($this->widget->model, $this->widget->attribute, $this->widget->field)) {
            $connection['standalone'] = true;
            $connection['formId'] = $widget->id;
            $connection['fieldId'] = Html::getInputId($connection['model'], 'file');
            return;
        }

        $session = new Session([
            'validator' => ValidatorHelper::getValidator($this->widget->model, $this->widget->attribute, 'nms\filepond\validators\FilepondValidator'),
        ]);

        $session->saveParams();

        $connection['formId'] = $this->widget->field->form->id;
        $connection['fieldId'] = Html::getInputId($this->widget->model, $this->widget->attribute);
        $connection['fieldName'] = Html::getInputName($this->widget->model, $this->widget->attribute . ($connection['multiple'] ? '[]' : ''));

        return $connection;
    }

    /**
     * Gets FilePond configuration
     * @return array
     */
    public function get()
    {
        return $this->filePond;
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
