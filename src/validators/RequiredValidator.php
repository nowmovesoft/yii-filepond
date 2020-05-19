<?php

namespace nms\filepond\validators;

/**
 * Client validation rules are integrated in FilePond instance.
 * @author Michael Naumov <vommuan@gmail.com>
 */
class RequiredValidator extends \yii\validators\RequiredValidator
{
    /**
     * {@inheritdoc}
     */
    public function clientValidateAttribute($model, $attribute, $view)
    {
        return null;
    }
}
