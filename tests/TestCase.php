<?php
/**
 * Created by PhpStorm.
 * User: floor12
 * Date: 07.01.2018
 * Time: 12:40
 */

namespace floor12\backup\tests;

use Yii;
use yii\console\Application;

abstract class TestCase extends \PHPUnit\Framework\TestCase
{

    public $sqlite = 'tests/sqlite.db';


    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        parent::setUp();
        $this->mockApplication();
    }

    protected function setApp()
    {
        $files = [
            'class' => 'floor12\files\Module',
            'storage' => '@app/storage',
        ];
        Yii::$app->setModule('files', $files);
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
     *  Запускаем приложение
     * @throws \yii\base\InvalidConfigException
     */
    protected function mockApplication()
    {
        new Application([
            'id' => 'testapp',
            'basePath' => __DIR__,
            'vendorPath' => dirname(__DIR__) . '/vendor',
            'runtimePath' => __DIR__ . '/runtime',
        ]);
    }


    /**
     * Убиваем приложение
     */
    protected function destroyApplication()
    {
        Yii::$app = null;
    }
}