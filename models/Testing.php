<?php

class Testing extends Model
{
	public static $mysql_table	= "testing";
	
	protected $_inline_data = ["subjects_9", "subjects_11"];
	
	public function __construct($array)
	{
		parent::__construct($array);
		
		if (!$this->isNewRecord) {
			if ($this->cabinet) {
				$this->Cabinet = Cabinet::findById($this->cabinet);
			}
			
			$this->Students = TestingStudent::getByTestingId($this->id);
			
			Testing::_convertSubjects($this->subjects_9);
			Testing::_convertSubjects($this->subjects_11);
		}
	}
	
	public static function getAvailable($id_student)
	{
		$Student = Student::findById($id_student);
		$Contract = $Student->getLastContract();
		
		// доступно только для 9 и 11 классов
		if (!in_array($Contract->grade, [9, 11])) {
			return false;
		}
		
		$condition = [];
		$subject_ids = [];
		foreach ($Contract->subjects as $subject) {
			$subject_ids[] = $subject['id_subject'];
			$condition[] = "CONCAT(',', CONCAT(subjects_{$Contract->grade}, ',')) LIKE '%,{$subject['id_subject']},%'";
		}
		
		if (count($condition)) {
			return [
				"Testings" => Testing::findAll([
					"condition" => implode(" OR ", $condition) 
				]),
				"subject_ids" 	=> $subject_ids,
				"grade"			=> $Contract->grade,
			];
		} else {
			return false;
		}
	}
	
	public static function add($data)
	{
		$NewTesting = parent::add($data);
		
		Testing::_updateStudents($NewTesting->id, $data);
	}
	
	public static function updateById($id, $data)
	{
		parent::updateById($id, $data);
		
		Testing::_updateStudents($id, $data);
	}
	
	private static function _updateStudents($id_testing, $data)
	{
		TestingStudent::deleteAll([
			'condition' => "id_testing=$id_testing",
		]);
		
		foreach($data['Students'] as $Student) {
			TestingStudent::add([
				'id_testing' => $id_testing,
				'id_student' => $Student['id_student'],
				'id_subject' => $Student['id_subject'],
				'grade' 	 => $Student['grade'],
			]);
		}
	}
	
	private static function _convertSubjects(&$subjects)
	{
		if ($subjects[0] != '') {
			foreach ($subjects as $id_subject) {
				$subject_ids[$id_subject] = 1;	
			}
			$subjects = $subject_ids;
		} else {
			$subjects = null;
		}
	}
}

class TestingStudent extends Model
{
	public static $mysql_table = "testing_students";
	
	public function getByTestingId($id_testing)
	{
		return TestingStudent::findAll([
			"condition" => "id_testing=$id_testing"
		]);
	}
}