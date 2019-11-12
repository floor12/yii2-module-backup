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
use Yii;
use yii\filters\ContentNegotiator;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class ApiController extends Controller
{

    const HEADER_NAME = 'Backup-Auth-Token';
    /**
     * @var Backup
     */
    protected $model;
    /**
     * @var array
     */
    protected $successResonse = [
        'result' => 'success'
    ];

    /**
     * @inheritDoc
     * @return array
     */
    public function behaviors()
    {
        return [
            'contentNegotiator' => [
                'class' => ContentNegotiator::class,
                'formats' => [
                    'application/json' => Response::FORMAT_JSON,
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'index' => ['get'],
                    'delete' => ['delete'],
                    'backup' => ['post'],
                    'restore' => ['post'],
                    'download' => ['get'],
                ],
            ],
        ];
    }

    /**
     * @param $action
     * @return bool
     * @throws ForbiddenHttpException
     * @throws \yii\web\BadRequestHttpException
     */
    public function beforeAction($action)
    {
        $this->checkPermission();
        $this->enableCsrfValidation = false;
        return parent::beforeAction($action);
    }


    /**
     * @return string
     * @throws ForbiddenHttpException
     */
    public function actionIndex()
    {
        $model = new BackupFilter();
        return $model->dataProvider()->getModels();
    }

    /**
     * @return string
     */
    public function actionDelete($id)
    {
        $this->getBackup((int)$id);
        $this->model->delete();
        return $this->successResonse;
    }

    /**
     * @throws NotFoundHttpException
     * @throws InvalidConfigException
     */
    public function actionBackup($config_id)
    {
        if (!Yii::$app->getModule('backup')->checkConfig($config_id))
            throw new NotFoundHttpException(Yii::t('app.f12.backup', 'Backup config is not found.'));

        Yii::createObject(BackupCreate::class, [$config_id])->run();

        return $this->successResonse;
    }

    /**
     * @throws NotFoundHttpException
     * @throws \ErrorException
     * @throws InvalidConfigException
     */
    public function actionRestore($id)
    {
        $this->getBackup((int)$id);
        Yii::createObject(BackupRestore::class, [$this->model])->run();
        return $this->successResonse;
    }

    /**
     * @param $id
     * @throws NotFoundHttpException
     */
    public function actionGet($id)
    {
        $this->getBackup((int)$id);
        Yii::$app->response->sendFile($this->model->getFullPath());
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

    /**
     * @return bool
     * @throws ForbiddenHttpException
     */
    protected function checkPermission()
    {
        $headers = Yii::$app->request->getHeaders();
        $authTokens = Yii::$app->getModule('backup')->authTokens;
        if (!empty($headers[self::HEADER_NAME]) && in_array($headers[self::HEADER_NAME], $authTokens))
            return true;
        throw new ForbiddenHttpException();
    }

}