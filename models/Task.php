<?php

class Task extends Model
{
	public static $mysql_table	= "tasks";
	
	// путь хранения электронных версий договоров
	const UPLOAD_DIR = "files/task/";

	const PLACE = 'TASK';
	
	public $_serialized = ["files"];
	
	public function __construct($array)
	{
		parent::__construct($array);
		
		if (!$this->files) {
			$this->files = [];
		}
		
		if (!$this->isNewRecord) {
			$this->Comments = Comment::getByPlace(self::PLACE, $this->id);
			$this->User = User::findById($this->id_user);
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
	
	public static function countNew($type = false)
	{
        $taskStatusesToShow = implode(',',[TaskStatuses::NEWR, TaskStatuses::NEWR_FOR_MAX, TaskStatuses::NEWR_FOR_SHAM]);

        return self::count([
            "condition" => "id_status IN (". $taskStatusesToShow .") AND html!=''" . ($type !== false ? " AND type=$type" : "")
		]);
	}
}