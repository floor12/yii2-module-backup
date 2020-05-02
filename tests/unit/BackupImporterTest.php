<?php
/**
 * Created by PhpStorm.
 * User: floor12
 * Date: 11.11.2019
 * Time: 07:45
 */

namespace floor12\backup\tests\unit;

use floor12\backup\Exceptions\ConfigurationNotFoundException;
use floor12\backup\Exceptions\ModuleNotConfiguredException;
use floor12\backup\logic\BackupImporter;
use floor12\backup\models\Backup;
use floor12\backup\models\BackupStatus;
use floor12\backup\tests\TestCase;
use Yii;

class BackupImporterTest extends TestCase
{

    public function testEmptyConfigs()
    {
        $this->expectException(ModuleNotConfiguredException::class);
        $this->module->configs = [];
        $config_id = 'test_file_backup';
        $fileToImport = Yii::getAlias('@app/data/postgres_for_import');
        $importer = new BackupImporter($config_id, $fileToImport);
    }

    public function testWrongConfigName()
    {
        $this->expectException(ConfigurationNotFoundException::class);
        $fileToImport = Yii::getAlias('@app/data/postgres_for_import');
        $config_id = 'config_not_exists';
        $importer = new BackupImporter($config_id, $fileToImport);
    }

    public function testImportSuccess()
    {
        Backup::deleteAll();
        $config_id = 'postgres_db';
        $fileName = 'postgres_for_import';
        $fileToImport = Yii::getAlias("@app/data/{$fileName}");
        $importer = new BackupImporter($config_id, $fileToImport);
        $this->assertTrue($importer->import());
        $backup = Backup::findOne(['filename' => $fileName]);
        $this->assertIsObject($backup);
        $this->assertEquals(BackupStatus::IMPORTED, $backup->status);
        $this->assertEquals($config_id, $backup->config_id);
        $this->assertFileExists($backup->getFullPath());
        $backup->delete();
    }


}
