<?php
/**
 * Created by PhpStorm.
 * User: floor12
 * Date: 17.09.2018
 * Time: 23:28
 *
 * @var $this View
 * @var $model BackupFilter
 * @var $configs array
 */


use floor12\backup\assets\BackupAdminAsset;
use floor12\backup\assets\IconHelper;
use floor12\backup\models\Backup;
use floor12\backup\models\BackupFilter;
use floor12\backup\models\BackupStatus;
use floor12\backup\models\BackupType;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\web\View;
use yii\widgets\Pjax;

$this->title = Yii::t('app.f12.backup', 'Backups');

BackupAdminAsset::register($this);

$restoreConfirmText = Yii::t('app.f12.backup', 'Do you want to restore this backup?');
$restoreSuccessText = Yii::t('app.f12.backup', 'Backup was successful restored.');
$backupSuccessText = Yii::t('app.f12.backup', 'Backup was successful created.');
$importSuccessText = Yii::t('app.f12.backup', 'Backup imported.');
$deleteSuccessText = Yii::t('app.f12.backup', 'Backup is deleted.');
$this->registerJs("restoreConfirmText='{$restoreConfirmText}'", View::POS_READY, 'restoreConfirmText');
$this->registerJs("restoreSuccessText='{$restoreSuccessText}'", View::POS_READY, 'restoreSuccessText');
$this->registerJs("backupSuccessText='{$backupSuccessText}'", View::POS_READY, 'backupSuccessText');
$this->registerJs("deleteSuccessText='{$deleteSuccessText}'", View::POS_READY, 'deleteSuccessText');
$this->registerJs("importSuccessText='{$importSuccessText}'", View::POS_READY, 'importSuccessText');

?>
<div class="pull-right" style="display: flex;">
    <div class="btn-group">
        <button type="button" class="btn btn-sm btn-primary dropdown-toggle" data-toggle="dropdown"
                aria-expanded="false">
            <?= IconHelper::PLUS ?>
            <?= Yii::t('app.f12.backup', 'Run backup') ?> <span class="caret"></span>
        </button>
        <ul class="dropdown-menu" role="menu">
            <?php foreach ($configs as $config_id => $config) { ?>
                <li>
                    <a role="button" onclick="backup.create('<?= $config_id ?>')">
                        <?= $config['type'] == BackupType::DB ? IconHelper::DATABASE : IconHelper::FILE ?>
                        <?= $config['title'] ?>
                    </a>
                </li>
            <?php } ?>
        </ul>
    </div>

    <div class="btn-group">
        <button type="button" class="btn btn-sm btn-primary dropdown-toggle" data-toggle="dropdown"
                aria-expanded="false">
            <?= IconHelper::PLUS ?>
            <?= Yii::t('app.f12.backup', 'Import backup') ?> <span class="caret"></span>
        </button>
        <ul class="dropdown-menu" role="menu">
            <?php foreach ($configs as $config_id => $config) { ?>
                <li>
                    <a role="button" onclick="backup.openFileSelector('<?= $config_id ?>')">
                        <?= $config['type'] == BackupType::DB ? IconHelper::DATABASE : IconHelper::FILE ?>
                        <?= $config['title'] ?>
                    </a>
                </li>
            <?php } ?>
        </ul>
    </div>
</div>


<h1><?= Yii::t('app.f12.backup', 'Backups') ?></h1>

<div class="progress" id="backup-import-progress" style="display: none">
    <div class="progress-bar" role="progressbar" aria-valuenow="0"
         aria-valuemin="0" aria-valuemax="100" style="width: 0%">
        <span></span>
    </div>

</div>

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
                    $html .= Html::tag('span', IconHelper::CHECK, ['title' => 'Файл найден', 'style' => 'color:#38b704;']);
                else
                    $html .= Html::tag('span', IconHelper::EXCLAMATION, ['title' => 'Файл не найден', 'style' => 'color:#eca70b;']);

                $html .= ' ';

                if (is_writable($model->getFullPath()))
                    $html .= Html::tag('span', IconHelper::CHECK, ['title' => 'Права на запись найден', 'style' => 'color:#38b704;']);
                else
                    $html .= Html::tag('span', IconHelper::EXCLAMATION, ['title' => 'Нет прав на запись', 'style' => 'color:#eca70b;']);


                return $html;
            }
        ],
        'size:size',
        [
            'contentOptions' => ['class' => 'text-right'],
            'content' => function (Backup $model) {
                $html = Html::a(IconHelper::PLAY, null, [
                    'class' => 'btn btn-default btn-sm',
                    'title' => Yii::t('app.f12.backup', 'Restore'),
                    'onclick' => "backup.restore({$model->id})"
                ]);
                $html .= " " . Html::a(IconHelper::DOWNLOAD,
                        ['/backup/admin/download', 'id' => $model->id], [
                            'class' => 'btn btn-default btn-sm',
                            'title' => Yii::t('app.f12.backup', 'Download'),
                            'target' => '_blank',
                            'data-pjax' => '0'
                        ]);
                $html .= " " . Html::button(IconHelper::TRASH, [
                        'class' => 'btn btn-default btn-sm',
                        'title' => Yii::t('app.f12.backup', 'Delete'),
                        'onclick' => "backup.delete({$model->id})"
                    ]);

                return $html;
            }
        ]
    ]
]);

Pjax::end();

?>

<form id="backup-import-form" style="display:none;">
    <input id="backup-import-config_id" type="text" name="ImportForm[config_id]" maxlength="255">
    <input id="backup-import-file-selector" onchange="backup.importBackup()" type="file" name="ImportForm[file]"
           max="1">
</form>
