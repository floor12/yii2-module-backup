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

use floor12\backup\logic\BackupCreate;
use floor12\backup\models\Backup;
use floor12\backup\models\BackupType;
use floor12\backup\tests\MysqldumpMock;
use floor12\backup\tests\TestCase;
use Yii;
use yii\base\InvalidConfigException;

class BackupCreateTest extends TestCase
{

    public function testEmptyConfigs()
    {
        $this->expectException(InvalidConfigException::class);
        $this->module->configs = [];
        $this->expectExceptionMessage('Backup module need to be configured with `config array`');
        $backupFilePath = Yii::getAlias('@app/tmp/sqlite.db');
        $creator = new BackupCreate('main');
    }

    public function testWrongConfigName()
    {
        $this->expectException(InvalidConfigException::class);
        $config_id = 'wrong';
        $this->expectExceptionMessage("Config `{$config_id}` not found.");
        $creator = new BackupCreate($config_id);
    }

    public function testDatabaseSuccess()
    {
        Backup::deleteAll();
        $this->assertEquals(0, Backup::find()->count());
        $config_id = 'main_db';
        $dumperClass = MysqldumpMock::class;
        $creator = new BackupCreate($config_id, $dumperClass);
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
        $config_id = 'tmp_folder';
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
        Yii::$app->getModule('backup')->configs[1]['limit'] = 1;

        $this->assertEquals(0, Backup::find()->count());
        $this->create5TestsBackups();
        $this->assertEquals(1, Backup::find()->count());
    }

    public function testSaveThreeBackups()
    {
        Backup::deleteAll();
        Yii::$app->getModule('backup')->configs[1]['limit'] = 3;

        $this->assertEquals(0, Backup::find()->count());
        $this->create5TestsBackups();
        $this->assertEquals(3, Backup::find()->count());
    }

    protected function create5TestsBackups()
    {
        $config_id = 'tmp_folder';
        $creator = new BackupCreate($config_id);
        for ($i = 0; $i < 5; $i++)
            $creator->run();
    }


}
