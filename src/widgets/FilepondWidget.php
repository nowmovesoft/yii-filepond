<?php

namespace nms\filepond\widgets;

use nms\filepond\assets\YiiFilePondAsset;
use nms\filepond\helpers\PluginsMapper;
use nms\filepond\helpers\ValidatorHelper;
use nms\filepond\models\File;
use nms\filepond\models\Session;
use nms\filepond\Module;
use yii\helpers\FileHelper;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\helpers\Url;
use yii\web\JsExpression;
use yii\widgets\InputWidget;

/**
 * FilePond widget.
 * @author Michael Naumov <vommuan@gmail.com>
 */
class FilepondWidget extends InputWidget
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
     * @var string Input element, that should be replaced by FilePond
     */
    private $inputElement = 'input[type="file"]';

    /**
     * @var array FilePond options
     */
    public $filePond = [];

    /**
     * @var array Widget connection options (Via model or standalone form)
     */
    private $connection = ['standalone' => false];

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        $this->generateClientRules();
        $this->initConnection();
        $this->generateServerOptions();
    }

    /**
     * Initialize connection type and data for each of these types.
     */
    private function initConnection()
    {
        $this->connection['model'] = new File();
        $this->connection['multiple'] = isset($this->filePond['maxFiles']);

        if (!isset($this->model, $this->attribute, $this->field)) {
            $this->connection['standalone'] = true;
            $this->connection['formId'] = $this->id;
            $this->connection['fieldId'] = Html::getInputId($this->connection['model'], 'file');
            return;
        }

        $session = new Session([
            'validator' => ValidatorHelper::getValidator($this->model, $this->attribute, 'nms\filepond\validators\FilepondValidator'),
        ]);

        $session->saveParams();

        $this->connection['formId'] = $this->field->form->id;
        $this->connection['fieldId'] = Html::getInputId($this->model, $this->attribute);
        $this->connection['fieldName'] = Html::getInputName($this->model, $this->attribute . ($this->connection['multiple'] ? '[]' : ''));
    }

    /**
     * Generates server options.
     */
    private function generateServerOptions()
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
                        $("#' . $this->connection['formId'] . '").yiiActiveForm("updateAttribute", "' . $this->connection['fieldId'] . '", [response.message]);
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
     * Generates validation rules for FilePond by model.
     */
    private function generateClientRules()
    {
        $validator = ValidatorHelper::getValidator($this->model, $this->attribute, 'nms\filepond\validators\FilepondValidator');

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
     * Registers assets, using by widget
     */
    private function registerAssets()
    {
        YiiFilePondAsset::register($this->view);
        PluginsMapper::register($this->filePond, $this->view);
    }

    /**
     * {@inheritdoc}
     */
    public function run()
    {
        $this->registerAssets();
        $options = Json::encode($this->filePond);
        $this->view->registerJs("
            FilePond.create(document.querySelector('{$this->inputElement}'), {$options});
            $('#{$this->connection['formId']}').submit((event) => {
                $('[name=\"File[file]\"]').attr('name', '{$this->connection['fieldName']}');
                return true;
            });
        ", $this->view::POS_END, 'filepond-widget');

        return $this->render('filepond', [
            'connection' => $this->connection,
            'field' => $this->field,
        ]);
    }
}
