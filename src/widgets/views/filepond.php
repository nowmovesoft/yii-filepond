<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */

if (!$connection['standalone']) {
    echo Html::activeFileInput($connection['model'], 'file');
    echo Html::activeHiddenInput($field->model, $field->attribute);
} else {
    $form = ActiveForm::begin(['id' => $connection['formId']]);
    echo $form->field($connection['model'], 'file')->fileInput()->label(false);
    ActiveForm::end();
}
