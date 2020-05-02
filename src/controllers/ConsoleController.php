<?php
/**
 * Created by PhpStorm.
 * User: floor12
 * Date: 18.09.2018
 * Time: 20:34
 */

namespace floor12\backup\controllers;

use ErrorException;
use floor12\backup\Exceptions\ConfigurationNotFoundException;
use floor12\backup\Exceptions\FileNotFoundException;
use floor12\backup\Exceptions\ModuleNotConfiguredException;
use floor12\backup\logic\BackupCreate;
use floor12\backup\logic\BackupImporter;
use floor12\backup\logic\BackupRestore;
use floor12\backup\models\Backup;
use floor12\backup\models\BackupType;
use Yii;
use yii\base\InvalidConfigException;
use yii\console\Controller;
use yii\helpers\Console;

/**
 * Floor12 backup module console controller
 *
 * Class ConsoleController
 * @package floor12\backup\controllers
 */
class ConsoleController extends Controller
{
    /**
     * Pass config_id to this command to create new backup.
     *
     * @param string $config_id
     * @throws InvalidConfigException
     */
    public function actionBackup(string $config_id)
    {
        Yii::createObject(BackupCreate::class, [$config_id])->run();
        $this->stdout('Backup created.' . PHP_EOL, Console::FG_GREEN);
    }

    /**
     * Pass existing backup ID to this command to restore from backup.
     *
     * @param string $backup_id
     * @throws ErrorException
     * @throws InvalidConfigException
     */
    public function actionRestore(string $backup_id)
    {
        $model = Backup::findOne((int)$backup_id);
        if (!$model)
            throw new ErrorException('Backup not found.');

        Yii::createObject(BackupRestore::class, [$model])->run();
        $this->stdout('Backup restored.' . PHP_EOL, Console::FG_GREEN);
    }

    /**
     * List all created backups.
     *
     * @throws ErrorException
     * @throws InvalidConfigException
     */
    public function actionIndex()
    {
        $models = Backup::find()->orderBy('id DESC')->all();

        if (empty($models))
            return $this->stderr('Backups not found.' . PHP_EOL, Console::FG_YELLOW);

        foreach ($models as $model)
            $this->stdout("{$model->id}: " . Yii::$app->formatter->asDatetime($model->date) . "\t\t{$model->config_id}\t\t" .
                BackupType::$list[$model->type] .
                PHP_EOL,
                $model->status ? Console::FG_GREEN : Console::FG_RED);
    }

    /**
     * @param string $config_id
     * @param string $absoluteFilePath
     * @throws ConfigurationNotFoundException
     * @throws FileNotFoundException
     * @throws ModuleNotConfiguredException
     */
    public function actionImport(string $config_id, string $absoluteFilePath)
    {
        $importer = new BackupImporter($config_id, $absoluteFilePath);
        if ($importer->import())
            $this->stdout("Backup imported: {$absoluteFilePath}\n", Console::FG_GREEN);
        else
            $this->stdout("Something went wrong.\n", Console::FG_RED);
    }
}
