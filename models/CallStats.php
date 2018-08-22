<?php

class CallStats extends Model
{
	public static $mysql_table	= "call_stats";

	public static function sum($date_start, $date_end = null)
	{
		// Получаем количество из условия
		$result = static::dbConnection()->query(
			"SELECT sum(`count`) as s FROM `".static::$mysql_table."`
			 WHERE " . ($date_end ?
			 	"`date` BETWEEN '{$date_start}' AND '{$date_end}'" :
				"`date` = '{$date_start}'")
		);
		$sum = $result->fetch_object()->s;

		// добавляем статистику за сегодня
		if (($date_end && ($date_start <= now(true) && $date_end >= now(true))) || $date_start == now(true)) {
			$sum += self::todaySum();
		}

		return $sum;
	}

	/**
	 * Сколько было входящих звонков сегодня
	 * (типа в режиме реального времени)
	 */
	private static function todaySum()
	{
		$today = now(true);
		$result = dbEgerep()->query("SELECT count(*) as `cnt` from
                (select 1
                from mango m
                where DATE(FROM_UNIXTIME(m.`start`)) = '{$today}'
                	and m.line_number in ('74956468592', '74954886885', '74954886882')
                	and m.from_extension = 0
                	and m.answer > 0
                	and (m.finish - m.answer) > 15
                	and not exists(
                		select 1 from mango m2
                		where m2.from_number = m.from_number
                			and DATE(FROM_UNIXTIME(m2.start)) between
                				DATE_SUB(DATE(FROM_UNIXTIME(m.`start`)), INTERVAL 7 DAY) AND
                				DATE_SUB(DATE(FROM_UNIXTIME(m.`start`)), INTERVAL 1 DAY)
                	)
                group by from_number) x"
            );
		return $result->fetch_object()->cnt;
	}
}
