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
    public $depends = [
        'nms\filepond\FilePondAsset',
        'yii\web\YiiAsset',
    ];
}
