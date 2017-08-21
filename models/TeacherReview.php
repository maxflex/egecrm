<?php
	class TeacherReview extends Model
	{
		/*====================================== ПЕРЕМЕННЫЕ И КОНСТАНТЫ ======================================*/

		public static $mysql_table	= "teacher_reviews";

		const PER_PAGE = 30;
		const PLACE = 'REVIEW';

		/*====================================== СИСТЕМНЫЕ ФУНКЦИИ ======================================*/

		public function __construct($array)
		{
			parent::__construct($array);
			if (! $this->isNewRecord) {
				$this->Student = Student::getLight($this->id_student);
				$this->lesson_count = VisitJournal::count([
					'condition' => "id_entity = {$this->id_student} AND id_teacher = {$this->id_teacher}
										AND id_subject = {$this->id_subject} AND year = {$this->year}"
				]);
			}
		}

		/*====================================== СТАТИЧЕСКИЕ ФУНКЦИИ ======================================*/

		public static function countByYear()
		{
			$new_search = new StdClass;
			$new_search->mode = 0;

			$search = json_decode($_COOKIE['reviews']);
			if ($search->year) {
				$new_search->year = $search->year;
			}

			return static::_count($new_search);
		}

		public static function addData($rating, $id_student, $id_teacher, $id_subject, $year)
		{
			$data = [
				"id_student"			=> $id_student,
				"id_teacher"			=> $id_teacher,
				"id_subject"			=> $id_subject,
				"year"					=> $year,
				"rating"				=> $rating['rating'],
				"admin_rating"			=> $rating['admin_rating'],
				"admin_rating_final" 	=> $rating['admin_rating_final'],
				"comment" 				=> $rating['comment'],
				"admin_comment" 		=> $rating['admin_comment'],
				"admin_comment_final" 	=> $rating['admin_comment_final'],
				"published" 			=> $rating['published'],
				"approved" 			    => $rating['approved'],
				"score" 				=> $rating['score'],
				"signature" 			=> $rating['signature'],
				"max_score" 			=> $rating['max_score'],
				"date"					=> now(),
			];

			$Review = TeacherReview::getInfo($id_student, $id_teacher, $id_subject, $year);

			if ($Review) {
				$Review->update($data, true);
			} else {
				// если добавили отзыв, то возвращаем ID, чтобы была возможность комментировать
				return self::add($data)->id;
			}
		}

		public static function getInfo($id_student, $id_teacher, $id_subject, $year)
		{
			return self::find([
				"condition" => "id_student=" . $id_student . " AND id_subject={$id_subject} AND id_teacher={$id_teacher} AND year={$year}",
			]);
		}


		/**
		 * Получить оценку учителя
		 */
		public static function getStatus($id_student, $id_teacher, $id_subject, $year)
		{
			$StudentTeacherLike = static::find([
				"condition" => "id_teacher={$id_teacher} AND id_student={$id_student} AND id_subject={$id_subject} AND year='{$year}'"
			]);

			if ($StudentTeacherLike) {
				return $StudentTeacherLike->admin_rating;
			} else {
				return 0;
			}
		}

		/*
		 * Получить данные для основного модуля
		 * $id_student – если просматриваем отзывы отдельного ученика
		 */
		public static function getData($page, $Teachers, $id_student)
		{
			if (!$page) {
				$page = 1;
			}
			// С какой записи начинать отображение, по формуле
			$start_from = ($page - 1) * TeacherReview::PER_PAGE;

			$search = $id_student ? (object)compact('id_student') : json_decode($_COOKIE['reviews']);
			if (gettype($search) != "object") {
				$search = new StdClass;
			}

			// получаем данные
			$query = static::_generateQuery($search, "vj.id_entity, vj.id_subject, vj.id_teacher, vj.year, r.id, r.rating, r.grade,
				r.admin_rating, r.admin_rating_final, r.published, r.approved, r.score, r.max_score, r.comment, r.admin_comment, r.admin_comment_final, " . static::_countQuery('vj2'));
			$result = dbConnection()->query($query . ($id_student ? "" : " LIMIT {$start_from}, " . TeacherReview::PER_PAGE));

			while ($row = $result->fetch_object()) {
				$data[] = $row;
			}

			foreach ($data as &$d) {
				$d->Student = Student::getLight($d->id_entity);
				$d->Teacher = Teacher::getLight($d->id_teacher);
			}

			if (! $id_student) {
				// counts
				$counts['all'] = static::_count($search);

				foreach(array_merge([""], Years::$all) as $year) {
					$new_search = clone $search;
					$new_search->year = $year;
					$counts['year'][$year] = static::_count($new_search);
				}
				foreach(([''=>''] + Subjects::$all) as $id_subject => $name) {
					$new_search = clone $search;
					$new_search->id_subject = $id_subject;
					$counts['subject'][$id_subject] = static::_count($new_search);
				}
				foreach(array_merge(['id' => ''], $Teachers) as $Teacher) {
					$new_search = clone $search;
					$new_search->id_teacher = $Teacher['id'];
					$counts['teacher'][$Teacher['id']] = static::_count($new_search);
				}
				foreach(["", 1, 2, 3, 4, 5, 0] as $rating) {
					$new_search = clone $search;
					$new_search->rating = $rating;
					$counts['rating'][$rating] = static::_count($new_search);
				}
				foreach(["", 6, 1, 2, 3, 4, 5, 0] as $admin_rating) {
					$new_search = clone $search;
					$new_search->admin_rating = $admin_rating;
					$counts['admin_rating'][$admin_rating] = static::_count($new_search);
				}
				foreach(["", 6, 1, 2, 3, 4, 5, 0] as $admin_rating_final) {
					$new_search = clone $search;
					$new_search->admin_rating_final = $admin_rating_final;
					$counts['admin_rating_final'][$admin_rating_final] = static::_count($new_search);
				}
				foreach(["", 0, 1] as $published) {
					$new_search = clone $search;
					$new_search->published = $published;
					$counts['published'][$published] = static::_count($new_search);
				}
				foreach(["", 0, 1] as $approved) {
					$new_search = clone $search;
					$new_search->approved = $approved;
					$counts['approved'][$approved] = static::_count($new_search);
				}
				foreach(["", 0, 1] as $mode) {
					$new_search = clone $search;
					$new_search->mode = $mode;
					$counts['mode'][$mode] = static::_count($new_search);
				}
				foreach(["", 9, 10, 11, 12, 13, 14] as $grade) {
					$new_search = clone $search;
					$new_search->grade = $grade;
					$counts['grade'][$grade] = static::_count($new_search);
				}
				$users = User::getCached(true);
				foreach(array_merge([""], $users) as $user) {
					$new_search = clone $search;
					$new_search->id_user = $user['id'];
					$counts['user'][$user['id']] = static::_count($new_search);
				}
			}

			return [
				'data' 	=> $data,
				'counts' => $counts,
			];
		}

		private static function _count($search) {
			return dbConnection()
					->query(static::_generateQuery($search, "COUNT(*) AS cnt FROM (SELECT vj.id", false, ") AS X"))
					->fetch_object()
					->cnt;
		}

		private static function _connectTables($t, $addon = '') {
			return " {$t} ON ({$t}.id_student = vj.id_entity AND {$t}.id_teacher = vj.id_teacher AND {$t}.id_subject = vj.id_subject AND {$t}.year = vj.year {$addon})";
		}

		private static function _countQuery($t) {
			return "
				(SELECT COUNT(*) FROM visit_journal {$t}
				WHERE {$t}.id_entity = vj.id_entity AND {$t}.id_teacher = vj.id_teacher
					AND {$t}.id_subject = vj.id_subject AND {$t}.year = vj.year) AS lesson_count";
		}

		private static function _generateQuery($search, $select, $order = true, $ending)
		{
			$main_query = "
				FROM (select * from visit_journal where type_entity='STUDENT' GROUP BY id_entity, id_subject, id_teacher, `year`)  vj
				LEFT JOIN teacher_reviews" . static::_connectTables('r')
				. (isset($search->id_user) ? " JOIN students s ON s.id = vj.id_entity " : "") . "
				WHERE true "
				. ($search->year ? " AND vj.year={$search->year}" : "")
				. (($search->id_subject) ? " AND vj.id_subject={$search->id_subject}" : "")
				. ($search->id_teacher ? " AND vj.id_teacher={$search->id_teacher}" : "")
				. ($search->id_student ? " AND vj.id_entity={$search->id_student}" : "")
				. (!isBlank($search->mode) ? ($search->mode == 1 ? " AND r.id IS NOT NULL" : " AND r.id IS NULL") : "")
				. (!isBlank($search->published) ? " AND r.published={$search->published}" : "")
				. (!isBlank($search->approved) ? " AND r.approved={$search->approved}" : "")
				. (!isBlank($search->id_user) ? " AND s.id_user_review={$search->id_user}" : "")
				. (!isBlank($search->rating) ? " AND r.rating={$search->rating}" : "")
				. (!isBlank($search->grade) ? " AND r.grade={$search->grade}" : "")
				. (!isBlank($search->admin_rating) ? " AND r.admin_rating={$search->admin_rating}" : "")
				. (!isBlank($search->admin_rating_final) ? " AND r.admin_rating_final={$search->admin_rating_final}" : "")
				. ($order ? " ORDER BY vj.lesson_date DESC" : "");
			return "SELECT " . $select . $main_query . $ending;
		}


        public function beforeSave()
        {
            if ($this->isNewRecord) {
                $this->grade = dbConnection()->query("SELECT grade FROM visit_journal vj
                    WHERE vj.id_teacher = {$this->id_teacher}
                        AND vj.id_entity = {$this->id_student}
                        AND vj.id_subject = {$this->id_subject}
                        AND vj.year = {$this->year}
                        AND vj.type_entity = 'STUDENT'
                    LIMIT 1")->fetch_object()->grade;

                if ($this->admin_comment_final && ! $this->date) {
                    $this->date = now();
                }
            }
        }
	}
