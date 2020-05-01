<?php
/**
 * Created by PhpStorm.
 * User: floor12
 * Date: 18.09.2018
 * Time: 20:35
 */

namespace floor12\backup\logic;

use ErrorException;
use floor12\backup\models\Backup;
use floor12\backup\models\BackupStatus;
use floor12\backup\models\BackupType;
use floor12\backup\models\IOPriority;
use Throwable;
use Yii;
use yii\base\InvalidConfigException as InvalidConfigExceptionAlias;
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

    /**
     * @var string
     */
    protected $dumperClass;
    private $configs;
    private $currentConfig;
    private $model;

    /**
     * BackupCreate constructor.
     * @param string $config_id
     * @throws InvalidConfigExceptionAlias
     * @throws ErrorException
     */
    public function __construct(string $config_id)
    {
        $this->loadConfigs();
        $this->setUpActiveConfig($config_id);
    }

    /**
     * @throws InvalidConfigExceptionAlias
     */
    protected function loadConfigs()
    {
        $this->configs = Yii::$app->getModule('backup')->configs;

        if (!is_array($this->configs) || !sizeof($this->configs))
            throw new InvalidConfigExceptionAlias('Backup module need to be configured with `config array`');
    }

    /**
     * @param string $config_id
     * @throws InvalidConfigExceptionAlias
     */
    protected function setUpActiveConfig(string $config_id)
    {
        foreach ($this->configs as $config) {
            if (isset($config['id']) && $config['id'] == $config_id)
                $this->currentConfig = $config;
        }
        if (!$this->currentConfig)
            throw new InvalidConfigExceptionAlias("Config `{$config_id}` not found.");
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

        if ($this->currentConfig['type'] == BackupType::DB) {
            $connection = Yii::$app->{$this->currentConfig['connection']};
            Yii::createObject(DatabaseBackuper::class, [$this->model->getFullPath(), $connection, $this->currentConfig['io']])->backup();
        }

        if ($this->currentConfig['type'] == BackupType::FILES) {
            $targetPath = Yii::getAlias($this->currentConfig['path']);
            Yii::createObject(FolderBackupMaker::class, [$this->model->getFullPath(), $targetPath, $this->currentConfig['io']])->execute();
        }

        $this->updateBackupInfo();
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
            ->where(['config_id' => $this->currentConfig['id']])
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
        $this->model->config_id = $this->currentConfig['id'];
        $this->model->config_name = $this->currentConfig['title'];
        $this->model->filename = $this->createFileName();
        return $this->model->save();
    }

    /** Generate filename
     * @return string
     */
    private function createFileName()
    {
        $extension = Backup::EXT_TGZ;
        if ($this->model->type == BackupType::FILES)
            $extension = Backup::EXT_ZIP;
        $date = date("Y-m-d_H-i-s");
        $rand = substr(md5(rand(0, 9999)), 0, 3);
        return "{$this->currentConfig['id']}_{$date}_{$rand}{$extension}";
    }

    /**
     * @return bool
     */
    protected function updateBackupInfo()
    {
        $this->model->status = BackupStatus::DONE;
        $this->model->updateFileSize();
        return $this->model->save();
    }

}
