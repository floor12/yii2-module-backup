<?php
/**
 * Created by PhpStorm.
 * User: floor12
 * Date: 18.09.2018
 * Time: 20:35
 */

namespace floor12\backup\logic;

use floor12\backup\models\Backup;
use floor12\backup\models\BackupType;
use Yii;
use yii\base\InvalidConfigException;
use yii\base\UnknownPropertyException;
use yii\db\Connection;
use yii\web\BadRequestHttpException;

/**
 * Class BackupRestore
 * @package floor12\backup\logic
 * @property Connection $_connection
 * @property Backup $_model
 * @property array $_configs
 * @property array $_config
 *
 */
class BackupRestore
{

    private $_configs;
    private $_currentConfig;
    private $_model;
    private $_connection;

    /**
     * BackupRestore constructor.
     * @param Backup $model
     * @throws InvalidConfigException
     * @throws \ErrorException
     */
    public function __construct(Backup $model)
    {
        $this->_configs = Yii::$app->getModule('backup')->configs;

        if (!is_array($this->_configs) || !sizeof($this->_configs))
            throw new InvalidConfigException('Backup module need to be configured with `config array`');

        foreach ($this->_configs as $config) {
            if (isset($config['id']) && $config['id'] == $model->config_id)
                $this->_currentConfig = $config;
        }

        if (!$this->_currentConfig)
            throw new \ErrorException("Config `{$model->config_id}` not found.");

        $this->_model = $model;

    }

    /** Основной метод, который запускает весь процесс
     * @throws \ErrorException
     */
    public function run()
    {
        if ($this->_currentConfig['type'] == BackupType::DB)
            return $this->restoreDatabase();

        if ($this->_currentConfig['type'] == BackupType::FILES)
            return $this->restoreFiles();
    }


    protected function restoreFiles()
    {
        $backupFile = Yii::getAlias(Yii::$app->getModule('backup')->backupFolder . DIRECTORY_SEPARATOR . $this->_model->filename);
        $restorePath = Yii::getAlias($this->_currentConfig['path']);

        if (!file_exists($backupFile))
            throw new BadRequestHttpException('Backup file not found.');

        if (!file_exists($restorePath))
            throw new BadRequestHttpException('Restore path not found.');

        exec("cd {$restorePath} && unzip -o {$backupFile}");
    }


    /** Восстанавливаем базу данных из дампа:
     * Сначала пересоздаем её, выбираем, а дальше построчно читаем дамп из гзип-файла
     * @throws \ErrorException
     * @throws \yii\db\Exception
     */
    private function restoreDatabase()
    {
        try {
            $this->_connection = Yii::$app->{$this->_currentConfig['connection']};
        } catch (UnknownPropertyException $e) {
            throw new \ErrorException("Cound not find connection Yii::\$app->{$this->_currentConfig['connection']}");
        }

        if (!function_exists("gzopen")) {
            throw new ErrorException("Compression is enabled, but gzip lib is not installed or configured properly");
        }

        $dbName = $this->_connection->createCommand("SELECT DATABASE()")->queryScalar();
        $this->_connection->createCommand("DROP DATABASE `{$dbName}`")->execute();
        $this->_connection->createCommand("CREATE DATABASE `{$dbName}`")->execute();
        $this->_connection->createCommand("USE `{$dbName}`")->execute();

        $sql = '';
        $lines = gzfile($this->_model->getFullPath());

        foreach ($lines as $line) {
            if (substr($line, 0, 2) == '--' || $line == '')
                continue;
            $sql .= $line;
            if (substr(trim($line), -1, 1) == ';') {
                $this->_connection->createCommand($sql)->execute();
                $sql = '';
            }
        }


    }
}