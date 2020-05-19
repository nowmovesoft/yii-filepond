<?php

namespace nms\filepond\validators;

/**
 * Client validation rules are integrated in FilePond instance.
 * Server validation rules are integrated in `nms\filepond\models\File` model.
 * It isn't needed to validate rules here. This class is used only as adapter
 * for components, which are specified above.
 * @author Michael Naumov <vommuan@gmail.com>
 */
class ImageValidator extends \yii\validators\ImageValidator
{
    /**
     * {@inheritdoc}
     */
    public function validateAttributes($model, $attributes = null)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function validateAttribute($model, $attribute)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function clientValidateAttribute($model, $attribute, $view)
    {
        return null;
    }
}
