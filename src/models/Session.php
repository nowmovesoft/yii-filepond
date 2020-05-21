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
     * @var string Session identifier
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
        $this->id = Yii::$app->request->headers->get('X-Session-Id')
            ?? Yii::$app->request->post('session-id')
            ?? Yii::$app->security->generateRandomString(self::SESSION_ID_LENGTH);
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

        if (!isset($session['count'])) {
            $session['count'] = 0;
        }

        $session['count'] += 1;
        Yii::$app->session[$this->prefix] = $session;
    }

    /**
     * Decreases number of uploaded files
     */
    public function dec()
    {
        $session = Yii::$app->session[$this->prefix];

        if (empty($session['count'])) {
            throw new ErrorException("Impossible to decrease uploaded files number.", 3001);
        }

        $session['count'] -= 1;
        Yii::$app->session[$this->prefix] = $session;
    }

    /**
     * Gets number of uploaded files
     * @return integer
     */
    public function count()
    {
        $session = Yii::$app->session[$this->prefix];

        if (!isset($session['count'])) {
            return 0;
        }

        return $session['count'];
    }

    /**
     * Gets file information
     * @param string $id file identifier
     * @return array
     */
    public function getFile($id)
    {
        $session = Yii::$app->session[$this->prefix];

        return $session['files'][$id] ?? null;
    }

    /**
     * Adds file information in session.
     * @param string $id file identifier
     * @param \yii\web\UploadedFile $file file object
     */
    public function addFile($id, $file)
    {
        $session = Yii::$app->session[$this->prefix];

        $fields = [
            'baseName',
            'extension',
            'name',
            'size',
            'type',
        ];

        $fileInfo = [];

        foreach ($fields as $field) {
            $fileInfo[$field] = $file->$field;
        }

        $session['files'][$id] = $fileInfo;
        Yii::$app->session[$this->prefix] = $session;
    }

    /**
     * Removes file information from session.
     * @param string $id file identifier
     */
    public function removeFile($id)
    {
        $session = Yii::$app->session[$this->prefix];

        if (isset($session['files'][$id])) {
            unset($session['files'][$id]);
        }

        Yii::$app->session[$this->prefix] = $session;
    }
}
