<?php
/**
 * Created by PhpStorm.
 * User: floor12
 * Date: 17.09.2018
 * Time: 23:07
 */

namespace floor12\backup\controllers;

use floor12\backup\logic\BackupCreate;
use floor12\backup\logic\BackupRestore;
use floor12\backup\models\Backup;
use floor12\backup\models\BackupFilter;
use floor12\editmodal\DeleteAction;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

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
                    'backup' => ['post'],
                    'restore' => ['post'],
                ],
            ],
        ];
    }

    public function actionIndex()
    {
        $model = new BackupFilter();
        return $this->render('index', [
            'model' => $model,
            'configs' => Yii::$app->getModule('backup')->configs ?: []
        ]);
    }

    public function actions()
    {
        return [
            'delete' => [
                'class' => DeleteAction::class,
                'model' => Backup::class,
                'message' => Yii::t('app.f12.backup', 'Backup is deleted')
            ]
        ];
    }

    public function actionBackup()
    {
        $config_id = '';
        foreach (Yii::$app->getModule('backup')->configs as $config)
            if ($config['id'] == Yii::$app->request->post('config_id'))
                $config_id = $config['id'];

        if (!$config_id)
            throw new NotFoundHttpException('Backup config not found');

        Yii::createObject(BackupCreate::class, [$config_id])->run();
    }

    public function actionRestore()
    {
        $model = Backup::findOne(Yii::$app->request->post('backup_id'));
        if (!$model)
            throw new NotFoundHttpException('Backup not found.');

        Yii::createObject(BackupRestore::class, [$model])->run();
    }

    public function actionDownload($id)
    {
        $model = Backup::findOne((int)$id);
        if (!$model)
            throw new NotFoundHttpException('Backup is not found');

        Yii::$app->response->sendFile($model->getFullPath());
    }
}