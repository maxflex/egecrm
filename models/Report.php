<?php
	class Report extends Model
	{
		/*====================================== ПЕРЕМЕННЫЕ И КОНСТАНТЫ ======================================*/
		const PER_PAGE = 30;

		// кол-во занятий до того, как потребуется отчет
		// (с этой цифры включительно уже требуется)
		const LESSON_COUNT = 8;

		public static $mysql_table	= "reports";

		public function __construct($array)
		{
			parent::__construct($array);

			if (! $this->isNewRecord) {
				$this->force_noreport = ReportForce::check($this->id_student, $this->id_teacher, $this->id_subject, $this->year);
				$this->lesson_count = ReportHelper::getLessonCount($this->id_student, $this->id_teacher, $this->id_subject, $this->year);
				$this->Student = Student::getLight($this->id_student);
			}
		}

        public static function add($array)
        {
            $array['year'] = academicYear();
            parent::add($array);
        }

		public function countByYear()
		{
			$search = json_decode($_COOKIE['reports']);

			if ($search->year) {
				$data = [
					"condition" => "year={$search->year}"
				];
			}

			return Report::count($data);
		}

        public function beforeSave()
        {
            if ($this->available_for_parents && $this->available_for_parents != $this->getOriginal('available_for_parents')) {
                $Student = Student::findById($this->id_student);
                $sms_message = Template::get(11, [
					'representative_name'	=> $Student->Representative->first_name . " " . $Student->Representative->middle_name,
					'subject'				=> Subjects::$dative[$this->id_subject],
                    'entity_login'          => $Student->login,
                    'entity_password'       => $Student->password
				]);
				foreach (Student::$_phone_fields as $phone_field) {
					$representative_number = $Student->Representative->{$phone_field};
					if (!empty($representative_number)) {
						SMS::send($representative_number, $sms_message);
					}
				}
            }
        }

        /**
         * Check if report is needed
         */
        public static function required($id_student, $id_teacher, $id_subject, $year)
        {
            // if last year then not needed
            if ($year != academicYear()) {
                return false;
            }

            $in_group = Group::count([
                "condition" => "FIND_IN_SET({$id_student}, students) AND id_subject={$id_subject} AND id_teacher={$id_teacher} AND year={$year} and ended = 0"
            ]);

            $lesson_count = dbConnection()->query("
                                SELECT COUNT(*) AS cnt FROM reports_helper rh
                                LEFT JOIN reports_force rf ON (rf.id_subject = rh.id_subject AND rf.id_teacher = rh.id_teacher AND rf.id_student = rh.id_student AND rf.year = rh.year)
                                WHERE rh.lesson_count >= " . self::LESSON_COUNT . " AND rf.id IS NULL AND rh.id_report IS NULL AND
                                rh.id_student = {$id_student} AND rh.id_teacher = {$id_teacher} AND rh.id_subject = {$id_subject} AND rh.year={$year}
                            ")->fetch_object()->cnt;

            return $lessons_count >= self::LESSON_COUNT && $in_group;
        }

        /**
         * Get reports count in configuration
         */
        public static function getCount($id_student, $id_teacher, $id_subject, $year, $available_for_parents = false)
        {
            return Report::count([
                'condition' => self::conditionString($id_student, $id_teacher, $id_subject, $year) . ($available_for_parents ? ' and available_for_parents=1' : '')
            ]);
        }

        /**
         * Get lessons count in configuration
         */
        public function getLessonsCount($id_student, $id_teacher, $id_subject, $year)
        {
            return dbConnection()->query('
                SELECT SUM(lesson_count) AS `sum`
                FROM reports_helper
                WHERE ' . self::conditionString($id_student, $id_teacher, $id_subject, $year)
            )->fetch_object()->sum;
        }

        /**
         * Get reports in configuration
         */
        public static function get($id_student, $id_teacher, $id_subject, $params = false)
        {
            return self::findAll([
            	'condition' => self::conditionString($id_student, $id_teacher, $id_subject) . ($params ? ' and ' . implode(' and ', array_map(function($key, $value) {
						return "$key='$value'";
					}, array_keys($params), $params)) : '')
            ]);
        }

        /*
	     * Get reports in configuration [available for parents only]
	     */
	    public static function getAvailableForParents($params)
	    {
		    $query = dbConnection()->query("
				select rh.* from reports_helper rh
				left join reports r on rh.id_report = r.id
				where "
					. implode(' and ', array_map(function($key, $value) {
						return "rh.$key='$value'";
					}, array_keys($params), $params))
					. " and (r.id is null or r.available_for_parents=1)
					group by rh.id_student, rh.id_subject, rh.id_teacher, rh.year
			");

			while ($row = $query->fetch_object()) {
				$data[] = $row;
			}

			return $data;
	    }

        public static function condition($id_student, $id_teacher, $id_subject, $year = null)
		{
			return [
				'condition' => self::conditionString($id_student, $id_teacher, $id_subject, $year),
				'order' 	=> "STR_TO_DATE(date, '%d.%m.%Y') desc "
			];
		}

        public static function conditionString($id_student, $id_teacher, $id_subject, $year = null)
		{
			return "id_student = {$id_student} AND id_teacher = {$id_teacher} AND id_subject = {$id_subject}" . ($year ? " AND year = {$year}" : '');
		}
	}

	class ReportHelper extends Model
	{
		/*====================================== ПЕРЕМЕННЫЕ И КОНСТАНТЫ ======================================*/
		public static $mysql_table	= "reports_helper";

		public static function recalc()
		{
			dbConnection()->query("TRUNCATE TABLE " . ReportHelper::$mysql_table);

			$query = "
				SELECT vj.id_entity, vj.id_subject, vj.id_teacher, vj.year, r.id, STR_TO_DATE(r.date,'%d.%m.%Y') as date
				FROM visit_journal vj
				LEFT JOIN reports r ON (r.id_student = vj.id_entity AND r.id_teacher = vj.id_teacher AND r.id_subject = vj.id_subject AND r.year = vj.year)
				WHERE vj.type_entity='STUDENT'
				GROUP BY vj.id_entity, vj.id_subject, vj.id_teacher, vj.year, r.id
			";

			$result = dbConnection()->query($query);

			$data = [];

			while ($row = $result->fetch_object()) {
				$data[] = $row;
			}

			foreach ($data as $Object) {
				$condition = "id_student=" . $Object->id_entity . " AND id_subject=" . $Object->id_subject ."
							AND id_teacher=" . $Object->id_teacher ." AND year=" . $Object->year;

				// если отчет существует, то узнаем за какой срок был создан отчет
				if ($Object->id)	{
					// отчет перед отчетом
					$LatestReport = Report::find([
						"condition" => $condition . " AND STR_TO_DATE(date,'%d.%m.%Y') < '{$Object->date}'",
	                    "order" 	=> "STR_TO_DATE(date,'%d.%m.%Y') desc "
					]);

					// если перед отчетом был отчет
					if ($LatestReport) {
						$latest_report_date = date("Y-m-d", strtotime($LatestReport->date));
					} else {
						$latest_report_date = "0000-00-00";
					}

					$lessons_count = VisitJournal::count([
						"condition" => "id_subject={$Object->id_subject} AND id_entity={$Object->id_entity}
							AND id_teacher=" . $Object->id_teacher . " AND year={$Object->year}
							AND lesson_date > '$latest_report_date' AND lesson_date < '{$Object->date}'"
					]);
				} else {
				// если отчет не существует, то подсчитываем сколько дней прошло с последнего отчета/начала занятий
					// получаем кол-во занятий с последнего отчета по предмету
					$LatestReport = Report::find([
						"condition" => $condition,
	                    "order" 	=> "STR_TO_DATE(date,'%d.%m.%Y') desc "
					]);

					if ($LatestReport) {
						$latest_report_date = date("Y-m-d", strtotime($LatestReport->date));
					} else {
						$latest_report_date = "0000-00-00";
					}

					$lessons_count = VisitJournal::count([
						"condition" => "id_subject={$Object->id_subject} AND id_entity={$Object->id_entity}
							AND id_teacher=" . $Object->id_teacher . " AND year={$Object->year}
							AND lesson_date > '$latest_report_date'"
					]);
				}

				ReportHelper::add([
					'id_report' 	=> $Object->id,
					'id_teacher' 	=> $Object->id_teacher,
					'id_student' 	=> $Object->id_entity,
					'id_subject' 	=> $Object->id_subject,
					'year'			=> $Object->year,
					'lesson_count'	=> $lessons_count,
				]);
			}

			return Settings::set('reports_updated', now());
		}

		public static function getLessonCount($id_student, $id_teacher, $id_subject, $year)
		{
			return ReportHelper::find(Report::condition($id_student, $id_teacher, $id_subject, $year))->lesson_count;
		}
	}


	class ReportForce extends Model
	{
		/*====================================== ПЕРЕМЕННЫЕ И КОНСТАНТЫ ======================================*/

		public static $mysql_table	= "reports_force";

		public static function check($id_student, $id_teacher, $id_subject, $year)
		{
			return ReportForce::count(Report::condition($id_student, $id_teacher, $id_subject, $year)) > 0;
		}

		public static function toggle($id_student, $id_teacher, $id_subject, $year)
		{
			$ReportForce = ReportForce::find(Report::condition($id_student, $id_teacher, $id_subject, $year));

			if ($ReportForce) {
				$ReportForce->delete();
			} else {
				ReportForce::add(compact('id_student', 'id_teacher', 'id_subject', 'year'));
			}
		}
	}
?>
