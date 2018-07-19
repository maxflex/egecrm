<?php

class TeacherAdditionalPayment extends Model
{
	public static $mysql_table	= "teacher_additional_payments";

	public function __construct($array)
	{
		parent::__construct($array);
		if (! $this->isNewRecord) {
			$this->date = dateFormat($this->date, true);
			$this->user_login = User::findById($this->id_user)->login;
		}
	}

	public static function get($id_teacher)
	{
		return self::findAll([
			'condition' => "id_teacher={$id_teacher}",
			'order' => '`date` desc'
		]);
	}

	public function beforeSave()
	{
		if ($this->isNewRecord) {
			$this->id_user = User::id();
		}
		$this->date = fromDotDate($this->date);
	}
}