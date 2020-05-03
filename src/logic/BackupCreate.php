<?php
/**
 * Created by PhpStorm.
 * User: floor12
 * Date: 18.09.2018
 * Time: 20:35
 */

namespace floor12\backup\logic;

use ErrorException;
use floor12\backup\Exceptions\ConfigurationNotFoundException;
use floor12\backup\Exceptions\FolderDumpException;
use floor12\backup\Exceptions\ModuleNotConfiguredException;
use floor12\backup\Exceptions\MysqlDumpException;
use floor12\backup\Exceptions\PostgresDumpException;
use floor12\backup\models\Backup;
use floor12\backup\models\BackupStatus;
use floor12\backup\models\BackupType;
use floor12\backup\models\IOPriority;
use Throwable;
use Yii;
use yii\base\InvalidConfigException as InvalidConfigExceptionAlias;
use yii\db\Connection;
use yii\db\StaleObjectException;

/**
 * Class BackupCreate
 * @package floor12\backup\logic
 * @property Backup $model
 * @property array $configs
 * @property array $_config
 */
class BackupCreate
{
    /** @var array */
    private $configs = [];
    /** @var array */
    private $currentConfig = [];
    /** @var string */
    private $config_id;
    /** @var Backup */
    private $model;

    /**
     * BackupCreate constructor.
     * @param string $config_id
     * @throws InvalidConfigExceptionAlias
     * @throws ErrorException
     */
    public function __construct(string $config_id)
    {
        $this->config_id = $config_id;
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
     * @return void
     * @throws InvalidConfigExceptionAlias
     * @throws Throwable
     * @throws StaleObjectException
     */
    public function run()
    {
        $this->deleteOldFiles();
        $this->createBackupItem();

        if (empty($this->currentConfig['io']))
            $this->currentConfig['io'] = IOPriority::IDLE;
        try {
            if ($this->currentConfig['type'] == BackupType::DB) {
                $connection = Yii::$app->{$this->currentConfig['connection']};
                Yii::createObject(DatabaseBackuper::class, [$this->model->getFullPath(), $connection, $this->currentConfig['io']])->backup();
            }

            if ($this->currentConfig['type'] == BackupType::FILES) {
                $targetPath = Yii::getAlias($this->currentConfig['path']);
                Yii::createObject(FolderBackupMaker::class, [$this->model->getFullPath(), $targetPath, $this->currentConfig['io']])->execute();
            }
        } catch (MysqlDumpException|PostgresDumpException|FolderDumpException $exception) {
            $this->setBackupFailed();
            throw new $exception;
        }

        $this->setBackupDone();
    }

    /**
     * Delete old files if current config has files count limit
     * @throws Throwable
     * @throws StaleObjectException
     */
    protected function deleteOldFiles()
    {
        if (!isset($this->currentConfig['limit']) || empty($this->currentConfig['limit']))
            return false;
        $backups = Backup::find()
            ->where(['config_id' => $this->config_id])
            ->orderBy('date DESC')
            ->offset($this->currentConfig['limit'] - 1)
            ->all();

        if ($backups)
            foreach ($backups as $backup)
                $backup->delete();
    }

    /**
     * @return bool
     */
    protected function createBackupItem()
    {
        $this->model = new Backup();
        $this->model->date = date('Y-m-d H:i:s');
        $this->model->status = BackupStatus::IN_PROCESS;
        $this->model->type = $this->currentConfig['type'];
        $this->model->config_id = $this->config_id;
        $this->model->config_name = $this->currentConfig['title'];
        $this->model->filename = $this->createFileName();
        return $this->model->save();
    }

    /** Generate filename
     * @return string
     */
    private function createFileName()
    {
        $extension = Backup::EXT_ZIP;
        if ($this->model->type == BackupType::DB) {
            $extension = Backup::EXT_TGZ;
            /** @var Connection $connection */
            $connection = Yii::$app->{$this->currentConfig['connection']};
            if ($connection->driverName == 'pgsql')
                $extension = Backup::EXT_DUMP;
        }
        $date = date("Y-m-d_H-i-s");
        $rand = substr(md5(rand(0, 9999)), 0, 3);
        return "{$this->config_id}_{$date}_{$rand}.{$extension}";
    }

    /**
     * @return bool
     */
    protected function setBackupDone()
    {
        $this->model->status = BackupStatus::DONE;
        $this->model->updateFileSize();
        return $this->model->save();
    }

    /**
     * @return bool
     */
    protected function setBackupFailed()
    {
        $this->model->status = BackupStatus::ERROR;
        @unlink($this->model->getFullPath());
        return $this->model->save();
    }

}
