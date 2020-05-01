<?php
/**
 * Created by PhpStorm.
 * User: floor12
 * Date: 07.01.2018
 * Time: 12:40
 */

namespace floor12\backup\tests;

use floor12\backup\models\BackupType;
use floor12\backup\models\IOPriority;
use floor12\backup\Module;
use Yii;
use yii\base\InvalidConfigException;
use yii\console\Application;

abstract class TestCase extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Module
     */
    public $module;

    /**
     * @inheritdoc
     * @throws InvalidConfigException
     */
    protected function setUp()
    {
        $this->mockApplication();
        $this->setApp();
        parent::setUp();
    }

    /**
     * @throws InvalidConfigException
     */
    protected function mockApplication()
    {
        new Application([
            'id' => 'testApp',
            'basePath' => __DIR__,
            'vendorPath' => dirname(__DIR__) . '/vendor',
            'runtimePath' => __DIR__ . '/runtime',
        ]);
    }

    /**
     * Adds backup module to tmp app
     */
    protected function setApp()
    {
        $backupModule = [
            'class' => 'floor12\backup\Module',
            'backupFolder' => '@vendor/../tests/_output',
            'configs' => [
                [
                    'id' => 'mysql_db',
                    'type' => BackupType::DB,
                    'title' => 'Mysql Database',
                    'connection' => 'mysql',
                    'limit' => 0
                ],
                [
                    'id' => 'postgres_db',
                    'type' => BackupType::DB,
                    'title' => 'PostgresQL database',
                    'connection' => 'postgres',
                    'limit' => 0
                ],
                [
                    'id' => 'backup_test_folder',
                    'type' => BackupType::FILES,
                    'title' => 'TMP folder',
                    'path' => '@app/data/folder_for_backup',
                    'limit' => 0
                ]
            ]
        ];
        Yii::$app->setModule('backup', $backupModule);

        $mysql = [
            'class' => 'yii\db\Connection',
            'dsn' => 'mysql:host=mysql;dbname=tester',
            'username' => 'tester',
            'password' => 'tester',
            'charset' => 'utf8',
        ];
        Yii::$app->set('mysql', $mysql);

        $postgres = [
            'class' => 'yii\db\Connection',
            'dsn' => 'pgsql:host=postgres;port=5432;dbname=tester',
            'username' => 'tester',
            'password' => 'tester',
            'charset' => 'utf8',
        ];
        Yii::$app->set('postgres', $postgres);

        $this->module = Yii::$app->getModule('backup');
    }

    /**
     * @inheritdoc
     */
    protected function tearDown()
    {
        $this->destroyApplication();
        parent::tearDown();
    }

    /**
     * Destroy test application
     */
    protected function destroyApplication()
    {
        Yii::$app = null;
    }
}
