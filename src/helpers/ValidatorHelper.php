<?php

namespace nms\filepond\helpers;

/**
 * Collection of methods to operate with model validators.
 * @author Michael Naumov <vommuan@gmail.com>
 */
class ValidatorHelper
{
    /**
     * Gets specified active validator for model attribute.
     * @param Model $model
     * @param string $attribute
     * @param string $validatorName Class name of validator
     * @return yii\validators\Validator|null
     */
    public static function get($model, $attribute, $validatorName)
    {
        if (!isset($model, $attribute) || empty($validatorName)) {
            return null;
        }

        foreach ($model->getActiveValidators($attribute) as $validator) {
            if ($validator instanceof $validatorName) {
                return $validator;
            }
        }

        return null;
    }
}
