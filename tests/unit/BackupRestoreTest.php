<?php
/**
 * Created by PhpStorm.
 * User: floor12
 * Date: 11.11.2019
 * Time: 07:45
 */

namespace floor12\backup\tests\unit;

/**
 * This is a tests for Backup class
 */

use ErrorException;
use floor12\backup\logic\BackupRestore;
use floor12\backup\models\Backup;
use floor12\backup\models\BackupStatus;
use floor12\backup\models\BackupType;
use floor12\backup\tests\TestCase;
use Yii;
use yii\base\InvalidConfigException;

class BackupRestoreTest extends TestCase
{

    public function testEmptyConfigs()
    {
        $backup = new Backup([
            'status' => BackupStatus::DONE,
            'filename' => 'test.gz',
            'type' => BackupType::FILES,
            'config_id' => 'tmp_folder'
        ]);
        $this->expectException(InvalidConfigException::class);
        $this->module->configs = [];
        $this->expectExceptionMessage('Backup module need to be configured with `config array`');
        $restorer = new BackupRestore($backup);
    }

    public function testWrongConfigName()
    {
        $config_id = 'wrong_config_id';
        $backup = new Backup([
            'status' => BackupStatus::DONE,
            'filename' => 'test.gz',
            'type' => BackupType::FILES,
            'config_id' => $config_id
        ]);
        $this->expectException(ErrorException::class);
        $this->expectExceptionMessage("Config `{$config_id}` not found.");
        $restorer = new BackupRestore($backup);
    }

    public function testDatabaseSuccess()
    {
        $this->module->backupFolder = '@vendor/../tests/data';

        $backup = new Backup([
            'status' => BackupStatus::DONE,
            'filename' => 'test.zip',
            'type' => BackupType::DB,
            'config_id' => 'main_db'
        ]);
        $restorer = new BackupRestore($backup);
        $restorer->run();

        $databaseName = Yii::$app->db->databaseName;

        $commandsToCheck = [
            'SELECT DATABASE()',
            "DROP DATABASE `{$databaseName}`",
            "CREATE DATABASE `{$databaseName}`",
            "USE `{$databaseName}`",
            "/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;",
        ];

        foreach ($commandsToCheck as $command) {
            $this->assertTrue(in_array($command, Yii::$app->db->sql));
        }
    }

    public function testFolderSuccess()
    {
        $this->module->backupFolder = '@vendor/../tests/data';
        $resultFilePath = Yii::getAlias('@app/tmp/test.txt');
        @unlink($resultFilePath);
        $this->assertFileNotExists($resultFilePath);
        $backup = new Backup([
            'status' => BackupStatus::DONE,
            'filename' => 'test.tgz',
            'type' => BackupType::FILES,
            'config_id' => 'tmp_folder'
        ]);
        $restorer = new BackupRestore($backup);
        $restorer->run();
        $this->assertFileExists($resultFilePath);
        @unlink($resultFilePath);
    }


}