<?php
/**
 * Created by PhpStorm.
 * User: floor12
 * Date: 18.09.2018
 * Time: 20:35
 */

namespace floor12\backup\logic;

use ErrorException;
use Exception;
use floor12\backup\Exceptions\ConfigurationNotFoundException;
use floor12\backup\Exceptions\ModuleNotConfiguredException;
use floor12\backup\models\Backup;
use floor12\backup\models\BackupType;
use floor12\backup\models\IOPriority;
use Yii;
use yii\base\InvalidConfigException;
use yii\db\Connection;

class BackupRestore
{
    /** @var array */
    private $configs = [];
    /** @var array */
    private $currentConfig = [];
    /** @var array */
    private $params = [];
    /** @var string */
    private $config_id;
    /** @var Backup */
    private $model;
    /** @var Connection */
    private $connection;

    /**
     * BackupRestore constructor.
     * @param Backup $model
     * @throws InvalidConfigException
     * @throws ErrorException
     */
    public function __construct(Backup $model, array $params = [])
    {
        $this->config_id = $model->config_id;
        $this->model = $model;
        $this->params = $params;
        $this->loadConfigs();
        $this->setUpActiveConfig();
    }


    /**
     * @throws ModuleNotConfiguredException
     */
    protected function loadConfigs()
    {
        $this->configs = Yii::$app->getModule('backup')->configs;
        if (!is_array($this->configs) || !sizeof($this->configs))
            throw new ModuleNotConfiguredException('Backup configs is empty');
    }

    /**
     * @param string $config_id
     * @throws ConfigurationNotFoundException
     */
    protected function setUpActiveConfig()
    {
        if (!is_array($this->configs[$this->config_id]))
            throw new ConfigurationNotFoundException("Configuration `{$this->config_id}` not found.");
        $this->currentConfig = $this->configs[$this->config_id];
    }

    /**
     * Ma
     * @return bool
     * @throws InvalidConfigException
     * @throws Exception
     */
    public function run()
    {
        if (empty($this->currentConfig['io']))
            $this->currentConfig['io'] = IOPriority::IDLE;

        if ($this->currentConfig['type'] == BackupType::DB) {
            $this->connection = Yii::$app->{$this->currentConfig['connection']};
            return Yii::createObject(DatabaseBackuper::class, [$this->model->getFullPath(), $this->connection, $this->currentConfig['io']])
                ->restore($this->params);
        }

        if ($this->currentConfig['type'] == BackupType::FILES) {
            $targetFolder = Yii::getAlias($this->currentConfig['path']);
            return Yii::createObject(FolderBackupRestorer::class, [$this->model->getFullPath(), $targetFolder, $this->currentConfig['io']])->execute();
        }
    }
}
