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


use floor12\backup\assets\BackupAdminAsset;
use floor12\backup\models\Backup;
use floor12\backup\models\BackupFilter;
use floor12\backup\models\BackupStatus;
use floor12\backup\models\BackupType;
use floor12\editmodal\EditModalHelper;
use rmrevin\yii\fontawesome\FontAwesome;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\web\View;
use yii\widgets\Pjax;

$this->title = Yii::t('app.f12.backup', 'Backups');

BackupAdminAsset::register($this);

$restoreConfirmText = Yii::t('app.f12.backup', 'Are your realy want to restor thie backup?');
$restoreSuccessText = Yii::t('app.f12.backup', 'Backup was successful restored.');
$backupeSuccessText = Yii::t('app.f12.backup', 'Backup was successful created.');
$this->registerJs("restoreConfirmText='{$restoreConfirmText}'", View::POS_READY, 'restoreConfirmText');
$this->registerJs("restoreSuccessText='{$restoreSuccessText}'", View::POS_READY, 'restoreSuccessText');
$this->registerJs("backupeSuccessText='{$backupeSuccessText}'", View::POS_READY, 'backupeSuccessText');

?>

    <div class="btn-group pull-right">
        <button type="button" class="btn btn-sm btn-primary dropdown-toggle" data-toggle="dropdown"
                aria-expanded="false">
            <?= FontAwesome::icon('plus') ?>
            Создать бекап <span class="caret"></span>
        </button>
        <ul class="dropdown-menu" role="menu">
            <?php foreach ($configs as $config) { ?>
                <li>
                    <a onclick="backup.create('<?= $config['id'] ?>')">
                        <?= FontAwesome::i($config['type'] == BackupType::DB ? 'database' : 'file') ?>
                        <?= $config['title'] ?>
                    </a>
                </li>
            <?php } ?>
        </ul>
    </div>


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
        [
            'content' => function (Backup $model) {
                $html = ' ';
                if (file_exists($model->getFullPath()))
                    $html .= FontAwesome::icon('check', ['title' => 'Файл найден', 'style' => 'color:#38b704;']);
                else
                    $html .= FontAwesome::icon('ban', ['title' => 'Файл не найден', 'style' => 'color:#eca70b;']);
                $html .= ' ';

                if (is_writable($model->getFullPath()))
                    $html .= FontAwesome::icon('check', ['title' => 'Права на запись есть', 'style' => 'color:#38b704;']);
                else
                    $html .= FontAwesome::icon('ban', ['title' => 'Нет прав на запись', 'style' => 'color:#eca70b;']);


                return $html;
            }
        ],
        'size:size',
        [
            'contentOptions' => ['class' => 'text-right'],
            'content' => function (Backup $model) {
                $html = Html::a(FontAwesome::icon('play'), null, [
                    'class' => 'btn btn-default btn-sm',
                    'title' => Yii::t('app.f12.backup', 'Restore'),
                    'onclick' => "backup.restore({$model->id})"
                ]);
                $html .= " " . Html::a(FontAwesome::icon('download'),
                        ['/backup/admin/download', 'id' => $model->id], [
                            'class' => 'btn btn-default btn-sm',
                            'title' => Yii::t('app.f12.backup', 'Download'),
                            'target' => '_blank',
                            'data-pjax' => '0'
                        ]);
                $html .= " " . Html::a(FontAwesome::icon('trash'), null, [
                        'class' => 'btn btn-default btn-sm',
                        'title' => Yii::t('app.f12.backup', 'Delete'),
                        'onclick' => EditModalHelper::deleteItem('/backup/admin/delete', $model->id)
                    ]);

                return $html;
            }
        ]
    ]
]);

Pjax::end();
