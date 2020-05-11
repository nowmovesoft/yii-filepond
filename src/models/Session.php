<?php

namespace nms\filepond\models;

use Yii;
use yii\base\ErrorException;
use yii\base\Model;
use yii\helpers\Json;

/**
 * Save and load upload state between HTTP requests.
 * @author Michael Naumov <vommuan@gmail.com>
 */
class Session extends Model
{
    /**
     * Length of session identifier for current upload
     */
    const SESSION_ID_LENGTH = 8;

    /**
     * @var string Session file identifier
     */
    public $id;

    /**
     * @var nms\filepond\validators\Validator
     */
    public $validator;

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        $this->initId();
    }

    /**
     * Initializes session identifier
     */
    private function initId()
    {
        if (isset($this->id)) {
            return;
        }

        if (null !== Yii::$app->request->csrfTokenFromHeader) {
            $this->id = substr(md5(Yii::$app->request->csrfTokenFromHeader), 0, self::SESSION_ID_LENGTH);
        } else {
            $this->id = substr(md5(Yii::$app->request->csrfToken), 0, self::SESSION_ID_LENGTH);
        }
    }

    /**
     * Saves validator params for current upload.
     * @return boolean
     * @throws Exception If impossible to create sessions directory.
     */
    public function saveParams()
    {
        if (is_null($this->validator)) {
            return false;
        }

        Yii::$app->session["filepond.{$this->id}.validator"] = Json::encode($this->validator);

        return true;
    }

    /**
     * Loads validator params for current upload
     * @return array|null|false Parent validator configuration
     */
    public function loadParams()
    {
        if (is_null(Yii::$app->session["filepond.{$this->id}.validator"])) {
            return null;
        }

        return Json::decode(Yii::$app->session["filepond.{$this->id}.validator"]);
    }

    /**
     * Increases number of uploaded files
     */
    public function inc()
    {
        if (!isset(Yii::$app->session["filepond.{$this->id}.filesNumber"])) {
            Yii::$app->session["filepond.{$this->id}.filesNumber"] = 0;
        }

        Yii::$app->session["filepond.{$this->id}.filesNumber"] += 1;
    }

    /**
     * Decreases number of uploaded files
     */
    public function dec()
    {
        if (empty(Yii::$app->session["filepond.{$this->id}.filesNumber"])) {
            throw new ErrorException("Impossible to decrease uploaded files number.", 3001);
        }

        Yii::$app->session["filepond.{$this->id}.filesNumber"] -= 1;
    }

    /**
     * Gets number of uploaded files
     * @return integer
     */
    public function getFilesNumber()
    {
        if (is_null(Yii::$app->session["filepond.{$this->id}.filesNumber"])) {
            return 0;
        }

        return Yii::$app->session["filepond.{$this->id}.filesNumber"];
    }
}
