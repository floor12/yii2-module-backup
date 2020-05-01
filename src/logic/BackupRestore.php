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
use floor12\backup\models\Backup;
use floor12\backup\models\BackupType;
use floor12\backup\models\IOPriority;
use Yii;
use yii\base\InvalidConfigException;
use yii\db\Connection;

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
    private $currentConfig;
    private $_model;
    private $_connection;
    private $backupFilePath;

    /**
     * BackupRestore constructor.
     * @param Backup $model
     * @throws InvalidConfigException
     * @throws ErrorException
     */
    public function __construct(Backup $model)
    {
        $this->_configs = Yii::$app->getModule('backup')->configs;

        if (!is_array($this->_configs) || !sizeof($this->_configs))
            throw new InvalidConfigException('Backup module need to be configured with `config array`');

        foreach ($this->_configs as $config) {
            if (isset($config['id']) && $config['id'] == $model->config_id)
                $this->currentConfig = $config;
        }

        if (!$this->currentConfig)
            throw new ErrorException("Config `{$model->config_id}` not found.");

        $this->_model = $model;
        $this->backupFilePath = Yii::getAlias(Yii::$app->getModule('backup')->backupFolder . DIRECTORY_SEPARATOR . $this->_model->filename);
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
            $this->_connection = Yii::$app->{$this->currentConfig['connection']};
            return Yii::createObject(DatabaseBackuper::class, [$this->backupFilePath, $this->_connection, $this->currentConfig['io']])->restore();
        }

        if ($this->currentConfig['type'] == BackupType::FILES) {
            $targetFolder = Yii::getAlias($this->currentConfig['path']);
            return Yii::createObject(FolderBackupRestorer::class, [$this->backupFilePath, $targetFolder, $this->currentConfig['io']])->execute();
        }
    }
}
