<?php

namespace floor12\backup\logic;

use Yii;
use yii\base\Exception;

class FolderBackupMaker
{
    /**
     * @var string
     */
    protected $backupFilePath;
    /**
     * @var string
     */
    protected $targetFolder;
    /**
     * @var string
     */
    protected $chmod;
    /**
     * @var string
     */
    protected $ionice;

    /**
     * DatabaseBackupMaker constructor.
     * @param string $backupFilePath
     * @param string $targetFolder
     * @throws Exception
     */
    public function __construct(string $backupFilePath, string $targetFolder)
    {
        if (file_exists($backupFilePath))
            throw new Exception("Backup file exists.");

        if (!file_exists($targetFolder))
            throw new Exception("Target folder not exists.");

        $this->backupFilePath = $backupFilePath;
        $this->targetFolder = $targetFolder;
        $this->chmod = Yii::$app->getModule('backup')->chmod;
        $this->ionice = Yii::$app->getModule('backup')->ionice;
    }

    /**
     * @return bool
     * @throws \Exception
     */
    public function execute()
    {
        if ($this->ionice)
            exec(" cd {$this->targetFolder} && " . $this->ionice . " zip -r -0 {$this->backupFilePath} *");
        else
            exec("cd {$this->targetFolder} && zip -r -0 {$this->backupFilePath} *", $tmo);

        if ($this->chmod)
            chmod($this->backupFilePath, $this->chmod);
        return true;
    }
}