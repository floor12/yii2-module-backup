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
    public function _before()
    {

    }

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

    public function testRestoreAllTables()
    {
        $this->assertFalse($this->isPostgresTableExists('test_table_1'));
        $this->assertFalse($this->isPostgresTableExists('test_table_2'));
        $backupFilePath = Yii::getAlias('@app/data/postgres_for_import.dump');
        $processor = new PostgresProcessor($backupFilePath, Yii::$app->postgres);
        $processor->restore();
        $this->assertTrue($this->isPostgresTableExists('test_table_1'));
        $this->assertTrue($this->isPostgresTableExists('test_table_2'));
    }

    public function testRestoreOneTable1()
    {
        $this->assertFalse($this->isPostgresTableExists('test_table_1'));
        $this->assertFalse($this->isPostgresTableExists('test_table_2'));
        $backupFilePath = Yii::getAlias('@app/data/postgres_for_import.dump');
        $processor = new PostgresProcessor($backupFilePath, Yii::$app->postgres);
        $processor->restore(['test_table_1']);
        $this->assertTrue($this->isPostgresTableExists('test_table_1'));
        $this->assertFalse($this->isPostgresTableExists('test_table_2'));
    }

    public function testRestoreOneTable2()
    {
        $this->assertFalse($this->isPostgresTableExists('test_table_1'));
        $this->assertFalse($this->isPostgresTableExists('test_table_2'));
        $backupFilePath = Yii::getAlias('@app/data/postgres_for_import.dump');
        $processor = new PostgresProcessor($backupFilePath, Yii::$app->postgres);
        $processor->restore(['test_table_2']);
        $this->assertTrue($this->isPostgresTableExists('test_table_2'));
        $this->assertFalse($this->isPostgresTableExists('test_table_1'));
    }

    public function testRestoreOneTable3()
    {
        $this->assertFalse($this->isPostgresTableExists('test_table_1'));
        $this->assertFalse($this->isPostgresTableExists('test_table_2'));
        $backupFilePath = Yii::getAlias('@app/data/postgres_for_import.dump');
        $processor = new PostgresProcessor($backupFilePath, Yii::$app->postgres);
        $processor->restore(['test_table_1']);
        $processor->restore(['test_table_2']);
        $this->assertTrue($this->isPostgresTableExists('test_table_2'));
        $this->assertTrue($this->isPostgresTableExists('test_table_1'));
    }

    public function testRestoreOneTable4()
    {
        $this->assertFalse($this->isPostgresTableExists('test_table_1'));
        $this->assertFalse($this->isPostgresTableExists('test_table_2'));
        $backupFilePath = Yii::getAlias('@app/data/postgres_for_import.dump');
        $processor = new PostgresProcessor($backupFilePath, Yii::$app->postgres);
        $processor->restore(['test_table_2']);
        $this->assertTrue($this->isPostgresTableExists('test_table_2'));
        Yii::$app->postgres->createCommand("DELETE FROM test_table_2")->execute();
        $this->assertTrue(empty(Yii::$app->postgres->createCommand("SELECT * FROM test_table_2")->queryAll()));
        $processor->restore(['test_table_2']);
        $this->assertTrue(Yii::$app->postgres->createCommand("SELECT count(*) FROM test_table_2")->queryScalar() === 2);
    }

    public function testGetTables()
    {
        $backupFilePath = Yii::getAlias('@app/data/postgres_for_import.dump');
        $processor = new PostgresProcessor($backupFilePath, Yii::$app->postgres);
        $tableList = $processor->getTables();
        self::assertIsArray($tableList);
        self::assertTrue(in_array('test_table_1', $tableList));
        self::assertTrue(in_array('test_table_2', $tableList));
    }
}