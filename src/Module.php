<?php
/**
 * Created by PhpStorm.
 * User: floor12
 * Date: 31.12.2017
 * Time: 14:45
 */

namespace floor12\backup;

use Yii;


class Module extends \yii\base\Module
{

    /** @var string FontAwesome helper class */
    public $fontAwesome = 'rmrevin\yii\fontawesome\FontAwesome';

    /** @var string */
    public $editRole = 'admin';

    /** @inheritdoc */
    public $controllerNamespace = 'floor12\backup\controllers';

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->fontAwesome = Yii::createObject($this->fontAwesome);
        $this->registerTranslations();
    }


    public function registerTranslations()
    {
        Yii::$app->i18n->translations['app.f12.backup'] = [
            'class' => 'yii\i18n\PhpMessageSource',
            'basePath' => '@vendor/floor12/yii2-module-backup/src/messages',
            'sourceLanguage' => 'en-US',
            'fileMap' => [
                'app.f12.backup' => 'backup.php',
            ],
        ];
    }

}