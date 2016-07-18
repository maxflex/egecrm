<?php
	class Test extends Model
	{
		/*====================================== ПЕРЕМЕННЫЕ И КОНСТАНТЫ ======================================*/

		public static $mysql_table	= "tests";
		
		public function __construct($array)
		{
			parent::__construct($array);
			
			if ($this->isNewRecord) {
				$this->Problems[] = TestProblem::getEmpty();
			} else {
				$this->Problems = TestProblem::findByTest($this->id);
				$this->name = !empty($this->name) ? $this->name : 'Тест №' . $this->id; 
			}
		}
		
		static function getMaxScore($id_test)
		{
			return dbConnection()->query("SELECT SUM(score) AS s FROM test_problems WHERE id_test = {$id_test}")->fetch_object()->s;
		}
		
		function beforeSave()
		{
			if ($this->isNewRecord) {
				$this->id_user = User::fromSession()->id;
				$this->created_at = now();
			}
		}
	}
	
	class TestProblem extends Model
	{
		/*====================================== ПЕРЕМЕННЫЕ И КОНСТАНТЫ ======================================*/

		public static $mysql_table	= "test_problems";
		
		protected $_serialized = ['answers'];
		
		public function __construct($array)
		{
			parent::__construct($array);
		}
		
		public function findByTest($id_test) {
			return TestProblem::findAll([
				'condition' => "id_test = {$id_test}"
			]);
		}
		
		public function getEmpty()
		{
			return (object)[
				'problem' => 'текст задания...',
				'answers' => ['текст ответа...'],
			];
		}
	}
	
	class TestStudent extends Model
	{
		public static $mysql_table	= "test_students";
		
				
		public function __construct($array)
		{
			parent::__construct($array);
			
			if (! $this->isNewRecord) {
				$this->isFinished = $this->finished();
				$this->inProgress = $this->inProgress();
				$this->final_score = $this->finalScoreString();
				$this->name = Test::findById($this->id_test)->name; 
			}
		}
		
		public static function countNeeded($id_test) {
			// @todo: брошеные тесты здесь не засчитываются
			return TestStudent::count([
				'condition' => 'id_student=' . User::fromSession()->id_entity . ' AND date_finish="' . EMPTY_DATETIME .'"'
			]);
		}
		
		public static function getByStudentId($id_student) {
			$TestStudents = TestStudent::findAll([
				'condition' => "id_student = {$id_student}"
			]);
			
			return $TestStudents ? $TestStudents : [];
		}
		
		public static function get($id_student, $id_test) {
			return TestStudent::find([
				'condition' => "id_student = {$id_student} AND id_test={$id_test}"
			]);
		}
		
		public static function findByTest($id_test) {
			$TestStudents = TestStudent::findAll([
				'condition' => "id_test = {$id_test}"
			]);
			
			return $TestStudents ? $TestStudents : [];
		}
		
		public function start()
		{
			$this->date_start = now();
			$this->save('date_start');
		}
		
		static function getAnswers($id)
		{
			return (object)json_decode($_COOKIE["answers{$id}"]);
		}
		
		function finalScoreString()
		{
			return $this->score . "/" . Test::getMaxScore($this->id_test);
		}
		
		public function finish()
		{
			$score = 0;
			$answers = TestStudent::getAnswers($this->id_test);
			
			foreach($answers as $id_problem => $answer) {
				$Problem = TestProblem::findById($id_problem);
				if ($Problem->correct_answer == $answer) {
					$score += $Problem->score;
				}
			}
			
			$this->score = $score;
			$this->date_finish = now();
			$this->save();
		}
		
		public function inProgress()
		{
			return ($this->date_start != EMPTY_DATETIME && !$this->_finished());
		}
		
		public function finished()
		{
			return ($this->date_start != EMPTY_DATETIME && $this->_finished());
		}
		
		private function _finished() {
			// если неактивен в течение 2х часов, то завершен
			return ($this->date_finish != EMPTY_DATETIME || (time() - strtotime($this->date_start)) > (2 * 3600));
		}
	}