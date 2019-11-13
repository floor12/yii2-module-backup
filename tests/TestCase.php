<?php
/**
 * Created by PhpStorm.
 * User: floor12
 * Date: 07.01.2018
 * Time: 12:40
 */

namespace floor12\backup\tests;

use floor12\backup\Module;
use Yii;
use yii\base\InvalidConfigException;
use yii\console\Application;

abstract class TestCase extends \PHPUnit\Framework\TestCase
{
    /**
     * @var string Path to sqlite database for tests
     */
    public $sqlite = 'tests/sqlite.db';
    /**
     * @var Module
     */
    public $module;

    /**
     * @inheritdoc
     * @throws InvalidConfigException
     */
    protected function setUp()
    {
        $this->mockApplication();
        $this->setApp();
        parent::setUp();
    }

    /**
     * Adds backup module to tmp app
     */
    protected function setApp()
    {
        $backupModule = [
            'class' => 'floor12\backup\Module',
            'backupFolder' => '@vendor/../tests/tmp',
        ];
        Yii::$app->setModule('backup', $backupModule);
        $this->module = Yii::$app->getModule('backup');
    }


    /**
     * @inheritdoc
     */
    protected function tearDown()
    {
        $this->destroyApplication();
        parent::tearDown();
    }

    /**
     * @throws InvalidConfigException
     */
    protected function mockApplication()
    {
        new Application([
            'id' => 'testApp',
            'basePath' => __DIR__,
            'vendorPath' => dirname(__DIR__) . '/vendor',
            'runtimePath' => __DIR__ . '/runtime',
        ]);
    }


    /**
     * Destroy test application
     */
    protected function destroyApplication()
    {
        Yii::$app = null;
    }
}