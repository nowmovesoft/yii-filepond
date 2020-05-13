<?php

namespace nms\filepond\models;

use nms\filepond\models\Session;
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
     * Adds validation rules for FilePond by model.
     * @param FilepondValidator $validator
     */
    public function addValidatorOptions($validator)
    {
        if (is_null($validator) || !$validator->enableClientValidation) {
            return;
        }

        $session = new Session(['validator' => $validator]);
        $session->saveParams();

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
     * Makes JSON configuration for FilePond connection
     * @return json
     */
    public function make()
    {
        return Json::encode($this->filePond);
    }
}
