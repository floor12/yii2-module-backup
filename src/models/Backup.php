<?php
/**
 * Created by PhpStorm.
 * User: floor12
 * Date: 17.09.2018
 * Time: 23:10
 */

namespace floor12\backup\models;


use Yii;
use yii\db\ActiveRecord;

/**
 * Class Backup
 * @package floor12\backup\models
 * @property integer $id
 * @property integer $status
 * @property integer $type
 * @property integer $size
 * @property integer $config_id
 * @property string $config_name
 * @property string $date
 * @property string $filename
 * @property string $fullPath
 */
class Backup extends ActiveRecord
{

    const EXT_TGZ = '.gz';

    public static function getDb()
    {
        return Yii::$app->getModule('backup')->connection;
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'backup';
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'date' => Yii::t('app.f12.backup', 'Date'),
            'config_id' => Yii::t('app.f12.backup', 'Config'),
            'type' => Yii::t('app.f12.backup', 'Backup type'),
            'filename' => Yii::t('app.f12.backup', 'File name'),
            'config_name' => Yii::t('app.f12.backup', 'Config name'),
            'size' => Yii::t('app.f12.backup', 'Size'),
            'status' => Yii::t('app.f12.backup', 'Status'),
        ];
    }

    /** Гененрируем имя полноый путь к архиву на основании конфига и емения архива.
     * @return string|void
     */
    public function getFullPath()
    {
        if (!$this->filename)
            return;
        return Yii::$app->getModule('backup')->backupRootPath . DIRECTORY_SEPARATOR . $this->filename;
    }

    /**
     * Обновляем размер архива после его создания.
     * @return void
     */
    public function updateFileSize()
    {
        $this->size = filesize($this->getFullPath());
    }

    public function afterDelete()
    {
        @unlink($this->getFullPath());
        parent::afterDelete();
    }
}