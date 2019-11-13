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

use floor12\backup\logic\FolderBackupMaker;
use floor12\backup\tests\TestCase;
use Yii;
use yii\base\Exception;

class FolderBackupMakerTest extends TestCase
{
    public function testCreateBackupFileExists()
    {
        $this->expectException(Exception::class);
        $backupFilePath = Yii::getAlias('@app/tmp/sqlite.db');
        $creator = new FolderBackupMaker($backupFilePath, 'tmp');
    }

    public function testCreateBackupTargetNotExists()
    {
        $this->expectException(Exception::class);
        $backupFilePath = Yii::getAlias('@app/tmp/backup.tgz');
        $creator = new FolderBackupMaker($backupFilePath, 'not-exists');
    }


    public function testCreateBackupSuccess()
    {
        $backupFilePath = Yii::getAlias('@app/tmp/backup.tgz');
        $targetFolder = Yii::getAlias('@app/unit');
        $creator = new FolderBackupMaker($backupFilePath, $targetFolder);
        $this->assertTrue($creator->execute());
        $this->fileExists($backupFilePath);
        unlink($backupFilePath);
    }

    public function testCreateBackupSuccessWithIoNice()
    {
        $backupFilePath = Yii::getAlias('@app/tmp/backup.tgz');
        $testFilePath = Yii::getAlias('@app/tmp/test');
        $targetFolder = Yii::getAlias('@app/unit');

        $this->module->ionice = "touch {$testFilePath} && ";
        $creator = new FolderBackupMaker($backupFilePath, $targetFolder);
        $this->assertTrue($creator->execute());
        $this->fileExists($backupFilePath);
        $this->fileExists($testFilePath);
        unlink($backupFilePath);
        unlink($testFilePath);
    }

    public function testCreateBackupSuccessWithChmod()
    {
        $backupFilePath = Yii::getAlias('@app/tmp/backup.tgz');
        @unlink($backupFilePath);
        $targetFolder = Yii::getAlias('@app/unit');
        $this->module->chmod = 0700;
        $creator = new FolderBackupMaker($backupFilePath, $targetFolder);
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