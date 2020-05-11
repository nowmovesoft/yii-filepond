<?php

namespace nms\filepond;

use Yii;

/**
 * FilePond module definition class.
 * @author Michael Naumov <vommuan@gmail.com>
 */
class Module extends \yii\base\Module
{
    /**
     * @inheritdoc
     */
    public $controllerNamespace = 'nms\filepond\controllers';

    /**
     * @inheritdoc
     */
    public $defaultRoute = 'filepond';

    /**
     * @inheritdoc
     */
    public function init()
    {
        Yii::setAlias('@' . $this->uniqueId, __DIR__);
        $this->registerTranslations();
        parent::init();
    }

    /**
     * Registers translation messages.
     */
    public function registerTranslations()
    {
        Yii::$app->i18n->translations['modules/' . $this->uniqueId . '/*'] = [
            'class' => 'yii\i18n\PhpMessageSource',
            'sourceLanguage' => 'en-US',
            'basePath' => '@' . $this->uniqueId . '/messages',
            'fileMap' => [
                'modules/' . $this->uniqueId . '/main' => 'main.php',
            ],
        ];
    }

    /**
     * Gets translated message.
     * @see Yii::t for more information.
     * @param string $category
     * @param string $message
     * @param array $params
     * @param string|null $language
     * @return string
     */
    public static function t($category, $message, $params = [], $language = null)
    {
        if (!isset(Yii::$app->i18n->translations['modules/' . self::getInstance()->uniqueId . '/*'])) {
            return $message;
        }

        return Yii::t('modules/' . self::getInstance()->uniqueId . '/' . $category, $message, $params, $language);
    }
}
