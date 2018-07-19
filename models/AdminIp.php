<?php

class AdminIp extends Model
{
	public static $mysql_table	= "admin_ips";

	public static function getAll($id_admin)
	{
		return self::findAll([
			'condition' => "id_admin={$id_admin}"
		]);
	}
}
