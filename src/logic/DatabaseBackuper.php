<?php


namespace floor12\backup\logic;


use floor12\backup\logic\processors\DbProcessor;
use floor12\backup\logic\processors\MysqlProcessor;
use floor12\backup\logic\processors\PostgresProcessor;
use yii\base\Exception;
use yii\db\Connection;

class DatabaseBackuper
{
    /**
     * @var array
     */
    protected $dbProcessors = [
        'mysql' => MysqlProcessor::class,
        'pgsql' => PostgresProcessor::class,
    ];
    /**
     * @var DbProcessor
     */
    protected $dbProcessor;
    /**
     * @var string
     */
    protected $backupFilePath;

    /**
     * DatabaseBackupMaker constructor.
     * @param string $backupFilePath
     * @param Connection $connection
     * @throws Exception
     */
    public function __construct(string $backupFilePath, Connection $connection)
    {
        $this->backupFilePath = $backupFilePath;
        $currentProcessorClassname = $this->dbProcessors[$connection->driverName];
        $this->dbProcessor = new $currentProcessorClassname($backupFilePath, $connection);
    }

    /**
     * @return bool
     * @throws \Exception
     */
    public function backup()
    {
        if (file_exists($this->backupFilePath))
            throw new Exception("Backup file exists.");
        $this->dbProcessor->backup();
    }

    /**
     * @return bool
     * @throws \Exception
     */
    public function restore(array $tableNames)
    {
        $this->dbProcessor->restore($tableNames);
    }
}
