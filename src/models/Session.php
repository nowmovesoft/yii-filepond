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
     * @var yii\validators\Validator[]
     */
    public $validators;

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

        $token = Yii::$app->request->csrfTokenFromHeader ?? Yii::$app->request->csrfToken;
        $this->id = substr(md5($token), 0, self::SESSION_ID_LENGTH);
    }

    /**
     * Gets prefix for session key
     * @return string
     */
    public function getPrefix()
    {
        return "filepond.{$this->id}";
    }

    /**
     * Saves validators params for current upload.
     * @return boolean
     * @throws Exception If impossible to create sessions directory.
     */
    public function saveParams()
    {
        if (is_null($this->validators)) {
            return false;
        }

        $validatorsNames = [];

        foreach ($this->validators as $name => $validator) {
            if (is_null($validator)) {
                continue;
            }

            $validatorsNames[] = $name;
            Yii::$app->session["{$this->prefix}.validators.{$name}"] = Json::encode($validator);
        }

        Yii::$app->session["{$this->prefix}.validators.names"] = $validatorsNames;

        return true;
    }

    /**
     * Loads validator params for current upload
     * @return array Parent validator configuration
     */
    public function loadParams()
    {
        if (empty(Yii::$app->session["{$this->prefix}.validators.names"])) {
            return [];
        }

        $params = [];

        foreach (Yii::$app->session["{$this->prefix}.validators.names"] as $name) {
            $params[$name] = Json::decode(Yii::$app->session["{$this->prefix}.validators.{$name}"]);
        }

        return $params;
    }

    /**
     * Increases number of uploaded files
     */
    public function inc()
    {
        if (!isset(Yii::$app->session["{$this->prefix}.filesNumber"])) {
            Yii::$app->session["{$this->prefix}.filesNumber"] = 0;
        }

        Yii::$app->session["{$this->prefix}.filesNumber"] += 1;
    }

    /**
     * Decreases number of uploaded files
     */
    public function dec()
    {
        if (empty(Yii::$app->session["{$this->prefix}.filesNumber"])) {
            throw new ErrorException("Impossible to decrease uploaded files number.", 3001);
        }

        Yii::$app->session["{$this->prefix}.filesNumber"] -= 1;
    }

    /**
     * Gets number of uploaded files
     * @return integer
     */
    public function getFilesNumber()
    {
        if (is_null(Yii::$app->session["{$this->prefix}.filesNumber"])) {
            return 0;
        }

        return Yii::$app->session["{$this->prefix}.filesNumber"];
    }
}
