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
use yii\base\NotSupportedException;
use yii\db\Connection;
use yii\db\Exception;

class Module extends \yii\base\Module
{

    /**
     * @var string
     */
    public $administratorRoleName = 'admin';
    /**
     * @var string
     */
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
     * @var string[]
     */
    public $binaries = [
        'mysql' => '/usr/bin/mysql',
        'mysqldump' => '/usr/bin/mysqldump',
        'pg_dump' => '/usr/bin/pg_dump',
        'pg_restore' => '/usr/bin/pg_restore',
        'gzip' => '/bin/gzip',
        'zcat' => '/bin/zcat',
        'ionice' => '/usr/bin/ionice',
        'zip' => '/usr/bin/zip',
        'unzip' => '/usr/bin/unzip',
        'chmod' => '/bin/chmod',
    ];
    /**
     * @inheritdoc
     */
    public $controllerNamespace = 'floor12\backup\controllers';
    /**
     * @var string
     */
    public $backupRootPath;
    /**
     * @var string
     */
    public $connection;
    /**
     * @var string
     */
    public $ionice;
    /**
     * @var array
     */
    public $configs = [];
    /**
     * @var string
     */
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
            throw new ErrorException("Backup folder is not writable.");

        $this->checkSqliteDb();
        $this->registerTranslations();
    }

    /**
     * @throws NotSupportedException
     * @throws Exception
     */
    public function checkSqliteDb()
    {
        $dbFileName = $this->backupRootPath . '/sqlite.db';
        $this->connection = new Connection(['dsn' => 'sqlite:' . $dbFileName]);
        $this->connection->getSchema();
        $sql = file_get_contents(__DIR__ . '/migration/sqlite.backup.sql');
        $this->connection->createCommand($sql)->execute();
    }

    /**
     * Register some lang files
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

    /**
     * @param string $config_id
     * @return bool
     */
    public function checkConfig(string $config_id)
    {
        foreach (Yii::$app->getModule('backup')->configs as $config)
            if ($config['id'] == $config_id)
                return true;
        return false;
    }

}
