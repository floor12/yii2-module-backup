<?php


namespace floor12\backup\tests;


use yii\db\Connection;

class ConnectionMock extends Connection
{

    public $databaseName;
    /**
     * @var array
     */
    public $sql = [];

    public function __construct($config = [])
    {
        parent::__construct($config);
    }

    public function createCommand($sql = null, $params = [])
    {
        $this->sql[] = trim($sql);
        return $this;
    }

    public function execute()
    {
        return true;
    }

    public function queryScalar()
    {
        return $this->databaseName;
    }

}