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
            [['id'], 'string'],
            [['file'], 'safe'],
        ];
    }

    /**
     * Validates uploaded file dynamically.
     * @return boolean
     */
    private function validateFile()
    {
        $params = $this->session->loadParams();
        $fileParams = null;

        foreach (['image', 'file'] as $validator) {
            if (isset($params[$validator])) {
                $fileParams = $params[$validator];
                $validatorName = $validator;
                break;
            }
        }

        if (is_null($fileParams)) {
            return true;
        } elseif (false === $fileParams) {
            return false;
        }

        if ($this->session->count() >= $fileParams['maxFiles']) {
            return false;
        }

        // Files are uploaded only this way: 1 file by 1 request.
        $fileParams['maxFiles'] = 1;
        $model = new DynamicModel(['file' => $this->file]);
        $model->addRule(['file'], $validatorName, $fileParams)->validate();

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
        $this->initSession();

        if (!$this->validateFile()) {
            throw new ErrorException("File validation failed.", 2001); // TODO: display specific error
        }

        FileHelper::createDirectory(Yii::getAlias(self::TEMPORARY_STORAGE));
        $this->id = basename($this->file->tempName) . '.' . $this->file->extension;
        $status = $this->file->saveAs(self::TEMPORARY_STORAGE . '/' . $this->id);

        if ($status) {
            $this->session->addFile($this->id, $this->file);
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
        $this->initSession();

        if (empty($this->id)) {
            throw new ErrorException("FilePond can't revert file by empty identifier.", 2002);
        }

        $status = FileHelper::unlink($this->path);

        if ($status) {
            $this->session->removeFile($this->id);
            $this->session->dec();
        }

        return $status;
    }

    /**
     * Removes file from temporary storage. Use this method in custom code.
     */
    public function remove()
    {
        if (empty($this->id)) {
            throw new ErrorException("FilePond can't remove file by empty identifier.", 2003);
        }

        return FileHelper::unlink($this->path);
    }

    /**
     * Gets temporary path of uploaded file
     * @return string
     */
    public function getPath()
    {
        return Yii::getAlias(self::TEMPORARY_STORAGE) . '/' . $this->id;
    }

    /**
     * Gets file information.
     * @return array
     */
    public function getInfo()
    {
        $this->initSession();

        return $this->session->getFile($this->id);
    }

    /**
     * Saves file by specific name.
     * @param string $file the file path or a path alias used to save the uploaded file.
     * @param boolean $deleteTempFile whether to delete the temporary file after saving.
     * @return boolean
     */
    public function saveAs($file, $deleteTempFile = true)
    {
        $targetFile = Yii::getAlias($file);
        FileHelper::createDirectory(dirname($targetFile));

        return $deleteTempFile ? rename($this->path, $targetFile) : copy($this->path, $targetFile);
    }

    /**
     * Creates file model by uploaded file identifier
     * @param string $id Identifier
     */
    public static function getById($id)
    {
        return new self(['id' => $id]);
    }
}
