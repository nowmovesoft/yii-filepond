<?php

namespace nms\filepond\validators;

use yii\validators\ValidationAsset;

/**
 * Validates FilePond instance.
 * @author Michael Naumov <vommuan@gmail.com>
 */
class FileValidator extends \yii\validators\FileValidator
{
    /**
     * {@inheritdoc}
     */
    public function clientValidateAttribute($model, $attribute, $view)
    {
        ValidationAsset::register($view);
        $options = $this->getClientOptions($model, $attribute);

        return 'yii.validation.string(value, messages, ' . json_encode($options, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . ');';
    }

    /**
     * {@inheritdoc}
     */
    public function getClientOptions($model, $attribute)
    {
        $label = $model->getAttributeLabel($attribute);

        $options = [
            'message' => $this->formatMessage($this->message, [
                'attribute' => $label,
            ]),
        ];

        $options['skipOnEmpty'] = $this->skipOnEmpty;

        if (!$this->skipOnEmpty) {
            $options['uploadRequired'] = $this->formatMessage($this->uploadRequired, [
                'attribute' => $label,
            ]);
        }

        return $options;
    }
}
