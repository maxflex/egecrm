<?php

class Task extends Model
{
	public static $mysql_table	= "tasks";
	
	public function __construct($array)
	{
		parent::__construct($array);
	}
	
	public function beforeSave()
	{
		if ($this->isNewRecord) {
			$this->date_created = now();
			$this->id_user = User::fromSession()->id;
		}
	}
	
	public static function countNew()
	{
		return self::count([
			"condition" => "id_status = ". TaskStatuses::NEWR . " AND html!=''"
		]);
	}
}