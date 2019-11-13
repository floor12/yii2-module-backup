<?php
/**
 * Created by PhpStorm.
 * User: floor12
 * Date: 17.09.2018
 * Time: 23:48
 */

namespace floor12\backup\models;


use yii2mod\enum\helpers\BaseEnum;

class BackupStatus extends BaseEnum
{
    const IN_PROCESS = 0;
    const DONE = 1;
    const ERROR = 2;
    /**
     * @var string
     */
    public static $messageCategory = 'app.f12.backup';
    /**
     * @var array
     */
    public static $list = [
        self::IN_PROCESS => 'In process',
        self::DONE => 'Done',
        self::ERROR => 'Error',
    ];

}