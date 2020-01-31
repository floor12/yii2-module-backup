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
use Ifsnop\Mysqldump\Mysqldump;
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

    private $configs;
    private $currentConfig;
    private $model;
    /**
     * @var string
     */
    protected $dumperClass;

    /**
     * BackupCreate constructor.
     * @param string $config_id
     * @throws InvalidConfigExceptionAlias
     * @throws ErrorException
     */
    public function __construct(string $config_id, string $dumperClass = Mysqldump::class)
    {
        $this->dumperClass = $dumperClass;
        $this->configs = Yii::$app->getModule('backup')->configs;

        if (!is_array($this->configs) || !sizeof($this->configs))
            throw new InvalidConfigExceptionAlias('Backup module need to be configured with `config array`');

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

        if ($this->currentConfig['type'] == BackupType::DB) {
            $connection = Yii::$app->{$this->currentConfig['connection']};
            Yii::createObject(DatabaseBackupMaker::class, [$this->model->getFullPath(), $connection, $this->dumperClass])->execute();
        }

        if ($this->currentConfig['type'] == BackupType::FILES) {
            $targetPath = Yii::getAlias($this->currentConfig['path']);
            Yii::createObject(FolderBackupMaker::class, [$this->model->getFullPath(), $targetPath])->execute();
        }

        $this->finalize();
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

    /**
     * @return bool
     */
    protected function finalize()
    {
        $this->model->status = BackupStatus::DONE;
        $this->model->updateFileSize();
        return $this->model->save();
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

    /** Generate filename
     * @return string
     */
    private function createFileName()
    {
        $extension = BackupType::DB ? Backup::EXT_TGZ : Backup::EXT_ZIP;
        $date = date("Y-m-d_H-i-s");
        $rand = substr(md5(rand(0, 9999)), 0, 3);
        return "{$this->currentConfig['id']}_{$date}_{$rand}{$extension}";
    }

}
