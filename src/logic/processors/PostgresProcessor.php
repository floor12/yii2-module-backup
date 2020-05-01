<?php


namespace floor12\backup\logic\processors;


use floor12\backup\Exceptions\PostgresDumpException;

class PostgresProcessor extends DbProcessor
{

    public function backup()
    {
        $binaryPath = $this->module->binaries['pg_dump'];
        $ionicePath = $this->module->binaries['ionice'];
        $command = "PGPASSWORD='{$this->password}' {$ionicePath} -c{$this->io} {$binaryPath} -h {$this->host} -p {$this->port} -U {$this->username} -Fc -Z1 {$this->database} -f {$this->backupFilePath}";
        exec($command, $return);
        if (!empty($return))
            throw new PostgresDumpException();
    }

    public function restore()
    {
        $binaryPath = $this->module->binaries['pg_restore'];
        $command = "PGPASSWORD='{$this->password}' {$binaryPath} -c -Fc -j 4 -h {$this->host} -p {$this->port} -U {$this->username}  -d {$this->database} {$this->backupFilePath}";
        exec($command, $return);
        if (!empty($return))
            throw new PostgresDumpException();
    }

    public function init()
    {
        $this->port = $this->port ?: 5432;
    }
}
