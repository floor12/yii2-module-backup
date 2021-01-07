<?php


namespace floor12\backup\logic\processors;


use floor12\backup\Exceptions\PostgresDumpException;

class PostgresProcessor
    extends DbProcessor
    implements DbProcessorInterface
{

    public $port = 5432;

    public function backup(): void
    {
        $binaryPath = $this->module->binaries['pg_dump'];
        $ionicePath = $this->module->binaries['ionice'];
        $command = "PGPASSWORD='{$this->password}' {$ionicePath} -c{$this->io} {$binaryPath} -h {$this->host} -p {$this->port} -U {$this->username} -Fc -Z1 {$this->database} -f {$this->backupFilePath}";
        exec($command, $return);
        if (!empty($return))
            throw new PostgresDumpException();
    }

    public function restore(array $tableNames = []): void
    {
        $binaryPath = $this->module->binaries['pg_restore'];
        if (empty($tableNames)) {
            $command = "PGPASSWORD='{$this->password}' {$binaryPath} -c -Fc -j 4 -h {$this->host} -p {$this->port} -U {$this->username}  -d {$this->database} {$this->backupFilePath}";
        } else {
            foreach ($tableNames as $tableName) {
                $command = "PGPASSWORD='{$this->password}' {$binaryPath} -t {$tableName} -Fc -h {$this->host} -p {$this->port} -U {$this->username}  -d {$this->database} {$this->backupFilePath}";
            }
        }
        exec($command, $return);
        if (!empty($return))
            throw new PostgresDumpException();
    }

    public function getTables(): array
    {
        $binaryPath = $this->module->binaries['pg_restore'];
        $command = "PGPASSWORD='{$this->password}' {$binaryPath} -l {$this->backupFilePath}| grep TABLE";
        exec($command, $dumpContentLines);
        if (empty($dumpContentLines))
            throw new PostgresDumpException('Empty output received from `pg_restore -l` command.');
        $tables = [];
        foreach ($dumpContentLines as $dumpContentLine) {
            if (preg_match('/TABLE public (\w+) /', $dumpContentLine, $result)) {
                $tables[] = $result[1];
            }
        }
        return $tables;
    }


}
