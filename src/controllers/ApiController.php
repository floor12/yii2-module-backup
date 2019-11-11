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
use yii\filters\ContentNegotiator;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\Response;

class ApiController extends Controller
{

    const HEADER_NAME = 'Backup-Auth-Token';
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
                ],
            ],
        ];
    }

    /**
     * @return string
     */
    public function actionIndex()
    {
        $this->checkPermission();
        $model = new BackupFilter();
        return $model->dataProvider()->getModels();
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