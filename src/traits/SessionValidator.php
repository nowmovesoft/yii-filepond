<?php

namespace nms\filepond\traits;

use nms\filepond\models\Session;
use yii\validators\Validator;

/**
 * Methods for validating files and images uploaded via session algorithm.
 * @author Michael Naumov <vommuan@gmail.com>
 */
trait SessionValidator
{
    /**
     * @see yii\validators\Validator::validateAttribute()
     */
    public function validateAttribute($model, $attribute)
    {
        $session = new Session(['id' => $model->$attribute]);
        $filesCount = $session->count();

        if ($this->maxFiles && $filesCount > $this->maxFiles) {
            $this->addError($model, $attribute, $this->tooMany, ['limit' => $this->maxFiles]);
        }

        if ($this->minFiles && $this->minFiles > $filesCount) {
            $this->addError($model, $attribute, $this->tooFew, ['limit' => $this->minFiles]);
        }
    }

    /**
     * @see yii\validators\Validator::isEmpty()
     */
    public function isEmpty($value, $trim = false)
    {
        return Validator::isEmpty($value);
    }
}
