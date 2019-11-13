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

use floor12\backup\logic\DatabaseBackupRestorer;
use floor12\backup\tests\ConnectionMock;
use floor12\backup\tests\TestCase;
use Yii;
use yii\base\Exception;

class DatabaseBackupRestorerTest extends TestCase
{

    public function testFileNoExists()
    {
        $connection = new ConnectionMock();
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Backup file don`t exist.');
        $backupFilePath = Yii::getAlias('@app/data/no-exist.tgz');
        new DatabaseBackupRestorer($backupFilePath, $connection);
    }

    public function testRestoreSuccess()
    {
        $databaseName = 'testDataBaseName';
        $connection = new ConnectionMock(['databaseName' => $databaseName]);
        $commandsToCheck = [
            'SELECT DATABASE()',
            "DROP DATABASE `{$databaseName}`",
            "CREATE DATABASE `{$databaseName}`",
            "USE `{$databaseName}`",
            "/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;",
        ];
        $backupFilePath = Yii::getAlias('@app/data/test.zip');
        $restorer = new DatabaseBackupRestorer($backupFilePath, $connection);
        $restorer->execute();
        foreach ($commandsToCheck as $command) {
            $this->assertTrue(in_array($command, $connection->sql));
        }
    }


}