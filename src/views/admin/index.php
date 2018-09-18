<?php
/**
 * Created by PhpStorm.
 * User: floor12
 * Date: 17.09.2018
 * Time: 23:28
 *
 * @var $this View
 * @var $model BackupFilter
 */


use floor12\backup\models\Backup;
use floor12\backup\models\BackupFilter;
use floor12\backup\models\BackupStatus;
use rmrevin\yii\fontawesome\FontAwesome;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\web\View;
use yii\widgets\Pjax;

?>


    <h1><?= Yii::t('app.f12.backup', 'Backups') ?></h1>


<?php
Pjax::begin(['id' => 'items']);

echo GridView::widget([
    'layout' => "{items}\n{pager}\n{summary}",
    'tableOptions' => ['class' => 'table table-striped'],
    'dataProvider' => $model->dataProvider(),
    'columns' => [
        'id',
        'date:datetime',
        'config_name',
        'filename',
        [
            'attribute' => 'status',
            'content' => function (Backup $model) {
                return BackupStatus::getLabel($model->status);
            }
        ],
        'size:size',
        [
            'contentOptions' => ['class' => 'text-right'],
            'content' => function (Backup $model) {
                $html = Html::a(FontAwesome::icon('play'), null, [
                    'class' => 'btn btn-default btn-sm',
                    'title' => Yii::t('app.f12.backup', 'Restore')
                ]);
                $html .= " " . Html::a(FontAwesome::icon('cloud-download'), null, [
                        'class' => 'btn btn-default btn-sm',
                        'title' => Yii::t('app.f12.backup', 'Download')
                    ]);
                $html .= " " . Html::a(FontAwesome::icon('trash'), null, [
                        'class' => 'btn btn-default btn-sm',
                        'title' => Yii::t('app.f12.backup', 'Delete')
                    ]);

                return $html;
            }
        ]
    ]
]);

Pjax::end();
