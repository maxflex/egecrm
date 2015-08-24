<?php

class Task extends Model
{
	public static $mysql_table	= "tasks";
	
	// путь хранения электронных версий договоров
	const UPLOAD_DIR = "files/task/";

	public $_serialized = ["files"];
	
	public function __construct($array)
	{
		parent::__construct($array);
		
		if (!$this->files) {
			$this->files = [];
		}
	}
	
	public function beforeSave()
	{
		foreach ($this->files as $file) {
			unset($file['$$hashKey']);
		}
		
		if ($this->isNewRecord) {
			$this->date_created = now();
			$this->id_user = User::fromSession()->id;
		} else {
			if ($this->id_status == TaskStatuses::NEWR) {
			//	Email::send('makcyxa-k@yandex.ru', 'Новая задача', $this->html, []);
			}
		}
	}
	
	public static function countNew()
	{
		return self::count([
			"condition" => "id_status = ". TaskStatuses::NEWR . " AND html!=''"
		]);
	}
}