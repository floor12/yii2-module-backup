<?php
/**
 * Created by PhpStorm.
 * User: floor12
 * Date: 31.12.2017
 * Time: 14:45
 */

namespace floor12\backup;

use Yii;
use yii\base\ErrorException;
use yii\db\Connection;

class Module extends \yii\base\Module
{

    /** @var string */
    public $administratorRoleName = 'admin';

    /** @var string */
    public $backupFolder = '@app/backups';
    /**
     * @var string
     */
    public $chmod;
    /**
     * @var array
     */
    public $authTokens = [];
    /**
     * @inheritdoc
     */
    public $controllerNamespace = 'floor12\backup\controllers';

    public $backupRootPath;

    public $connection;

    public $ionice;

    public $configs = [];

    public $adminLayout = '@app/views/layouts/main';

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->backupRootPath = Yii::getAlias($this->backupFolder);

        try {
            if (!file_exists($this->backupRootPath))
                mkdir($this->backupRootPath);
        } catch (ErrorException $e) {
            throw new ErrorException("Backup folder not exists. Its impossible to create it because of permission error.");
        }

        if (!is_writable($this->backupRootPath))
            throw new ErrorException("Backup folder is not writeble.");

        $this->checkDb();
        $this->registerTranslations();
    }

    /**
     * @throws \yii\base\NotSupportedException
     * @throws \yii\db\Exception
     */
    public function checkDb()
    {
        $dbFileName = $this->backupRootPath . '/sqlite.db';
        $this->connection = new Connection(['dsn' => 'sqlite:' . $dbFileName]);
        $this->connection->getSchema();
        $this->connection->createCommand('
            CREATE TABLE IF NOT EXISTS backup (
              id INTEGER PRIMARY KEY,
              date DATETIME NOT NULL,
              status INTEGER NOT NULL DEFAULT 0,
              type INTEGER NOT NULL,
              config_id STRING(255) NOT NULL,
              config_name STRING(255) NULL,
              filename STRING(255) NULL,
              size INTEGER NOT NULL DEFAULT 0              
            );            
        ')->execute();
    }

    /**
     * Registing some lang files
     * @return void
     */
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