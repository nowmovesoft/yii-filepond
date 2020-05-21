<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */

if ($connection['standalone']) {
    $form = ActiveForm::begin(['id' => $connection['formId']]);
    echo $form->field($connection['model'], 'file')->fileInput()->label(false);
    ActiveForm::end();
} else {
    echo Html::activeFileInput($connection['model'], 'file');
    echo Html::hiddenInput('session-id', $connection['sessionId']);
}
