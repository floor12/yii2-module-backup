<?php


namespace floor12\backup\logic;


use floor12\backup\logic\processors\DbProcessor;
use floor12\backup\logic\processors\MysqlProcessor;
use floor12\backup\logic\processors\PostgresProcessor;
use yii\base\Exception;
use yii\db\Connection;

class DatabaseBackupRestorer
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
     * DatabaseBackupMaker constructor.
     * @param string $backupFilePath
     * @param Connection $connection
     * @throws Exception
     */
    public function __construct(string $backupFilePath, Connection $connection)
    {
        if (file_exists($backupFilePath))
            throw new Exception("Backup file exists.");

        $currentProcessorClassname = $this->dbProcessors[$connection->driverName];
        $this->dbProcessor = new $currentProcessorClassname($backupFilePath, $connection);
    }

    /**
     * @return bool
     * @throws \Exception
     */
    public function backup()
    {
        $this->dbProcessor->backup();
    }
}
