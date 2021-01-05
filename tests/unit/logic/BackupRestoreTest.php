<?php
/**
 * Created by PhpStorm.
 * User: floor12
 * Date: 11.11.2019
 * Time: 07:45
 */

namespace floor12\backup\tests\unit\logic;

/**
 * This is a tests for Backup class
 */

use ErrorException;
use floor12\backup\Exceptions\ModuleNotConfiguredException;
use floor12\backup\logic\BackupRestore;
use floor12\backup\models\Backup;
use floor12\backup\models\BackupStatus;
use floor12\backup\models\BackupType;
use floor12\backup\tests\TestCase;
use Yii;
use yii\base\InvalidConfigException;

class BackupRestoreTest extends TestCase
{
    /**
     * @throws ErrorException
     * @throws InvalidConfigException
     */
    public function testEmptyConfigs()
    {
        $backup = new Backup([
            'status' => BackupStatus::DONE,
            'filename' => 'test.gz',
            'type' => BackupType::FILES,
            'config_id' => 'tmp_folder'
        ]);
        $this->expectException(ModuleNotConfiguredException::class);
        $this->module->configs = [];
        new BackupRestore($backup);
    }

    /**
     * @throws ErrorException
     * @throws InvalidConfigException
     */
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
        new BackupRestore($backup);
    }

    /**
     * @throws ErrorException
     * @throws InvalidConfigException
     */
    public function testDatabaseSuccess()
    {
        $this->module->backupFolder = '@vendor/../tests/data';
        $this->module->backupRootPath = Yii::getAlias('@vendor/../tests/data');

        $backup = new Backup([
            'status' => BackupStatus::DONE,
            'filename' => 'postgres',
            'type' => BackupType::DB,
            'config_id' => 'postgres_db'
        ]);
        $restorer = new BackupRestore($backup);
        $restorer->run();
        $tableExists = Yii::$app->postgres->createCommand("SELECT EXISTS (
               SELECT FROM information_schema.tables
               WHERE  table_schema = 'public'
                 AND    table_name   = 'test_table'
           );")->queryScalar() == 'true';
        $this->assertTrue($tableExists);
        $dropResult = Yii::$app->postgres->createCommand()->dropTable('test_table')->execute();
        $this->assertEquals(0, $dropResult);
    }

    /**
     * @throws ErrorException
     * @throws InvalidConfigException
     */
    public function testFolderSuccess()
    {
        $this->module->backupFolder = '@vendor/../tests/data';
        $this->module->backupRootPath = Yii::getAlias('@vendor/../tests/data');

        $resultFilePath = Yii::getAlias('@app/data/folder_for_backup/exists_test_file.txt');
        @unlink($resultFilePath);
        $this->assertFileNotExists($resultFilePath);
        $backup = new Backup([
            'status' => BackupStatus::DONE,
            'filename' => 'folder.zip',
            'type' => BackupType::FILES,
            'config_id' => 'backup_test_folder'
        ]);
        $restorer = new BackupRestore($backup);
        $restorer->run();
        $this->assertFileExists($resultFilePath);
        @unlink($resultFilePath);
    }


}
