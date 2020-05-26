<?php

namespace nms\filepond\assets;

use yii\web\AssetBundle;

/**
 * Asset for Yii FilePond module.
 * @author Michael Naumov <vommuan@gmail.com>
 */
class YiiFilePondAsset extends AssetBundle
{
    /**
     * {@inheritdoc}
     */
    public $sourcePath = __dir__ . '/sources/yii-filepond';

    /**
     * {@inheritdoc}
     */
    public $js = [
        'js/yii-filepond.js',
    ];

    /**
     * {@inheritdoc}
     */
    public $depends = [
        'nms\filepond\FilePondAsset',
        'yii\web\JqueryAsset',
    ];
}
