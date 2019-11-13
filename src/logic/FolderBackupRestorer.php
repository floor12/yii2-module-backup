<?php

namespace floor12\backup\logic;

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
     * DatabaseBackupMaker constructor.
     * @param string $backupFilePath
     * @param string $targetFolder
     * @throws Exception
     */
    public function __construct(string $backupFilePath, string $targetFolder)
    {
        if (!file_exists($backupFilePath))
            throw new Exception("Backup file don`t exist..");

        if (!file_exists($targetFolder))
            throw new Exception("Target folder don`t exist.");

        $this->backupFilePath = $backupFilePath;
        $this->targetFolder = $targetFolder;
    }

    /**
     * @return bool
     * @throws \Exception
     */
    public function execute()
    {
        $command = "cd {$this->targetFolder} && unzip -o {$this->backupFilePath}";
        exec($command, $r);
        return true;
    }
}