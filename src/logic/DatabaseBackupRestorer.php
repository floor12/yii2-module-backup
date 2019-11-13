<?php


namespace floor12\backup\logic;


use yii\base\Exception;
use yii\db\Connection;

class DatabaseBackupRestorer
{
    /**
     * @var string
     */
    protected $backupFilePath;
    /**
     * @var Connection
     */
    protected $connection;
    /**
     * @var string
     */
    protected $sql;

    /**
     * DatabaseBackupMaker constructor.
     * @param string $backupFilePath
     * @param Connection $connection
     * @param string $dumperClass
     * @throws Exception
     */
    public function __construct(string $backupFilePath, Connection $connection)
    {
        if (!file_exists($backupFilePath))
            throw new Exception("Backup file don`t exist.");

        $this->backupFilePath = $backupFilePath;
        $this->connection = $connection;
    }

    /**
     * @return bool
     * @throws \Exception
     */
    public function execute()
    {
        $dbName = $this->connection->createCommand("SELECT DATABASE()")->queryScalar();

        $this->connection->createCommand("DROP DATABASE `{$dbName}`")->execute();
        $this->connection->createCommand("CREATE DATABASE `{$dbName}`")->execute();
        $this->connection->createCommand("USE `{$dbName}`")->execute();

        $lines = gzfile($this->backupFilePath);

        foreach ($lines as $line) {
            if (substr($line, 0, 2) == '--' || $line == '')
                continue;
            $this->sql .= $line;
            if (substr(trim($line), -1, 1) == ';') {
                $this->connection->createCommand($this->sql)->execute();
                $this->sql = '';
            }
        }
        return true;
    }
}