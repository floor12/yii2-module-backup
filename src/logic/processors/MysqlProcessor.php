<?php


namespace floor12\backup\logic\processors;


use floor12\backup\Exceptions\MysqlDumpException;
use floor12\backup\Exceptions\PostgresDumpException;

class MysqlProcessor extends DbProcessor
{

    public function init()
    {
        $this->port = $this->port ?: 3306;
    }

    public function backup()
    {
        $mysqldumpPuth = $this->module->binaries['mysqldump'];
        $gzipPath = $this->module->binaries['gzip'];
        $ionicePath = $this->module->binaries['ionice'];
        $command = "{$ionicePath} -c{$this->io} {$mysqldumpPuth} -h {$this->host} -P {$this->port} -u {$this->username}  -p{$this->password}  {$this->database} | {$gzipPath} -9 -c > {$this->backupFilePath}";
        (exec($command));
        if (!file_exists($this->backupFilePath) || filesize($this->backupFilePath) < 100)
            throw new MysqlDumpException();
    }

    public function restore()
    {
        $mysqlPath = $this->module->binaries['mysql'];
        $zcatPath = $this->module->binaries['zcat'];
        $command = "{$zcatPath} {$this->backupFilePath} | {$mysqlPath} -h {$this->host} -P {$this->port} -u {$this->username} -p{$this->password} {$this->database}";
        exec($command, $return);
        if (!empty($return))
            throw new PostgresDumpException();
    }
}