<?php


namespace floor12\backup\logic\processors;


use floor12\backup\Exceptions\BinaryNotFoundException;
use floor12\backup\Exceptions\DsnParseException;
use floor12\backup\models\IOPriority;
use floor12\backup\Module;
use Yii;
use yii\db\Connection;

abstract class  DbProcessor implements DbProcessorInterface
{
    /**@var Connection */
    protected $connection;
    /**@var array */
    protected $parsedDsn;
    /**@var string */
    protected $backupFilePath;
    /**@var Module */
    protected $module;
    /**@var string */
    protected $username;
    /**@var string */
    protected $database;
    /**@var string */
    protected $host;
    /** @var int */
    protected $port;
    /**@var string */
    protected $io;
    /**@var string */
    protected $password;


    /**
     * DbProcessor constructor.
     * @param string $backupFilePath
     * @param Connection $connection
     * @throws DsnParseException
     */
    public function __construct(string $backupFilePath, Connection $connection, $io = IOPriority::IDLE)
    {
        $this->parseDsn($connection->dsn);
        $this->backupFilePath = $backupFilePath;
        $this->username = $connection->username;
        $this->password = $connection->password;
        $this->module = Yii::$app->getModule('backup');
        $this->io = $io;
    }

    protected function parseDsn(string $dsn): void
    {
        $dsn = substr($dsn, strpos($dsn, ':') + 1);
        $dsnParts = explode(';', $dsn);

        if (empty($dsnParts))
            throw new DsnParseException();

        foreach ($dsnParts as $part) {
            $row = explode('=', $part);
            if (isset($row[0]) && isset($row[1]))
                $this->parsedDsn[$row[0]] = $row[1];
        }

        $this->host = $this->parsedDsn['host'] ?: null;
        if (!empty($this->parsedDsn['port'])) {
            $this->port = $this->parsedDsn['port'];
        }
        $this->database = $this->parsedDsn['dbname'] ?: null;
    }

    public function setPort(int $port): void
    {
        $this->port = $port;
    }

    protected function checkBinary(string $binary): void
    {
        if (!file_exists($binary) || !is_executable($binary)) {
            throw new BinaryNotFoundException($binary);
        }
    }

    public function backup(): void
    {

    }

    public function restore(array $tableNames = []): void
    {

    }

    public function getTables(): array
    {
        return [];
    }

}
