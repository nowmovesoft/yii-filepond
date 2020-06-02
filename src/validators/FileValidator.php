<?php

namespace nms\filepond\validators;

use nms\filepond\traits\SessionValidator;

/**
 * Client validation rules are integrated in FilePond instance.
 * Server validation rules are integrated in `nms\filepond\models\File` model.
 * Validating `minFiles` and `maxFiles` options only. It isn't needed to
 * validate another rules here. This class is used only as adapter for components,
 * which are specified above.
 * @author Michael Naumov <vommuan@gmail.com>
 */
class FileValidator extends \yii\validators\FileValidator
{
    use SessionValidator;

    /**
     * {@inheritdoc}
     */
    public function clientValidateAttribute($model, $attribute, $view)
    {
        return null;
    }
}
