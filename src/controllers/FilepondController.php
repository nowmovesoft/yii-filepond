<?php

namespace nms\filepond\controllers;

use nms\filepond\models\File;
use nms\filepond\Module;
use Yii;
use yii\base\ErrorException;
use yii\base\Exception;
use yii\base\NotSupportedException;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\UploadedFile;
use yii\web\Response;

class FilepondController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'process' => ['POST'],
                    'revert' => ['DELETE'],
                    'load' => ['GET'],
                    'restore' => ['GET'],
                    'fetch' => ['GET'],
                    'patch' => ['PATCH'],
                ],
            ],
        ];
    }

    /**
     * Server answers in JSON format for all requests.
     * {@inheritdoc}
     */
    public function beforeAction($action)
    {
        if (!parent::beforeAction($action)) {
            return false;
        }

        Yii::$app->response->format = Response::FORMAT_JSON;

        return true;
    }

    /**
     * Uploads file to temporary storage.
     * @return json
     */
    public function actionProcess()
    {
        $model = new File();
        $model->file = UploadedFile::getInstance($model, 'file');

        try {
            $model->process();
        } catch (ErrorException | Exception $e) {
            Yii::$app->response->statusCode = 500;
            Yii::error("Code: {$e->getCode()}.\nMessage: {$e->getMessage()}\nTrace: {$e->getTraceAsString()}", 'nms\filepond\controllers\FilepondController::actionProcess');
            return [
                'message' => Module::t('main', 'Error during upload.') . ' '
                    . Module::t('main', 'Please, contact your administrator. Error code: {errorCode}', ['errorCode' => $e->getCode()]),
            ];
        }

        return ['key' => $model->id];
    }

    /**
     * Deletes uploaded file from temporary storage.
     * @return null|json
     */
    public function actionRevert()
    {
        $model = new File(['id' => file_get_contents('php://input')]);

        try {
            $model->revert();
        } catch (ErrorException | Exception $e) {
            Yii::$app->response->statusCode = 500;
            Yii::error("Code: {$e->getCode()}.\nMessage: {$e->getMessage()}.\nTrace: {$e->getTraceAsString()}.", 'nms\filepond\controllers\FilepondController::actionRevert');
            return [
                'message' => Module::t('main', 'Error during revert.') . ' '
                    . Module::t('main', 'Please, contact your administrator. Error code: {errorCode}', ['errorCode' => $e->getCode()]),
            ];
        }
    }

    public function actionLoad()
    {
        throw new NotSupportedException("Load action isn't supported.", 4001);
    }

    public function actionRestore()
    {
        throw new NotSupportedException("Restore action isn't supported.", 4002);
    }

    public function actionFetch()
    {
        throw new NotSupportedException("Fetch action isn't supported.", 4003);
    }

    public function actionPatch()
    {
        throw new NotSupportedException("Patch action isn't supported.", 4004);
    }
}
