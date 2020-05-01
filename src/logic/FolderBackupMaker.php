<?php

namespace floor12\backup\logic;

use floor12\backup\Exceptions\FolderDumpException;
use floor12\backup\models\IOPriority;
use floor12\backup\Module;
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
    protected $io;
    /**
     * @var Module
     */
    protected $module;

    /**
     * DatabaseBackupMaker constructor.
     * @param string $backupFilePath
     * @param string $targetFolder
     * @throws Exception
     */
    public function __construct(string $backupFilePath, string $targetFolder, $io = IOPriority::IDLE)
    {
        if (file_exists($backupFilePath))
            throw new Exception("Backup file exists.");

        if (!file_exists($targetFolder))
            throw new Exception("Target folder not exists.");

        $this->module = Yii::$app->getModule('backup');
        $this->backupFilePath = $backupFilePath;
        $this->targetFolder = $targetFolder;
        $this->io = $io;
    }

    /**
     * @return bool
     * @throws \Exception
     * @throws FolderDumpException
     */
    public function execute()
    {
        $ionicePath = $this->module->binaries['ionice'];
        $zipPath = $this->module->binaries['zip'];
        $command = "cd {$this->targetFolder} && {$ionicePath} -c{$this->io} {$zipPath} -r -0 {$this->backupFilePath} * > /dev/null";
        exec($command);
        if (!file_exists($this->backupFilePath))
            throw new FolderDumpException();

        if ($this->module->chmod)
            chmod($this->backupFilePath, $this->module->chmod);

        return true;
    }
}
