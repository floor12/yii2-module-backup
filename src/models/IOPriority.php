<?php
/**
 * Created by PhpStorm.
 * User: floor12
 * Date: 17.09.2018
 * Time: 23:48
 */

namespace floor12\backup\models;


use yii2mod\enum\helpers\BaseEnum;

class IOPriority extends BaseEnum
{
    const NONE = 0;
    const REALTIME = 1;
    const IDLE = 3;
    /**
     * @var string
     */
    public static $messageCategory = 'app.f12.backup';
    /**
     * @var array
     */
    public static $list = [
        self::NONE => 'None',
        self::REALTIME => 'Realtime',
        self::IDLE => 'Idle',
    ];

}
