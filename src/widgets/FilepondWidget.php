<?php

namespace nms\filepond\widgets;

use nms\filepond\assets\YiiFilePondAsset;
use nms\filepond\helpers\PluginsMapper;
use nms\filepond\helpers\ValidatorHelper;
use nms\filepond\models\ConfigAdapter;
use nms\filepond\models\File;
use yii\helpers\Html;
use yii\widgets\InputWidget;

/**
 * FilePond widget.
 * @author Michael Naumov <vommuan@gmail.com>
 */
class FilepondWidget extends InputWidget
{
    /**
     * @var array FilePond options
     */
    public $filePond = [];

    /**
     * @var array Widget connection options (Via model or standalone form)
     */
    private $connection = ['standalone' => false];

    /**
     * @var ConfigAdapter
     */
    private $config;

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        $this->field->enableClientValidation = false;
        $this->config = new ConfigAdapter(['filePond' => $this->filePond]);

        $this->config->addValidatorOptions(
            ValidatorHelper::get(
                $this->model,
                $this->attribute,
                'nms\filepond\validators\FileValidator'
            )
        );

        $this->config->addServerOptions();
        $this->initConnection(isset($this->config->filePond['maxFiles']));
    }

    /**
     * Initialize connection type and data for each of these types.
     * @param boolean $multiple Is it a multiple files uploading?
     */
    private function initConnection($multiple)
    {
        $this->connection['model'] = new File();

        if (isset($this->model, $this->attribute, $this->field)) {
            $this->connection['formId'] = $this->field->form->id;
            $this->connection['fieldName'] = Html::getInputName($this->model, $this->attribute . ($multiple ? '[]' : ''));
        } else {
            $this->connection['standalone'] = true;
            $this->connection['formId'] = $this->id;
        }
    }

    /**
     * Registers assets, using by widget
     */
    private function registerAssets()
    {
        YiiFilePondAsset::register($this->view);
        PluginsMapper::register($this->config->filePond, $this->view);
    }

    /**
     * {@inheritdoc}
     */
    public function run()
    {
        $this->registerAssets();
        $this->view->registerJs("
            FilePond.create(document.querySelector('input[type=\"file\"]'), {$this->config->make()});
            $('#{$this->connection['formId']}').submit((event) => {
                $('.filepond--data [name=\"File[file]\"]').attr('name', '{$this->connection['fieldName']}');
                return true;
            });
        ", $this->view::POS_END, 'filepond-widget');

        return $this->render('filepond', [
            'connection' => $this->connection,
            'field' => $this->field,
        ]);
    }
}
