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

use floor12\backup\logic\DatabaseBackuper;
use floor12\backup\tests\TestCase;
use Yii;
use yii\base\Exception;
use yii\db\Connection;

class DatabaseBackuperTest extends TestCase
{
//    protected function assertTableExists(string $tableName, Connection $connection)
//    {
//
//
//    }

    protected function deleteTableSuccess(string $tableName, $connection)
    {
        try {
            $connection->createCommand()->dropTable($tableName)->execute();
        } catch (\yii\db\Exception $e) {
            return false;
        }
        return true;
    }

    public function testBackupFileExists()
    {
        $this->expectException(Exception::class);
        $backupFilePath = Yii::getAlias('@app/tmp/sqlite.db'); // Just existing file
        $connection = Yii::$app->mysql;
        new DatabaseBackuper($backupFilePath, $connection);
        $backuper = new DatabaseBackuper($backupFilePath, $connection);
        $backuper->backup();
    }

//    public function testCreateMysqlBackup()
//    {
//        $backupFilePath = Yii::getAlias('@app/tmp/mysql.tar');
//        $connection = Yii::$app->mysql;
//        $backuper = new DatabaseBackuper($backupFilePath, $connection);
//        $backuper->backup();
//        $this->assertFileExists($backupFilePath);
//    }

    public function testCreatePostgresBackup()
    {
        $backupFilePath = Yii::getAlias('@app/tmp/postgres');
        $connection = Yii::$app->postgres;
        $backuper = new DatabaseBackuper($backupFilePath, $connection);
        $backuper->backup();
        $this->assertFileExists($backupFilePath);
        @unlink($backupFilePath);
    }

    public function testRestorePostgresBackup()
    {
        $backupFilePath = Yii::getAlias('@app/data/postgres');
        /** @var $connection Connection */
        $connection = Yii::$app->postgres;
        $this->assertFalse($this->deleteTableSuccess('test_table', $connection));
        $backuper = new DatabaseBackuper($backupFilePath, $connection);
        $backuper->restore();
        $this->assertTrue($this->deleteTableSuccess('test_table', $connection));
    }


}
