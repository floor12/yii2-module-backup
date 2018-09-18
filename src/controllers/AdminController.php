<?php
/**
 * Created by PhpStorm.
 * User: floor12
 * Date: 17.09.2018
 * Time: 23:07
 */

namespace floor12\backup\controllers;

use floor12\backup\models\Backup;
use floor12\backup\models\BackupFilter;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;

class AdminController extends Controller
{
    public $defaultAction = 'index';

    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => [Yii::$app->getModule('backup')->editRole],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['delete'],
                ],
            ],
        ];
    }

    public function actionIndex()
    {
        $model = new BackupFilter();
        return $this->render('index', ['model' => $model]);
    }

    public function actionCreate()
    {
        $model = new Backup();
        $model->type = 1;
        $model->size = 1133312;
        $model->config_name = 'Основная база';
        $model->config_id = 1;
        $model->filename = '234f2fsl2k.tgz';
        $model->date = date("Y-m-d");
        var_dump($model->save());
    }
}