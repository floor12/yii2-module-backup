<?php


namespace floor12\backup\tests;


use Ifsnop\Mysqldump\Mysqldump;

class MysqldumpMock extends Mysqldump
{
    public function __construct(
        $dsn = '',
        $user = '',
        $pass = '',
        $dumpSettings = array(),
        $pdoSettings = array()
    )
    {
    }

    public function start($filename = '')
    {
        file_put_contents($filename, 'testdata');
    }
}