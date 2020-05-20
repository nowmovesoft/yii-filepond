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

        $session = Yii::$app->session[$this->prefix];

        foreach ($this->validators as $name => $validator) {
            if (is_null($validator)) {
                continue;
            }

            $session['validators'][$name] = Json::encode($validator);
        }

        Yii::$app->session[$this->prefix] = $session;

        return true;
    }

    /**
     * Loads validator params for current upload
     * @return array Parent validator configuration
     */
    public function loadParams()
    {
        $session = Yii::$app->session[$this->prefix];

        if (empty($session['validators'])) {
            return [];
        }

        $params = [];

        foreach ($session['validators'] as $name => $validator) {
            $params[$name] = Json::decode($validator);
        }

        return $params;
    }

    /**
     * Increases number of uploaded files
     */
    public function inc()
    {
        $session = Yii::$app->session[$this->prefix];

        if (!isset($session['filesNumber'])) {
            $session['filesNumber'] = 0;
        }

        $session['filesNumber'] += 1;
        Yii::$app->session[$this->prefix] = $session;
    }

    /**
     * Decreases number of uploaded files
     */
    public function dec()
    {
        $session = Yii::$app->session[$this->prefix];

        if (empty($session['filesNumber'])) {
            throw new ErrorException("Impossible to decrease uploaded files number.", 3001);
        }

        $session['filesNumber'] -= 1;
        Yii::$app->session[$this->prefix] = $session;
    }

    /**
     * Gets number of uploaded files
     * @return integer
     */
    public function getFilesNumber()
    {
        $session = Yii::$app->session[$this->prefix];

        if (is_null($session['filesNumber'])) {
            return 0;
        }

        return $session['filesNumber'];
    }

    /**
     *
     */
    public function saveFileInfo($file)
    {

    }

    /**
     *
     */
    public function removeFileInfo($id)
    {

    }
}
