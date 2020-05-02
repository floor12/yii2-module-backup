<?php


namespace floor12\backup\logic;


use floor12\backup\Exceptions\ConfigurationNotFoundException;
use floor12\backup\Exceptions\FileNotFoundException;
use floor12\backup\Exceptions\ModuleNotConfiguredException;
use floor12\backup\models\Backup;
use floor12\backup\models\BackupStatus;
use floor12\backup\Module;
use Yii;

class BackupImporter
{
    /** @var Backup */
    protected $model;
    /** @var string */
    protected $absoluteFilePath;
    /** @var Module */
    protected $module;
    /** @var array */
    protected $currentConfig;

    /**
     * BackupImporter constructor.
     * @param string $config_id
     * @param string $absoluteFilePath
     */
    public function __construct(string $config_id, string $absoluteFilePath)
    {
        $this->module = Yii::$app->getModule('backup');

        if (!file_exists($absoluteFilePath))
            throw new FileNotFoundException();

        if (!is_array($this->module->configs) || empty($this->module->configs))
            throw new ModuleNotConfiguredException();

        if (!is_array($this->module->configs[$config_id]))
            throw new ConfigurationNotFoundException();

        $this->currentConfig = $this->module->configs[$config_id];
        $this->model = new Backup();
        $this->model->config_id = $config_id;
        $this->absoluteFilePath = $absoluteFilePath;
    }

    /**
     * @return bool
     */
    public function import()
    {
        $this->loadDataToBackup();
        return $this->model->save();
    }


    protected function loadDataToBackup()
    {
        $this->model->type = $this->currentConfig['type'];
        $this->model->type = $this->currentConfig['type'];
        $this->model->config_name = $this->currentConfig['title'];
        $this->model->size = filesize($this->absoluteFilePath);
        $this->model->date = date('Y-m-d H:i:s');
        $this->model->filename = basename($this->absoluteFilePath);
        $this->model->status = $this->copyFile() ? BackupStatus::IMPORTED : BackupStatus::IMPORT_ERROR;
    }

    /**
     * @return bool
     */
    protected function copyFile()
    {
        return copy($this->absoluteFilePath, $this->model->getFullPath());
    }

}
