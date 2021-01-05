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

use floor12\backup\Exceptions\ConfigurationNotFoundException;
use floor12\backup\Exceptions\ModuleNotConfiguredException;
use floor12\backup\logic\BackupCreate;
use floor12\backup\models\Backup;
use floor12\backup\models\BackupType;
use floor12\backup\tests\TestCase;
use Yii;

class BackupCreateTest extends TestCase
{

    public function testEmptyConfigs()
    {
        $this->expectException(ModuleNotConfiguredException::class);
        $this->module->configs = [];
         Yii::getAlias('@app/tmp/sqlite.db');
        new BackupCreate('main');
    }

    public function testWrongConfigName()
    {
        $this->expectException(ConfigurationNotFoundException::class);
        $config_id = 'wrong';
        new BackupCreate($config_id);
    }

    public function testDatabaseSuccess()
    {
        Backup::deleteAll();
        $this->assertEquals(0, Backup::find()->count());
        $config_id = 'postgres_db';
        $creator = new BackupCreate($config_id);
        $creator->run();
        $backup = Backup::find()->one();
        $this->assertTrue(is_object($backup));
        $this->assertEquals(BackupType::DB, $backup->type);
        $this->assertFileExists($backup->getFullPath());
        @unlink($backup->getFullPath());
    }

    public function testFolderSuccess()
    {
        Backup::deleteAll();
        $this->assertEquals(0, Backup::find()->count());
        $config_id = 'backup_test_folder';
        $creator = new BackupCreate($config_id);
        $creator->run();

        $backup = Backup::find()->one();
        $this->assertTrue(is_object($backup));
        $this->assertEquals(BackupType::FILES, $backup->type);
        $this->assertFileExists($backup->getFullPath());
        // @unlink($backup->getFullPath());
    }

    public function testSaveAllOldBackups()
    {
        Backup::deleteAll();
        $this->assertEquals(0, Yii::$app->getModule('backup')->configs[1]['limit']);
        $this->assertEquals(0, Backup::find()->count());
        $this->create5TestsBackups();
        $this->assertEquals(5, Backup::find()->count());
    }

    public function testSaveOnlyOneBackup()
    {
        Backup::deleteAll();
        Yii::$app->getModule('backup')->configs['backup_test_folder']['limit'] = 1;

        $this->assertEquals(0, Backup::find()->count());
        $this->create5TestsBackups();
        $this->assertEquals(1, Backup::find()->count());
    }

    public function testSaveThreeBackups()
    {
        Backup::deleteAll();
        Yii::$app->getModule('backup')->configs['backup_test_folder']['limit'] = 3;

        $this->assertEquals(0, Backup::find()->count());
        $this->create5TestsBackups();
        $this->assertEquals(3, Backup::find()->count());
    }

    protected function create5TestsBackups()
    {
        $config_id = 'backup_test_folder';
        $creator = new BackupCreate($config_id);
        for ($i = 0; $i < 5; $i++)
            $creator->run();
    }


}
