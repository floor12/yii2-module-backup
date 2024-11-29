<?php

namespace floor12\backup\logic;

use floor12\backup\models\IOPriority;
use floor12\backup\Module;
use Yii;
use yii\base\Exception;

class FolderBackupRestorer
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
     * @var Module
     */
    protected $module;
    /**
     * @var string
     */
    protected $io;

    /**
     * DatabaseBackupMaker constructor.
     * @param string $backupFilePath
     * @param string $targetFolder
     * @throws Exception
     */
    public function __construct(string $backupFilePath, string $targetFolder, $io = IOPriority::IDLE)
    {
        if (!file_exists($backupFilePath))
            throw new Exception("Backup file don`t exist..");

        if (!file_exists($targetFolder))
            throw new Exception("Target folder don`t exist.");

        $this->backupFilePath = $backupFilePath;
        $this->targetFolder = $targetFolder;

        $this->module = Yii::$app->getModule('backup');
        $this->io = $io;
    }

    /**
     * @return bool
     * @throws \Exception
     */
    public function execute()
    {
        $ionicePath = $this->module->binaries['ionice'];
        $unzipPath = $this->module->binaries['unzip'];
        $command = "cd {$this->targetFolder} && {$ionicePath} -c{$this->io} {$unzipPath} -o {$this->backupFilePath}";
        exec($command, $r);
        return true;
    }
}
