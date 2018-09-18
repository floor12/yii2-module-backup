<?php
/**
 * Created by PhpStorm.
 * User: floor12
 * Date: 18.09.2018
 * Time: 20:35
 */

namespace floor12\backup\logic;

use floor12\backup\models\Backup;
use floor12\backup\models\BackupStatus;
use floor12\backup\models\BackupType;
use Yii;
use yii\base\InvalidConfigException;

class BackupCreate
{

    private $_configs;
    private $_currentConfig;
    private $_model;

    public function __construct(string $config_id)
    {
        $this->_configs = Yii::$app->getModule('backup')->configs;

        if (!is_array($this->_configs) || !sizeof($this->_configs))
            throw new InvalidConfigException('Backup module need to be configured with `config array`');

        foreach ($this->_configs as $config) {
            if (isset($config['id']) && $config['id'] == $config_id)
                $this->_currentConfig = $config;
        }

        if (!$this->_currentConfig)
            throw new \ErrorException("Config `{$config_id}` not found.");

        $this->_model = new Backup();

    }

    public function run()
    {
        if ($this->_currentConfig['type'] == BackupType::DB)
            $this->backupDb();
    }

    private function backupDb()
    {
        $this->_model->date = date('Y-m-d H:i:s');
        $this->_model->status = BackupStatus::IN_PROCESS;
        $this->_model->type = $this->_currentConfig['type'];
        $this->_model->config_id = $this->_currentConfig['id'];
        $this->_model->config_name = $this->_currentConfig['title'];
        $this->_model->filename = $this->createFileName();
        $this->_model->size = 0;
        $this->_model->save();
    }

    private function createFileName()
    {
        return $this->_currentConfig['id'] . "_" . date("Y-m-d_H-i-s");
    }
}