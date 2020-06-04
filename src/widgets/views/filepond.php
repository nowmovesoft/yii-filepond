<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */

if ($connection['standalone']) {
    $form = ActiveForm::begin();
    echo Html::fileInput(null, null, ['id' => $connection['fieldId']]);
    echo Html::hiddenInput("session[{$connection['fieldId']}]", $connection['sessionId']);
    ActiveForm::end();
} else {
    echo Html::fileInput(null, null, ['id' => $connection['fieldId']]);
    echo $field->hiddenInput()->label(false);
}
