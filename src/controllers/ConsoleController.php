<?php
/**
 * Created by PhpStorm.
 * User: floor12
 * Date: 18.09.2018
 * Time: 20:34
 */

namespace floor12\backup\controllers;

use floor12\backup\logic\BackupCreate;
use floor12\backup\logic\BackupRestore;
use floor12\backup\models\Backup;
use Yii;
use yii\console\Controller;

class ConsoleController extends Controller
{
    public function actionBackup(string $config_id)
    {
        Yii::createObject(BackupCreate::class, [$config_id])->run();
    }

    public function actionRestore(string $backup_id)
    {
        $model = Backup::findOne((int)$backup_id);
        if (!$model)
            throw new \ErrorException('Backup not found.');

        Yii::createObject(BackupRestore::class, [$model])->run();
    }
}