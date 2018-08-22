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
		return $result->fetch_object()->s;
	}
}
