<?php

class Testing extends Model
{
	public static $mysql_table	= "testing";
	
	protected $_inline_data = ["subjects_9", "subjects_11"];
	
	const PLACE = 'TESTING';
	
	public function __construct($array)
	{
		parent::__construct($array);
		
		if (!$this->isNewRecord) {
			if ($this->cabinet) {
				$this->Cabinet = Cabinet::findById($this->cabinet);
			}
			
			$this->Students = TestingStudent::getByTestingId($this->id);
			$this->Comments = Comment::getByPlace(self::PLACE, $this->id);
			
			Testing::_convertSubjects($this->subjects_9);
			Testing::_convertSubjects($this->subjects_11);
			
			$this->getTestCount();
		}
	}
	
	
	/**
	 * Получить кол-во доступных тестов.
	 * 
	 */
	public function getTestCount()
	{
		$this->total_tests_available = 0;
		// получить сколько предметов подходит под временной диапазон
		if ($this->start_time && $this->end_time) {
			$diff = (strtotime($this->end_time) - strtotime($this->start_time)) / 60; // разница в минутах между началом и концом – интервал
			
			foreach(Subjects::$minutes_9 as $minutes) {
				if ($diff >= $minutes) {
					$this->total_tests_available++;
				}
			}
			
			foreach(Subjects::$minutes_11 as $minutes) {
				if ($diff >= $minutes) {
					$this->total_tests_available++;
				}
			}
		}
		
		$this->total_tests_selected = count($this->subjects_9) + count($this->subjects_11);
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
        $TestingStudents = TestingStudent::findAll([
                               "condition" => "id_testing=$id_testing"
                           ]);

        foreach ($TestingStudents as &$ts) {
            $ts->group_ids = VisitJournal::getGroupIdsBySubject($ts->id_student, $ts->id_subject, $ts->grade);
        }

        return $TestingStudents;
	}
}