<?php

class Task extends Model
{
	public static $mysql_table	= "tasks";
    protected     $loggable = false;

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
		}
	}

	public static function countNew()
	{
        $taskStatusesToShow = implode(',',[TaskStatuses::NEWR]);

        return self::count([
            "condition" => "id_status IN (". $taskStatusesToShow .") AND html!=''"
		]);
	}
}
