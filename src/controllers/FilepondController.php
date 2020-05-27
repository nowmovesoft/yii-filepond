<?php

namespace nms\filepond\controllers;

use nms\filepond\models\File;
use Yii;
use yii\base\ErrorException;
use yii\base\Exception;
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
     * Uploads file to temporary storage.
     * @return json
     */
    public function actionProcess()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $model = new File();
        $model->file = UploadedFile::getInstance($model, 'file');

        try {
            $model->process();
        } catch (ErrorException | Exception $e) {
            Yii::$app->response->statusCode = 500;
            Yii::error("Code: {$e->getCode()}.\nMessage: {$e->getMessage()}\nTrace: {$e->getTraceAsString()}", 'nms\filepond\controllers\FilepondController::actionProcess');
            return ['message' => Yii::t('app', 'Processing error. Please, contact your administrator. Error code: ' . $e->getCode())];
        }

        return ['key' => $model->id];
    }

    /**
     * Deletes uploaded file from temporary storage.
     * @return null|json
     */
    public function actionRevert()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $model = new File(['id' => file_get_contents('php://input')]);

        try {
            $model->revert();
        } catch (ErrorException | Exception $e) {
            Yii::$app->response->statusCode = 500;
            Yii::error("Code: {$e->getCode()}.\nMessage: {$e->getMessage()}.\nTrace: {$e->getTraceAsString()}.", 'nms\filepond\controllers\FilepondController::actionRevert');
            return ['message' => Yii::t('app', 'Reverting error. Please, contact your administrator. Error code: ' . $e->getCode())];
        }
    }

    public function actionLoad()
    {
        return $this->render('load');
    }

    public function actionRestore()
    {
        return $this->render('restore');
    }

    public function actionFetch()
    {
        return $this->render('fetch');
    }

    public function actionPatch()
    {
        return $this->render('patch');
    }
}
