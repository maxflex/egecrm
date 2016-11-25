<?php
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
		
		function getLight($id)
		{
			$test = dbConnection()->query('SELECT id, name FROM tests WHERE id = ' . $id)->fetch_object();
			$test->name = !empty($test->name) ? $test->name : 'Тест №' . $test->id;
			$test->max_score = Test::getMaxScore($test->id);
			return $test;
		}
		
		function getLightAll()
		{
			$result = dbConnection()->query('SELECT id, name FROM tests');
			while($row = $result->fetch_object()) {
				$row->name = !empty($row->name) ? $row->name : 'Тест №' . $row->id;
				$tests[] = $row;
			}
			return $tests;
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

        protected $loggable = false;

		public static $mysql_table	= "test_students";
		
		protected $_json = ['answers'];
				
		public function __construct($array, $light_test = false)
		{
			parent::__construct($array, $light_test);
			
			if (! $this->isNewRecord) {
				$this->Test = $light_test ? Test::getLight($this->id_test) : Test::findById($this->id_test);
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
		
		public static function getForGroup($id_student, $id_subject, $grade) {
			$result = dbConnection()->query("
				SELECT ts.id FROM test_students ts
				JOIN tests t ON t.id = ts.id_test
				WHERE ts.id_student = {$id_student} AND t.id_subject={$id_subject} AND t.grade={$grade}
			");
			if ($result->num_rows) {
				return TestStudent::findById($result->fetch_object()->id, true);
			}
			return false;
		}
		
		public function start()
		{
			$this->date_start = now();
			$this->save('date_start');
		}
		
		function finalScoreString()
		{
			return round($this->score * 100 / Test::getMaxScore($this->id_test));
		}

		public function calcScore()
        {
            $score = 0;
            foreach($this->answers as $id_problem => $answer) {
                $Problem = TestProblem::findById($id_problem);
                if ($Problem->correct_answer == $answer) {
                    $score += $Problem->score;
                }
            }
            $this->score = $score;
        }

		public function finish()
		{
			$this->calcScore();
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
			return $this->date_start == EMPTY_DATETIME;
		}

        public static function getData($page)
        {
            if (!$page) {
                $page = 1;
            }
            // С какой записи начинать отображение, по формуле
            $start_from = ($page - 1) * TestStudent::PER_PAGE;
            $search = isset($_COOKIE['tests']) ? json_decode($_COOKIE['tests']) : (object)[];

            // получаем данные
            $query = static::_generateQuery($search, 'ts.*');
            $result = dbConnection()->query($query . ($page == -1 ? '' : " LIMIT {$start_from}, " . TestStudent::PER_PAGE));

            $data = false;
            if ($result->num_rows) {
                while ($row = $result->fetch_array()) {
                    if ($page == -1) {
                        $data[] = $row['id'];
                    } else {
                        $Test = new TestStudent($row);
                        $Test->Student = Student::getLight($Test->id_student);
                        $data[] = $Test;
                    }
                } 
            }

            if ($page > 0) {
                foreach(array_merge([''], array_keys(TestStates::$all)) as $state) {
                    $new_search = clone $search;
                    $new_search->state = $state;
                    $counts['state'][$state ? $state : 'all'] = static::_count($new_search);
                }

                foreach(array_merge([''], array_keys(Grades::$all)) as $grade) {
                    $new_search = clone $search;
                    $new_search->grade = $grade;
                    $counts['grade'][$grade ? $grade : 'all'] = static::_count($new_search);
                }

                foreach(array_merge([''], array_keys(Subjects::$all)) as $subject) {
                    $new_search = clone $search;
                    $new_search->subject = $subject;
                    $counts['subject'][$subject ? $subject : 'all'] = static::_count($new_search);
                }
            }

            return [
                'data' 	=> $data,
                'counts' => $counts,
            ];
        }

        private static function _count($search) {
//            header('_'.microtime(true).':'.static::_generateQuery($search, "COUNT(*) AS cnt"));
            return dbConnection()
                ->query(static::_generateQuery($search, "COUNT(*) AS cnt"))
                ->fetch_object()
                ->cnt;
        }

        static $empty_date_condition = "date_start = '".EMPTY_DATETIME."'";
        static $not_empty_date_condition = "date_start <> '".EMPTY_DATETIME."'";
        static $finished_condition = "(ts.date_finish <> '".EMPTY_DATETIME."' or (UNIX_TIMESTAMP() - UNIX_TIMESTAMP(ts.date_start) > (60 * t.minutes)))";

        private static function _generateQuery($search, $select)
        {
            $main_query =
				' FROM test_students ts ' .
                ' JOIN tests t ON ts.id_test = t.id ' .
                ' WHERE true ' .
                ($search->state && $search->state == 'not_started' ? ' and '.self::$empty_date_condition : '') .
                ($search->state && $search->state == 'in_progress' ? ' and '.self::$not_empty_date_condition.' and (not '.self::$finished_condition.')' : '') .
                ($search->state && $search->state == 'finished' ? ' and '.self::$not_empty_date_condition .' and '.self::$finished_condition : '') .
                (!isBlank($search->subject) ? " AND t.id_subject = " . $search->subject : "") .
                (!isBlank($search->grade) ? " AND t.grade = " . $search->grade : "")
            ;
            return "SELECT " . $select . $main_query;
        }
	}