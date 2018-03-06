<?php

class Job extends Model
{
    public static $mysql_table = "delayed_jobs";
    protected $_json = ['params'];

    public static function dispatch($class, $params, $delay_in_minutes)
    {
        // сначала удаляем похожие
        self::deleteAll([
            'condition' => "class='{$class}' AND params='" . json_encode($params, JSON_UNESCAPED_UNICODE) . "'"
        ]);

        self::add([
            'class'     => $class,
            'params'    => $params,
            'run_at'    => (new DateTime)->modify("+{$delay_in_minutes} minutes")->format('Y-m-d H:i:00')
        ]);
    }
}
