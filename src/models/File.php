<?php

namespace nms\filepond\models;

use Yii;
use yii\base\DynamicModel;
use yii\base\ErrorException;
use yii\base\Model;
use yii\helpers\FileHelper;
use yii\validators\Validator;

/**
 * FilePond form for file upload.
 *
 * @author Michael Naumov <vommuan@gmail.com>
 */
class File extends Model
{
    /**
     * string Temporary storage for uploaded files
     */
    const TEMPORARY_STORAGE = '@runtime/filepond/uploads';

    /**
     * @var string File identifier
     */
    public $id;

    /**
     * @var UploadedFile
     */
    public $file;

    /**
     * @var Session Session object
     */
    private $session;

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        $this->initSession();
    }

    /**
     * Initialize session object
     */
    private function initSession()
    {
        $this->session = new Session();
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'file'], 'safe'],
        ];
    }

    /**
     * Validates model data dynamically.
     * @return boolean
     */
    private function dynamicValidate()
    {
        $fileParams = $this->session->loadParams();

        if (null === $fileParams) {
            return true;
        } elseif (false === $fileParams) {
            return false;
        }

        if ($this->session->filesNumber >= $fileParams['maxFiles']) {
            return false;
        }

        // Files are uploaded only this way: 1 file by 1 request.
        $fileParams['maxFiles'] = 1;

        $model = new DynamicModel([
            'id' => $this->id,
            'file' => $this->file,
        ]);

        $model->addRule(['id'], 'string')
            ->addRule(['file'], 'file', $fileParams)
            ->validate();

        return !$model->hasErrors();
    }

    /**
     * Save file to the `@runtime` directory.
     * @return boolean
     * @throws ErrorException If validation failed.
     * @throws Exception If impossible to create upload directory or move file to it.
     */
    public function process()
    {
        if (!$this->dynamicValidate()) {
            throw new ErrorException("File validation failed.", 2001);
        }

        FileHelper::createDirectory(Yii::getAlias(self::TEMPORARY_STORAGE));
        $this->id = basename($this->file->tempName) . '.' . $this->file->extension;
        $status = $this->file->saveAs(self::TEMPORARY_STORAGE . '/' . $this->id);

        if ($status) {
            $this->session->inc();
        }

        return $status;
    }

    /**
     * Deletes uploaded file from temporary storage.
     * @return boolean
     */
    public function revert()
    {
        if (empty($this->id)) {
            throw new ErrorException("FilePond can't revert file by empty identifier.", 2002);
        }

        $status = FileHelper::unlink(Yii::getAlias(self::TEMPORARY_STORAGE) . '/' . $this->id);

        if ($status) {
            $this->session->dec();
        }

        return $status;
    }
}
