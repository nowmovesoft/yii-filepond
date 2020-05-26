<?php

namespace nms\filepond\widgets;

use nms\filepond\assets\YiiFilePondAsset;
use nms\filepond\helpers\PluginsMapper;
use nms\filepond\models\ConfigAdapter;
use nms\filepond\models\File;
use yii\helpers\Html;
use yii\helpers\Json;
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
        $this->config->addValidators($this->model, $this->attribute);
        $this->initConnection();
    }

    /**
     * Initialize connection type and data for each of these types.
     */
    private function initConnection()
    {
        $this->connection['frontend']['endpoints'] = $this->config->getEndpoints();
        $this->connection['backend']['model'] = new File();

        if (isset($this->field)) {
            $this->connection['common']['fieldId'] = "{$this->id}-" . Html::getInputId($this->connection['backend']['model'], 'file');
            $this->connection['frontend']['sessionId'] = $this->config->getSessionId();
            $this->model[$this->attribute] = $this->config->getSessionId();
        } else {
            $this->connection['backend']['standalone'] = true;
            $this->connection['backend']['formId'] = $this->id;
        }
    }

    /**
     * Registers assets, using by widget.
     */
    private function registerAssets()
    {
        YiiFilePondAsset::register($this->view);
        PluginsMapper::register($this->config->filePond, $this->view);

        $options = $this->config->make();
        $connection = Json::encode(array_merge($this->connection['common'], $this->connection['frontend']));

        $this->view->registerJs("YiiFilePond.register({$options}, {$connection});", $this->view::POS_END);
    }

    /**
     * {@inheritdoc}
     */
    public function run()
    {
        $this->registerAssets();

        return $this->render('filepond', [
            'connection' => array_merge($this->connection['common'], $this->connection['backend']),
            'field' => $this->field,
        ]);
    }
}
