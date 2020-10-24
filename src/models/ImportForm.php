<?php


namespace floor12\backup\models;


use floor12\backup\logic\BackupImporter;
use yii\base\Model;
use yii\web\UploadedFile;

class ImportForm extends Model
{
    /** @var integer */
    public $config_id;
    /** @var UploadedFile */
    public $file;

    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            [['config_id', 'file'], 'required'],
            ['config_id', 'integer'],
            ['file', 'file', 'extensions' => ['dump', 'zip'], 'maxFiles' => 1]
        ];
    }

    /**
     * @return bool
     * @throws \floor12\backup\Exceptions\ConfigurationNotFoundException
     * @throws \floor12\backup\Exceptions\FileNotFoundException
     * @throws \floor12\backup\Exceptions\ModuleNotConfiguredException
     */
    public function import(): bool
    {
        $importer = new BackupImporter($this->config_id, $this->file->tempName, $this->file->name);
        return $importer->import();
    }
}
