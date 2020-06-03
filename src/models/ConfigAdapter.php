<?php

namespace nms\filepond\models;

use nms\filepond\helpers\ValidatorHelper;
use nms\filepond\Module;
use Yii;
use yii\base\Model;
use yii\helpers\FileHelper;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\helpers\Url;

/**
 * Adopts widget configuration to FilePond configuration.
 * @author Michael Naumov <vommuan@gmail.com>
 */
class ConfigAdapter extends Model
{
    /**
     * FilePond endpoints list
     */
    const FILEPOND_ENDPOINTS = [
        'process',
        'revert',
        'load',
        'restore',
        'fetch',
        'patch',
    ];

    /**
     * @var array FilePond configuration
     */
    public $filePond;

    /**
     * @var Session Saved state of upload field
     */
    private $session;

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        $this->addName();
    }

    /**
     * Adds `name` option for FilePond.
     */
    public function addName()
    {
        if (!isset($this->filePond['name'])) {
            $this->filePond['name'] = 'File[file]';
        }
    }

    /**
     * Adds validators options to FilePond instance.
     * @param Model $model
     * @param string $attribute
     */
    public function addValidators($model, $attribute)
    {
        if (!isset($model, $attribute)) {
            // TODO: initialize session for standalone form
            return;
        }

        $validators = [
            'required' => ValidatorHelper::get($model, $attribute, 'nms\filepond\validators\RequiredValidator'),
            'file' => ValidatorHelper::get($model, $attribute, 'nms\filepond\validators\FileValidator'),
            'image' => ValidatorHelper::get($model, $attribute, 'nms\filepond\validators\ImageValidator'),
        ];

        $this->session = new Session(['validators' => $validators]);
        $this->session->saveParams();

        $this->addRequiredValidator($validators['required']);
        $this->addFileValidator($validators['file']);
        $this->addImageValidator($validators['image']);
    }

    /**
     * Adds `RequiredValidator` rules for FilePond instance.
     * @param nms\filepond\validators\RequiredValidator $validator
     */
    private function addRequiredValidator($validator)
    {
        if (is_null($validator) || !$validator->enableClientValidation) {
            return;
        }

        if (!isset($this->filePond['required'])) {
            $this->filePond['required'] = true;
        }
    }

    /**
     * Adds `FileValidator` rules for FilePond instance.
     * @param nms\filepond\validators\FileValidator $validator
     */
    private function addFileValidator($validator)
    {
        if (is_null($validator) || !$validator->enableClientValidation) {
            return;
        }

        if (!isset($this->filePond['acceptedFileTypes'])) {
            $mimeTypes = $validator->mimeTypes;

            if (!empty($validator->extensions)) {
                foreach ($validator->extensions as $extension) {
                    $mimeTypes[] = FileHelper::getMimeTypeByExtension("file.{$extension}");
                }
            }

            if (!empty($mimeTypes)) {
                $this->filePond['acceptedFileTypes'] = $mimeTypes;
            }
        }

        if (!isset($this->filePond['minFileSize']) && isset($validator->minSize)) {
            $this->filePond['minFileSize'] = $validator->minSize;
        }

        if (!isset($this->filePond['maxFileSize'])) {
            $this->filePond['maxFileSize'] = $validator->getSizeLimit();
        }

        if (!isset($this->filePond['minFiles']) && 0 < $validator->minFiles) {
            $this->filePond['minFiles'] = $validator->minFiles;
        }

        if (!isset($this->filePond['labelTooFew']) && isset($this->filePond['minFiles'])) {
            $this->filePond['labelTooFew'] = Yii::$app->getI18n()->format(
                $validator->tooFew,
                ['limit' => $this->filePond['minFiles']],
                Yii::$app->language
            );
        }

        if (!isset($this->filePond['allowMultiple'], $this->filePond['maxFiles']) && 1 !== $validator->maxFiles) {
            $this->filePond['allowMultiple'] = true;

            if (0 !== $validator->maxFiles) {
                $this->filePond['maxFiles'] = $validator->maxFiles;
            }
        }
    }

    /**
     * Adds `ImageValidator` rules for FilePond instance.
     * @param nms\filepond\validators\ImageValidator $validator
     */
    private function addImageValidator($validator)
    {
        if (is_null($validator) || !$validator->enableClientValidation) {
            return;
        }

        $this->addFileValidator($validator);

        if (!isset($this->filePond['imageValidateSizeMaxHeight']) && isset($validator->maxHeight)) {
            $this->filePond['imageValidateSizeMaxHeight'] = $validator->maxHeight;
        }

        if (!isset($this->filePond['imageValidateSizeMaxWidth']) && isset($validator->maxHeight)) {
            $this->filePond['imageValidateSizeMaxWidth'] = $validator->maxWidth;
        }

        if (!isset($this->filePond['imageValidateSizeMinHeight']) && isset($validator->minHeight)) {
            $this->filePond['imageValidateSizeMinHeight'] = $validator->minHeight;
        }

        if (!isset($this->filePond['imageValidateSizeMinWidth']) && isset($validator->minWidth)) {
            $this->filePond['imageValidateSizeMinWidth'] = $validator->minWidth;
        }
    }

    /**
     * Adds translation messages to FilePond configuration.
     */
    public function addMessages()
    {
        if (!isset($this->filePond['labelIdle'])) {
            $this->filePond['labelIdle'] = Module::t('main', 'Drag & Drop your files or ')
                . Html::tag('span', Module::t('main', 'Browse'), ['class' => 'filepond--label-action']);
        }

        if (!isset($this->filePond['labelInvalidField'])) {
            $this->filePond['labelInvalidField'] = Module::t('main', 'Field contains invalid files');
        }

        if (!isset($this->filePond['labelFileWaitingForSize'])) {
            $this->filePond['labelFileWaitingForSize'] = Module::t('main', 'Waiting for size');
        }

        if (!isset($this->filePond['labelFileSizeNotAvailable'])) {
            $this->filePond['labelFileSizeNotAvailable'] = Module::t('main', 'Size not available');
        }

        if (!isset($this->filePond['labelFileLoading'])) {
            $this->filePond['labelFileLoading'] = Module::t('main', 'Loading');
        }

        if (!isset($this->filePond['labelFileLoadError'])) {
            $this->filePond['labelFileLoadError'] = Module::t('main', 'Error during load');
        }

        if (!isset($this->filePond['labelFileProcessing'])) {
            $this->filePond['labelFileProcessing'] = Module::t('main', 'Uploading');
        }

        if (!isset($this->filePond['labelFileProcessingComplete'])) {
            $this->filePond['labelFileProcessingComplete'] = Module::t('main', 'Upload complete');
        }

        if (!isset($this->filePond['labelFileProcessingAborted'])) {
            $this->filePond['labelFileProcessingAborted'] = Module::t('main', 'Upload cancelled');
        }

        if (!isset($this->filePond['labelFileProcessingError'])) {
            $this->filePond['labelFileProcessingError'] = Module::t('main', 'Error during upload');
        }

        if (!isset($this->filePond['labelFileProcessingRevertError'])) {
            $this->filePond['labelFileProcessingRevertError'] = Module::t('main', 'Error during revert');
        }

        if (!isset($this->filePond['labelFileRemoveError'])) {
            $this->filePond['labelFileRemoveError'] = Module::t('main', 'Error during remove');
        }

        if (!isset($this->filePond['labelTapToCancel'])) {
            $this->filePond['labelTapToCancel'] = Module::t('main', 'tap to cancel');
        }

        if (!isset($this->filePond['labelTapToRetry'])) {
            $this->filePond['labelTapToRetry'] = Module::t('main', 'tap to retry');
        }

        if (!isset($this->filePond['labelTapToUndo'])) {
            $this->filePond['labelTapToUndo'] = Module::t('main', 'tap to undo');
        }

        if (!isset($this->filePond['labelButtonRemoveItem'])) {
            $this->filePond['labelButtonRemoveItem'] = Module::t('main', 'Remove');
        }

        if (!isset($this->filePond['labelButtonAbortItemLoad'])) {
            $this->filePond['labelButtonAbortItemLoad'] = Module::t('main', 'Abort');
        }

        if (!isset($this->filePond['labelButtonRetryItemLoad'])) {
            $this->filePond['labelButtonRetryItemLoad'] = Module::t('main', 'Retry');
        }

        if (!isset($this->filePond['labelButtonAbortItemProcessing'])) {
            $this->filePond['labelButtonAbortItemProcessing'] = Module::t('main', 'Cancel');
        }

        if (!isset($this->filePond['labelButtonUndoItemProcessing'])) {
            $this->filePond['labelButtonUndoItemProcessing'] = Module::t('main', 'Undo');
        }

        if (!isset($this->filePond['labelButtonRetryItemProcessing'])) {
            $this->filePond['labelButtonRetryItemProcessing'] = Module::t('main', 'Retry');
        }

        if (!isset($this->filePond['labelButtonProcessItem'])) {
            $this->filePond['labelButtonProcessItem'] = Module::t('main', 'Upload');
        }
    }

    /**
     * Gets endpoints URLs.
     * @return array
     */
    public function getEndpoints()
    {
        $endpoints = [];

        foreach (self::FILEPOND_ENDPOINTS as $endpoint) {
            $endpoints[$endpoint] = urldecode(Url::to(['/' . Module::getInstance()->uniqueId . '/filepond/' . $endpoint]));
        }

        return $endpoints;
    }

    /**
     * Gets session identifier.
     * @return string
     */
    public function getSessionId()
    {
        return $this->session->id;
    }

    /**
     * Makes JSON configuration for FilePond connection
     * @return json
     */
    public function make()
    {
        return Json::encode($this->filePond);
    }
}
