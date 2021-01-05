<?php


namespace floor12\backup\logic\processors;


use floor12\backup\Exceptions\MysqlDumpException;
use floor12\backup\Exceptions\PostgresDumpException;

class MysqlProcessor extends DbProcessor
{

    public $port = 3306;

    public function backup(): void
    {
        $mysqldumpPath = $this->module->binaries['mysqldump'];
        $gzipPath = $this->module->binaries['gzip'];
        $ionicePath = $this->module->binaries['ionice'];
        $this->checkBinary($mysqldumpPath);
        $this->checkBinary($gzipPath);
        $this->checkBinary($ionicePath);
        $command = "{$ionicePath} -c{$this->io} {$mysqldumpPath} -h {$this->host} -P {$this->port} -u {$this->username}  -p{$this->password}  {$this->database} | {$gzipPath} -9 -c > {$this->backupFilePath}";
        (exec($command));
        if (!file_exists($this->backupFilePath) || filesize($this->backupFilePath) < 100)
            throw new MysqlDumpException();
    }

    public function restore(array $tableNames = []): void
    {
        $mysqlPath = $this->module->binaries['mysql'];
        $zcatPath = $this->module->binaries['zcat'];
        $this->checkBinary($zcatPath);
        $this->checkBinary($mysqlPath);
        $command = "{$zcatPath} {$this->backupFilePath} | {$mysqlPath} -h {$this->host} -P {$this->port} -u {$this->username} -p{$this->password} {$this->database}";
        exec($command, $return);
        if (!empty($return))
            throw new PostgresDumpException();
    }
}
