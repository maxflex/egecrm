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

	public static function saveData($id_admin, $data)
	{
		AdminIp::deleteAll(['condition' => "id_admin = {$id_admin}"]);

		foreach($data as $ip) {
			AdminIp::add([
				'id_admin'       => $id_admin,
				'ip_from'        => $ip['ip_from'],
				'ip_to'          => $ip['ip_to'],
				'confirm_by_sms' => $ip['confirm_by_sms'] ? 1 : 0,
			]);
		}
	}
}
