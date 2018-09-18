<?php
/**
 * Created by PhpStorm.
 * User: floor12
 * Date: 18.09.2018
 * Time: 0:03
 */

namespace floor12\backup\models;


use yii\base\Model;
use yii\data\ActiveDataProvider;

class BackupFilter extends Model
{
    public $date;

    public function dataProvider()
    {
        return new ActiveDataProvider([
            'query' => Backup::find()
        ]);
    }
}