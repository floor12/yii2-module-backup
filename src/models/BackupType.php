<?php
/**
 * Created by PhpStorm.
 * User: floor12
 * Date: 17.09.2018
 * Time: 23:48
 */

namespace floor12\backup\models;


use yii2mod\enum\helpers\BaseEnum;

class BackupType extends BaseEnum
{
    const DB = 0;
    const FILES = 1;

    public static $list = [
        self::DB => 'Database',
        self::FILES => 'Files',
    ];

}