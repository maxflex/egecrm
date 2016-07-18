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
		
		public function getEmail()
		{
			$Student = Student::findById($this->id_student);
			return $Student->Representative->email;
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
	}
	
	class ReportHelper extends Model
	{
		/*====================================== ПЕРЕМЕННЫЕ И КОНСТАНТЫ ======================================*/
		public static $mysql_table	= "reports_helper";
		 
		public function recalc()
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
			return ReportHelper::find(ReportForce::condition($id_student, $id_teacher, $id_subject, $year))->lesson_count;
		}
	}
	
	
	class ReportForce extends Model
	{
		/*====================================== ПЕРЕМЕННЫЕ И КОНСТАНТЫ ======================================*/

		public static $mysql_table	= "reports_force";
		
		public static function check($id_student, $id_teacher, $id_subject, $year)
		{
			return ReportForce::count(ReportForce::condition($id_student, $id_teacher, $id_subject, $year)) > 0;
		}
		
		public static function toggle($id_student, $id_teacher, $id_subject, $year)
		{
			$ReportForce = ReportForce::find(ReportForce::condition($id_student, $id_teacher, $id_subject, $year));
			
			if ($ReportForce) {
				$ReportForce->delete();
			} else {
				ReportForce::add(compact('id_student', 'id_teacher', 'id_subject', 'year'));
			}
		}
		
		public static function condition($id_student, $id_teacher, $id_subject, $year)
		{
			return ['condition' => "id_student = {$id_student} AND id_teacher = {$id_teacher} AND id_subject = {$id_subject} AND year = {$year}"];
		}
	}
?>