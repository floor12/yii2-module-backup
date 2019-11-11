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
use Ifsnop\Mysqldump\Mysqldump;
use Yii;
use yii\base\InvalidConfigException;

/**
 * Class BackupCreate
 * @package floor12\backup\logic
 * @property Backup $_model
 * @property array $_configs
 * @property array $_config
 */
class BackupCreate
{

    private $_configs;
    private $_currentConfig;
    private $_model;

    /**
     * BackupCreate constructor.
     * @param string $config_id
     * @throws InvalidConfigException
     * @throws \ErrorException
     */
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

    /** Основный метод, который запускает процесс
     * @return bool
     */
    public function run()
    {
        if (isset($this->_currentConfig['limit'])) {
            $backups = Backup::find()
                ->where(['config_id' => $this->_currentConfig['id']])
                ->orderBy('date DESC')
                ->offset($this->_currentConfig['limit'])
                ->all();
            if ($backups)
                foreach ($backups as $backup)
                    $backup->delete();
        }

        if ($this->_currentConfig['type'] == BackupType::DB)
            return $this->backupDatabase();

        if ($this->_currentConfig['type'] == BackupType::FILES)
            return $this->backupFiles();
    }

    /** Создаем экзеспляр бекапа в своей sqlite базе
     * @return bool
     */
    private function backupDatabase()
    {
        $this->_model->date = date('Y-m-d H:i:s');
        $this->_model->status = BackupStatus::IN_PROCESS;
        $this->_model->type = $this->_currentConfig['type'];
        $this->_model->config_id = $this->_currentConfig['id'];
        $this->_model->config_name = $this->_currentConfig['title'];
        $this->_model->filename = $this->createFileName() . Backup::EXT_TGZ;
        $this->_model->save();

        $this->dumpDatabase($this->_model->getFullPath());
        $this->_model->status = BackupStatus::DONE;
        $this->_model->updateFileSize();
        return $this->_model->save();
    }

    /** Generate filename
     * @return string
     */
    private function createFileName()
    {
        return $this->_currentConfig['id'] . "_" . date("Y-m-d_H-i-s");
    }

    /**
     * @param $pathFull
     */
    private function dumpDatabase($pathFull)
    {
        $connection = Yii::$app->{$this->_currentConfig['connection']};

        try {
            $dump = new Mysqldump(
                $connection->dsn,
                $connection->username,
                $connection->password,
                ['compress' => Mysqldump::GZIP]
            );
            $dump->start($pathFull);
            if (Yii::$app->getModule('backup')->chmod)
                chmod($pathFull, Yii::$app->getModule('backup')->chmod);
        } catch (\Exception $e) {
            echo 'mysqldump-php error: ' . $e->getMessage();
        }
    }

    private function backupFiles()
    {
        $this->_model->date = date('Y-m-d H:i:s');
        $this->_model->status = BackupStatus::IN_PROCESS;
        $this->_model->type = $this->_currentConfig['type'];
        $this->_model->config_id = $this->_currentConfig['id'];
        $this->_model->config_name = $this->_currentConfig['title'];
        $this->_model->filename = $this->createFileName() . Backup::EXT_ZIP;
        $this->_model->save();

        $this->dumpFiles($this->_model->getFullPath());
        $this->_model->status = BackupStatus::DONE;
        $this->_model->updateFileSize();
        return $this->_model->save();
    }

    private function dumpFiles($pathFull)
    {
        $path = Yii::getAlias($this->_currentConfig['path']);

        if (Yii::$app->getModule('backup')->ionice)
            exec(" cd {$path} && " . Yii::$app->getModule('backup')->ionice . " zip -r -0 {$pathFull} *");
        else
            exec("cd {$path} && zip -r -0 {$pathFull} *", $tmo);

        if (Yii::$app->getModule('backup')->chmod)
            chmod($pathFull, Yii::$app->getModule('backup')->chmod);

    }
}