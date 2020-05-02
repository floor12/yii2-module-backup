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
use floor12\backup\models\ImportForm;
use Throwable;
use Yii;
use yii\base\InvalidConfigException;
use yii\db\StaleObjectException;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\UploadedFile;

class AdminController extends Controller
{

    /**
     * @var Backup
     */
    protected $model;

    /**
     * @inheritDoc
     * @return array
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => [Yii::$app->getModule('backup')->administratorRoleName],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'index' => ['get'],
                    'delete' => ['delete'],
                    'backup' => ['post'],
                    'restore' => ['post'],
                ],
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->layout = Yii::$app->getModule('backup')->adminLayout;
        parent::init();
    }

    /**
     * @return string
     */
    public function actionIndex()
    {
        $model = new BackupFilter();
        return $this->render('index', [
            'model' => $model,
            'configs' => Yii::$app->getModule('backup')->configs ?: []
        ]);
    }

    /**
     * @return string
     * @throws NotFoundHttpException
     * @throws Throwable
     * @throws StaleObjectException
     */
    public function actionDelete()
    {
        $this->getBackup((int)Yii::$app->request->post('id'));
        $this->model->delete();
        $this->model->delete();
    }

    /**
     * @throws InvalidConfigException
     * @throws NotFoundHttpException
     * @throws StaleObjectException
     * @throws Throwable
     */
    public function actionBackup()
    {
        $config_id = Yii::$app->request->post('config_id');
        Yii::createObject(BackupCreate::class, [$config_id])->run();
    }

    /**
     * @throws InvalidConfigException
     * @throws NotFoundHttpException
     */
    public function actionRestore()
    {
        $this->getBackup((int)Yii::$app->request->post('id'));
        Yii::createObject(BackupRestore::class, [$this->model])->run();
    }

    /**
     * @param $id
     * @throws NotFoundHttpException
     */
    public function actionDownload($id)
    {
        $this->getBackup((int)$id);
        Yii::$app->response->sendFile($this->model->getFullPath());
    }

    /**
     * @param $config_id
     * @return string
     */
    public function actionImport()
    {
        $model = new ImportForm();
        $model->load(Yii::$app->request->post());
        $model->file = UploadedFile::getInstance($model, 'file');
        if (!$model->import())
            throw new BadRequestHttpException(print_r($model->errors, true));
    }

    /**
     * @param int $id
     * @throws NotFoundHttpException
     */
    protected function getBackup(int $id)
    {
        $this->model = Backup::findOne($id);
        if (!$this->model)
            throw new NotFoundHttpException(Yii::t('app.f12.backup', 'Backup is not found.'));
    }

}
