<?php
/**
 * Created by PhpStorm.
 * User: floor12
 * Date: 11.11.2019
 * Time: 07:45
 */

namespace floor12\backup\tests\unit\logic\processors;

/**
 * This is a tests for Backup class
 */


use floor12\backup\logic\processors\PostgresProcessor;
use floor12\backup\tests\TestCase;
use Yii;

class PostgresProcessorTest extends TestCase
{
    public function testBackup()
    {
        $backupFilePath = Yii::getAlias('@app/_output/postgres.dump');
        @unlink($backupFilePath);
        self::assertFileNotExists($backupFilePath);
        $processor = new PostgresProcessor($backupFilePath, Yii::$app->postgres);
        $processor->backup();
        self::assertFileExists($backupFilePath);
        @unlink($backupFilePath);
    }

    public function testRestore()
    {
        $this->assertFalse($this->isPostgresTableExists('test_table'));
        $backupFilePath = Yii::getAlias('@app/data/postgres_for_import');
        $processor = new PostgresProcessor($backupFilePath, Yii::$app->postgres);
        $processor->restore();
        $this->assertTrue($this->isPostgresTableExists('test_table'));
        $dropResult = Yii::$app->postgres->createCommand()->dropTable('test_table')->execute();
        $this->assertEquals(0, $dropResult);
    }

    /**@todo допилить чтение таблиц */
    public function testGetTables()
    {

    }
}