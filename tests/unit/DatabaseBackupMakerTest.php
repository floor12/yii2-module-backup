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

use floor12\backup\logic\DatabaseBackupMaker;
use floor12\backup\tests\MysqldumpMock;
use floor12\backup\tests\TestCase;
use Yii;
use yii\base\Exception;
use yii\db\Connection;

class DatabaseBackupMakerTest extends TestCase
{
    public function testCreateBackupFileExists()
    {
        $this->expectException(Exception::class);
        $connection = new Connection(['dsn' => 'sqlite:tests/tmp/app.db']);
        $backupFilePath = Yii::getAlias('@app/tmp/sqlite.db');
        $dumper = MysqldumpMock::class;
        $creator = new DatabaseBackupMaker($backupFilePath, $connection, $dumper);
    }

    public function testCreateBackupSuccess()
    {
        $connection = new Connection(['dsn' => 'sqlite:tests/tmp/app.db']);
        $backupFilePath = Yii::getAlias('@app/tmp/backup.tgz');
        $dumper = MysqldumpMock::class;
        $creator = new DatabaseBackupMaker($backupFilePath, $connection, $dumper);
        $this->assertTrue($creator->execute());
        $this->fileExists($backupFilePath);
        @unlink($backupFilePath);
    }

    public function testCreateBackupSWithChmod()
    {
        $connection = new Connection(['dsn' => 'sqlite:tests/tmp/app.db']);
        $backupFilePath = Yii::getAlias('@app/tmp/backup.tgz');
        $this->module->chmod = 0700;
        $dumper = MysqldumpMock::class;
        $creator = new DatabaseBackupMaker($backupFilePath, $connection, $dumper);
        $this->assertTrue($creator->execute());
        $this->fileExists($backupFilePath);
        $this->assertEquals('0700', $this->readPerms($backupFilePath));
        @unlink($backupFilePath);
    }

    protected function readPerms(string $file)
    {
        return substr(sprintf('%o', fileperms($file)), -4);
    }


}