<?php
	const EMPTY_DATETIME = '0000-00-00 00:00:00';
	class Test extends Model
	{
		const PER_PAGE		= 30;

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
				$this->max_score = Test::getMaxScore($this->id);
			}
			
			if (! $this->intro) {
				$this->intro = 'вступительное описание';
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

			$this->problem = htmlspecialchars_decode($this->problem);
			if (!empty($this->answers)) {
				foreach ($this->answers as &$answer) {
					$answer = htmlspecialchars_decode($answer);
				}
			}
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

		public function beforeSave() {
			$this->problem = htmlspecialchars($this->problem);
			if (count($this->answers)) {
				foreach($this->answers as &$answer) {
					$answer = htmlspecialchars($answer);
				}
			}
		}
	}
	
	class TestStudent extends Model
	{
		const PER_PAGE		= 30;

		public static $mysql_table	= "test_students";
		
		protected $_json = ['answers'];
				
		public function __construct($array)
		{
			parent::__construct($array);
			
			if (! $this->isNewRecord) {
				$this->Test = Test::findById($this->id_test);
				$this->notStarted = $this->notStarted();
				$this->isFinished = $this->finished();
				$this->inProgress = $this->inProgress();
				$this->final_score = $this->finalScoreString();
			//	$this->name = Test::findById($this->id_test)->name;
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
		
		function finalScoreString()
		{
			return round($this->score * 100 / Test::getMaxScore($this->id_test)) . "/100";
		}
		
		public function finish()
		{
			$score = 0;
			foreach($this->answers as $id_problem => $answer) {
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
			return ($this->date_finish != EMPTY_DATETIME || (time() - strtotime($this->date_start)) > (60 * $this->Test->minutes));
		}

		private function notStarted() {
			return $this->date_finish == EMPTY_DATETIME;
		}
		
		public static function countFinished()
		{
			$result = self::dbConnection()->query("select count(*) as cnt from test_students ts join tests t on ts.id_test = t.id and ".self::$not_empty_date_condition.' and '.self::$finished_condition);
			$result = $result->fetch_assoc();
			return $result['cnt'];
		}

		private static function finishedCondition()
		{
			return self::$not_empty_date_condition .' and '.self::$finished_condition;
		}

		public static function countInProcess()
		{
			$result = self::dbConnection()->query("select count(*) as cnt from test_students ts join tests t on ts.id_test = t.id and ".self::$not_empty_date_condition.' and (not '.self::$finished_condition.')');
			$result = $result->fetch_assoc();
			return $result['cnt'];
		}

		private static function inProcessCondition()
		{
			return self::$not_empty_date_condition.' and (not '.self::$finished_condition.')';
		}

		public static function countNotStarted()
		{
			return self::count(['condition' => self::$empty_date_condition]);
		}

		static $empty_date_condition = "date_start = '0000-00-00 00:00:00'";
		static $not_empty_date_condition = "date_start <> '0000-00-00 00:00:00'";
		static $finished_condition = "(ts.date_finish <> '0000-00-00 00:00:00' or (UNIX_TIMESTAMP() - UNIX_TIMESTAMP(ts.date_start) > (60 * t.minutes)))";

		public static function filter($filter) {
			$condition = '1';
			switch ($filter) {
				case 'in_process':
					$condition = self::inProcessCondition();
					break;
				case 'finished':
					$condition = self::finishedCondition();
					break;
				case 'not_started':
					$condition = self::$empty_date_condition;
					break;
				default:
					$condition = '1';
			}
			return $condition;
		}
	}