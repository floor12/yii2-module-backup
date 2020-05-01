<?php


namespace floor12\backup\logic\processors;


use floor12\backup\Exceptions\DsnParseException;
use floor12\backup\models\IOPriority;
use floor12\backup\Module;
use Yii;
use yii\db\Connection;

abstract class  DbProcessor
{
    /**
     * @var Connection
     */
    protected $connection;
    /**
     * @var array
     */
    protected $parsedDsn;
    /**
     * @var string
     */
    protected $backupFilePath;
    /**
     * @var Module
     */
    protected $module;
    protected $username;
    protected $database;
    protected $host;
    protected $port;
    protected $io;
    protected $password;


    /**
     * DbProcessor constructor.
     * @param string $backupFilePath
     * @param Connection $connection
     */
    public function __construct(string $backupFilePath, Connection $connection, $io = IOPriority::IDLE)
    {
        $this->parseDsn($connection->dsn);
        $this->backupFilePath = $backupFilePath;
        $this->username = $connection->username;
        $this->password = $connection->password;
        $this->module = Yii::$app->getModule('backup');
        $this->io = $io;
        $this->init();
    }

    protected function parseDsn(string $dsn)
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
        $this->port = $this->parsedDsn['port'] ?: null;
        $this->database = $this->parsedDsn['dbname'] ?: null;
    }


    public function backup()
    {

    }

    public function restore()
    {

    }

    public function init()
    {

    }
}
