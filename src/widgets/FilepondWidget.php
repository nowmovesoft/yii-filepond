<?php

namespace nms\filepond\widgets;

use nms\filepond\assets\YiiFilePondAsset;
use nms\filepond\helpers\PluginsMapper;
use nms\filepond\models\ConfigAdapter;
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
        $this->config = new ConfigAdapter([
            'filePond' => $this->filePond,
            'widget' => $this,
        ]);
        $this->config->addValidatorOptions();
        $this->connection = $this->config->initConnection();
        $this->config->addServerOptions($this->connection);
    }

    /**
     * Registers assets, using by widget
     */
    private function registerAssets()
    {
        YiiFilePondAsset::register($this->view);
        PluginsMapper::register($this->config->get(), $this->view);
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
