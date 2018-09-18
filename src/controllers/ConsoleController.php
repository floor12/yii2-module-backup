<?php
/**
 * Created by PhpStorm.
 * User: floor12
 * Date: 18.09.2018
 * Time: 20:34
 */

namespace floor12\backup\controllers;

use floor12\backup\logic\BackupCreate;
use Yii;
use yii\console\Controller;

class ConsoleController extends Controller
{
    public function actionBackup(string $config_id)
    {
        Yii::createObject(BackupCreate::class, [$config_id])->run();
    }
}