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

use floor12\backup\logic\FolderBackupRestorer;
use floor12\backup\tests\TestCase;
use Yii;
use yii\base\Exception;

class FolderBackupRestorerTest extends TestCase
{
    public function testFolderNoExists()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Target folder don`t exist.');
        $backupFilePath = Yii::getAlias('@app/data/folder.zip');
        $targetFolder = Yii::getAlias('@app/no-exists');
        new FolderBackupRestorer($backupFilePath, $targetFolder);
    }

    public function testFileNoExists()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Backup file don`t exist.');
        $backupFilePath = Yii::getAlias('@app/data/no-exist.tgz');
        $targetFolder = Yii::getAlias('@app/data/');
        new FolderBackupRestorer($backupFilePath, $targetFolder);
    }

    public function testRestoreSuccess()
    {
        $fileFromBackup = Yii::getAlias('@app/data/exists_test_file.txt');
        $backupFilePath = Yii::getAlias('@app/data/folder.zip');
        $targetFolder = Yii::getAlias('@app/data/');
        @unlink($fileFromBackup);
        $this->assertFileNotExists($fileFromBackup);
        $restorer = new FolderBackupRestorer($backupFilePath, $targetFolder);
        $restorer->execute();
        $this->assertFileExists($fileFromBackup);
        @unlink($fileFromBackup);
    }


}
