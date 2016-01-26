<?php

class PrintTask extends Model
{
	public static $mysql_table	= "print_tasks";
	
	// путь хранения
	const UPLOAD_DIR = "files/print/";
	
	public $_serialized = ["files"];
	
	public function __construct($array)
	{
		parent::__construct($array);
		
		if (!$this->files) {
			$this->files = [];
		}
		
		if (!$this->isNewRecord) {
			$this->Lesson 	= GroupSchedule::findById($this->id_lesson);
			$this->Teacher 	= Teacher::findById($this->id_teacher);
		}
	}
	
	public static function countNew()
	{
		return self::count([
			"condition" => "id_status=0"
		]);
	}
	
	public function beforeSave()
	{
		foreach ($this->files as $file) {
			unset($file['$$hashKey']);
		}
		
		if ($this->isNewRecord) {
			$this->date_created = date("Y-m-d H:i:s");
			$this->id_teacher = User::fromSession()->id_entity;
		}
	}
}