<?php
/**
 * Статусы заявки
 */
class TestStates extends Factory {

    # Список
    const NOT_STARTED   = 'not_started';
    const IN_PROGRESS   = 'in_progress';
    const FINISHED      = 'finished';

    # Все
    static $all = [
        self::NOT_STARTED	=> "не приступали",
        self::IN_PROGRESS	=> "в процессе",
        self::FINISHED		=> "проденные",
    ];

    public static function json()
    {
        return json_encode(static::$all);
    }
}
