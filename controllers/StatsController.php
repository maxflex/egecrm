<?php

	// Контроллер
	class StatsController extends Controller
	{
		public $defaultAction = "list";

		const PER_PAGE 	= 30;

		// Папка вьюх
		protected $_viewsFolder	= "stats";

		// условие, которое не берет в расчет версии договора
		const ZERO_OR_NULL_CONDITION = "AND (id_contract=0 OR id_contract IS NULL)";


		public function beforeAction()
		{
			$this->addJs("ng-stats-app");
		}

/*
		private function _getSubjectColorCount($Group, $status)
		{
			if (!count($Group->students)) {
				return 0;
			}

			$student_ids = implode(",", $Group->students);

			$result = dbConnection()->query("
				SELECT COUNT(*) as cnt FROM contract_subjects cs
				LEFT JOIN contracts c on cs.id_contract = c.id
				LEFT JOIN students s on c.id_student = s.id
				WHERE s.id IN {$student_ids} AND cs.id_subject = {$Group->id_subject} AND cs.status = {$status}
			");
		}
*/



		# ============================= #
		# ==== ОСНОВНАЯ СТАТИСТИКА ==== #
		# ============================= #






		private function _getStats($date_start, $date_end = false)
		{
			$date_start_formatted 	= date("Y-m-d", strtotime($date_start));
			$date_end_formatted		= date("Y-m-d", strtotime($date_end));

			$Contracts = Contract::findAll([
				"condition" =>
					$date_end 	? "STR_TO_DATE(date, '%d.%m.%Y') > '$date_start_formatted' AND STR_TO_DATE(date, '%d.%m.%Y') <= '$date_end_formatted'"
								: "date='$date_start'"
			]);

			$Payments = Payment::findAll([
				"condition" => "entity_type='".Student::USER_TYPE."' and ".
					($date_end 	? "STR_TO_DATE(date, '%d.%m.%Y') > '$date_start_formatted' AND STR_TO_DATE(date, '%d.%m.%Y') <= '$date_end_formatted'"
								: "date = '$date_start'")
			]);

			foreach ($Contracts as $index => $Contract) {
				if ($Contract->isOriginal()) {
					$stats['contract_new']++;
					// сумма заключенных дагаваров
					$stats['contract_sum_new'] += $Contract->sum;
					$stats['subjects_new'] += count($Contract->subjects);

					continue; # если договор оригинальный, у него не может быть предыдущих версий
				}

				// если есть версия договора
				$PreviousContract = $Contract->getPreviousVersion();
				if ($PreviousContract) {
					// если сумма увеличилась
					if ($Contract->sum > $PreviousContract->sum) {
						$stats['contract_sum_changed'] += ($Contract->sum - $PreviousContract->sum);
					}

					// если сумма уменьшилась
					if ($PreviousContract->sum > $Contract->sum) {
						if (!isset($stats['contract_sum_changed'])) {
							$stats['contract_sum_changed'] = 0;
						}
						$stats['contract_sum_changed'] -= ($PreviousContract->sum - $Contract->sum);
					}

					// уменьшение услуг (было БОЛЬШЕ стало МЕНЬШЕ)
					if ($PreviousContract->activeSubjectsCount() - $Contract->activeSubjectsCount() > 0) {
						$stats['subjects_minus'] += $PreviousContract->activeSubjectsCount() - $Contract->activeSubjectsCount();
					}

					// увеличение услуг
					if ($Contract->activeSubjectsCount() - $PreviousContract->activeSubjectsCount() > 0) {
						$stats['subjects_plus'] += $Contract->activeSubjectsCount() - $PreviousContract->activeSubjectsCount();
					}

/*

					// если был НЕ расторжен и стал расторжен
					if ($Contract->isCancelled() &&  !$PreviousContract->isCancelled()) {
						// сумма расторгнутых
						$stats['contract_sum_changed'] -= $Contract->sum;
					}


					// если расторжен и стал НЕ расторжен
					if (!$Contract->isCancelled() && $PreviousContract->isCancelled()) {
						// сумма реанимированых
						$stats['contract_sum_changed'] += $Contract->sum;
					}

*/
				}
			}

			foreach ($Payments as $Payment) {
				if ($Payment->id_type == PaymentTypes::PAYMENT) {
					if ($Payment->confirmed) {
						$stats['payment_confirmed'] += $Payment->sum;
					} else {
						$stats['payment_unconfirmed'] += $Payment->sum;
					}
				} else
				if ($Payment->id_type == PaymentTypes::RETURNN) {
					if ($Payment->confirmed) {
						$stats['return_confirmed'] += $Payment->sum;
					} else {
						$stats['return_unconfirmed'] += $Payment->sum;
					}
				}
			}

			$requests_count = Request::count([
				"condition" =>
					$date_end 	? "DATE(date) > '". $date_start_formatted ."' AND DATE(date) <= '". $date_end_formatted ."' AND adding=0"
								: "DATE(date) = '". $date_start_formatted ."' AND adding=0"
			]);

			$stats['requests'] = $requests_count;

			return $stats;
		}

		protected function getByDays()
		{
			$page = $_GET['page'];
			if (!$page) {
				$page = 1;
			}

			$start = ($page - 1) * self::PER_PAGE;

			for ($i = (self::PER_PAGE * $page); $i >= $start + ($page > 1 ? 1 : 0); $i--) {
				$date = date("d.m.Y", strtotime("today -$i day"));
				$stats[$date] = self::_getStats($date);
			}


			uksort($stats, function($a, $b) {
				$d1 = date("Y-m-d", strtotime($a));
				$d2 = date("Y-m-d", strtotime($b));

				if ($d1 > $d2) {
					return -1;
				} else
				if ($d1 < $d2) {
					return 1;
				} else {
					return 0;
				}
			});

			return $stats;
		}

		protected function getByWeeks()
		{
			$date_end = date("d.m.Y", time());

			for ($i = 0; $i <= Request::timeFromFirst('weeks'); $i++) {
				$last_sunday = strtotime("last sunday -$i weeks");
				$date_start = date("d.m.Y", $last_sunday);

				$stats[$date_end] = self::_getStats($date_start, $date_end);

				$date_end = $date_start;
			}

			// добавляем расторгнутые
			return $stats;
		}

		protected function getByMonths()
		{
			$date_end = date("d.m.Y", time());
			for ($i = 1; $i <= Request::timeFromFirst('months'); $i++) {
				$last_day_of_month = strtotime("last day of -$i months");
				$date_start = date("d.m.Y", $last_day_of_month);

				$stats[$date_end] = self::_getStats($date_start, $date_end);

				$date_end = $date_start;
			}

			return $stats;
		}

		protected function getByYears()
		{
			$date_end = date("d.m.Y", time());

			for ($i = 1; $i <= Request::timeFromFirst('years'); $i++) {
				$last_day_of_july = strtotime("-$i year last day of july");
				$date_start = date("d.m.Y", $last_day_of_july);

				$stats[$date_end] = self::_getStats($date_start, $date_end);

				$date_end = $date_start;
			}

			return $stats;
		}


		public function actionList()
		{
			$this->setTabTitle("Итоги");

			switch ($_GET["group"]) {
				case "w": {
					$stats = self::getByWeeks();
					break;
				}
				case "m": {
					$stats = self::getByMonths();
					break;
				}
				case "y": {
					$stats = self::getByYears();
					break;
				}
				default: {
					$stats = self::getByDays();
					break;
				}
			}

			$ang_init_data = angInit([
				"currentPage" => $_GET['page'],
			]);


			$this->render("list", [
				"ang_init_data" 	=> $ang_init_data,
				"stats" => $stats,
			]);
		}






		# ================= #
		# ==== ПО ДНЯМ ==== #
		# ================= #






		private function _totalVisits($date_start, $date_end = false)
		{
			$return['lesson_count'] = VisitJournal::count([
				"condition" => ($date_end ? "lesson_date > '$date_start' AND lesson_date <= '$date_end'" : "lesson_date='$date_start'")
					. " AND type_entity='TEACHER'"
			]);

			$return['in_time'] = VisitJournal::count([
				"condition" => ($date_end ? "lesson_date > '$date_start' AND lesson_date <= '$date_end'" : "lesson_date='$date_start'")
					. " AND type_entity='STUDENT' AND presence!=2 AND late IS NULL"
			]);

			$return['late_count'] = VisitJournal::count([
				"condition" => ($date_end ? "lesson_date > '$date_start' AND lesson_date <= '$date_end'" : "lesson_date='$date_start'")
					. " AND type_entity='STUDENT' AND presence!=2 AND late > 0"
			]);

			$return['abscent_count'] = VisitJournal::count([
				"condition" => ($date_end ? "lesson_date > '$date_start' AND lesson_date <= '$date_end'" : "lesson_date='$date_start'")
					. " AND type_entity='STUDENT' AND presence=2"
			]);


			$return['abscent_percent'] = round($return['abscent_count'] / ($return['in_time'] + $return['late_count'] + $return['abscent_count']) * 100);

			return $return;
		}

		const PER_PAGE_STUDENTS = 30;
		const PER_PAGE_STUDENTS_PLUS = 3;


		private function getTotalVisitsByDays()
		{
			$page = $_GET['page'];
			if (!$page) {
				$page = 1;
			}

			$start = ($page - 1) * self::PER_PAGE_STUDENTS;
			for ($i = (self::PER_PAGE_STUDENTS * $page); $i >= $start + ($page > 1 ? 1 : 0); $i--) {
				$date = date("Y-m-d", strtotime("today -$i day"));
				$stats[$date] = self::_totalVisits($date);
			}

			return $stats;
		}

		public function plusDays($page = 1)
		{
			$start = ($page * self::PER_PAGE_STUDENTS_PLUS) + 1;

			for ($i = $start; $i < ($start + self::PER_PAGE_STUDENTS_PLUS); $i++) {
				$date = date("Y-m-d", strtotime("today +$i day"));
				$stats[$date] = self::_totalVisits($date);
			}

			return $stats;
		}

		private function getTotalVisitsByWeeks()
		{
			$date_end = date("Y-m-d", time());

			for ($i = 0; $i <= VisitJournal::fromFirstLesson('weeks'); $i++) {
				$last_sunday = strtotime("last sunday -$i weeks");
				$date_start = date("Y-m-d", $last_sunday);

				$stats[$date_end] = self::_totalVisits($date_start, $date_end);

				$date_end = $date_start;
			}

			return $stats;
		}


		private function getTotalVisitsByMonths()
		{
			$date_end = date("Y-m-d", time());

			// +1 день на будущий месяц
			for ($i = 1; $i <= VisitJournal::fromFirstLesson('months') + 1; $i++) {
				$last_day_of_month = strtotime("last day of -$i months");
				$date_start = date("Y-m-d", $last_day_of_month);

				$stats[$date_end] = self::_totalVisits($date_start, $date_end);

				$date_end = $date_start;
			}

			return $stats;
		}

		private function getTotalVisitsByYears()
		{
			$date_end = date("Y-m-d", time());

			for ($i = 1; $i <= VisitJournal::fromFirstLesson('years'); $i++) {
				$last_day_of_july = strtotime("last day of july -$i year");
				$date_start = date("Y-m-d", $last_day_of_july);

				$stats[$date_end] = self::_totalVisits($date_start, $date_end);

				$date_end = $date_start;
			}

			return $stats;
		}

		private function getTotalVisitsByWeekdays()
		{
// 			foreach(range(0, 6) as $day_number) {
			foreach(Freetime::DAYS_SHORT as $day => $title) {
				if ($day == 7) {
					$day_number = 0;
				} else {
					$day_number = $day;
				}

				$return[$day]['lesson_count'] = VisitJournal::count([
					"condition" => "DATE_FORMAT(lesson_date, '%w')=$day_number AND type_entity='TEACHER'"
				]);

				$return[$day]['in_time'] = VisitJournal::count([
					"condition" => "DATE_FORMAT(lesson_date, '%w')=$day_number AND type_entity='STUDENT' AND presence!=2 AND late IS NULL"
				]);

				$return[$day]['late_count'] = VisitJournal::count([
					"condition" => "DATE_FORMAT(lesson_date, '%w')=$day_number AND type_entity='STUDENT' AND presence!=2 AND late > 0"
				]);

				$return[$day]['abscent_count'] = VisitJournal::count([
					"condition" => "DATE_FORMAT(lesson_date, '%w')=$day_number AND type_entity='STUDENT' AND presence=2"
				]);


				$return[$day]['abscent_percent'] = round($return[$day]['abscent_count'] / ($return[$day]['in_time']
					+ $return[$day]['late_count'] + $return[$day]['abscent_count']) * 100);

				$return[$day]['title'] = $title;
			}

			return $return;
		}

		private function getTotalVisitsBySchedule()
		{
			foreach (Freetime::$weekdays_time as $day => $time_ids) {
				foreach ($time_ids as $time_id) {
					$key = $day . "_" . $time_id;

					if ($day == 7) {
						$day_number = 0;
					} else {
						$day_number = $day;
					}

					$return[$key]['lesson_count'] = VisitJournal::count([
						"condition" => "DATE_FORMAT(lesson_date, '%w')=$day_number AND lesson_time='" . Freetime::TIME[$time_id] . ":00' AND type_entity='TEACHER'"
					]);

					$return[$key]['in_time'] = VisitJournal::count([
						"condition" => "DATE_FORMAT(lesson_date, '%w')=$day_number AND lesson_time='" . Freetime::TIME[$time_id] . ":00' AND type_entity='STUDENT' AND presence!=2 AND late IS NULL"
					]);

					$return[$key]['late_count'] = VisitJournal::count([
						"condition" => "DATE_FORMAT(lesson_date, '%w')=$day_number AND lesson_time='" . Freetime::TIME[$time_id] . ":00' AND type_entity='STUDENT' AND presence!=2 AND late > 0"
					]);

					$return[$key]['abscent_count'] = VisitJournal::count([
						"condition" => "DATE_FORMAT(lesson_date, '%w')=$day_number AND lesson_time='" . Freetime::TIME[$time_id] . ":00' AND type_entity='STUDENT' AND presence=2"
					]);


					$return[$key]['abscent_percent'] = round($return[$key]['abscent_count'] / ($return[$key]['in_time']
						+ $return[$key]['late_count'] + $return[$key]['abscent_count']) * 100);

					$return[$key]['title'] = Freetime::DAYS_SHORT[$day] . " в " . Freetime::TIME[$time_id];
				}
			}

			return $return;
		}

		public function actionTotalVisits()
		{
			$this->setTabTitle("Общая посещаемость");

			$days_mode = 0;

			switch ($_GET["group"]) {
				case "w": {
					$stats = self::getTotalVisitsByWeeks();
					break;
				}
				case "m": {
					$stats = self::getTotalVisitsByMonths();
					break;
				}
				case "y": {
					$stats = self::getTotalVisitsByYears();
					break;
				}
				case "wd": {
					$stats = self::getTotalVisitsByWeekdays();

					$ang_init_data = angInit([
						"stats" => $stats,
						"days_mode" => $days_mode,
					]);

					$this->render("visits_by_weekdays", [
						"ang_init_data" => $ang_init_data,
					]);

					return;
				}
				case "s": {
					$stats = self::getTotalVisitsBySchedule();

					$ang_init_data = angInit([
						"stats" => $stats,
						"days_mode" => $days_mode,
					]);

					$this->render("visits_by_schedule", [
						"ang_init_data" => $ang_init_data,
					]);

					return;
				}
				default: {
					$stats 	= self::getTotalVisitsByDays();
// 					$errors = LOCAL_DEVELOPMENT ? CronController::actionUpdateJournalMiss() : memcached()->get("JournalErrors");
					$days_mode = 1; // в режиме просмотра по дням доступно намного больше функций
					break;
				}
			}

			$ang_init_data = angInit([
				"currentPage" 	=> $_GET['page'],
				"Subjects" 		=> Subjects::$three_letters,
				"stats"			=> $stats,
// 				"errors"		=> $errors,
				"missing"		=> Group::getLastWeekMissing(),
				"days_mode"		=> $days_mode,
			]);

			$this->render("total_visits", [
				"ang_init_data" => $ang_init_data,
			]);
		}


		# ================= #
		# ==== УЧЕНИКИ ==== #
		# ================= #







		public function actionTotalVisitStudents()
		{
			$this->setTabTitle("Общая посещаемость по ученикам");

			// получаем все ID преподов из журнала
			$result = dbConnection()->query("
				SELECT id_entity FROM visit_journal
				WHERE type_entity='STUDENT'
				GROUP BY id_entity"
			);
			while ($row = $result->fetch_object()) {
				$student_ids[] = $row->id_entity;
			}

			$Students = Student::findAll([
				"condition" => "id IN (" . implode(",", $student_ids) . ")",
			]);


			foreach ($Students as $index => &$Student) {
				$Student->lesson_count = VisitJournal::count([
					"condition" => "type_entity='STUDENT' AND id_entity=" . $Student->id
				]);

				$Student->in_time = VisitJournal::count([
					"condition" => "id_entity={$Student->id} AND type_entity='STUDENT' AND presence!=2 AND late IS NULL"
				]);

				$Student->late_count = VisitJournal::count([
					"condition" => "id_entity={$Student->id} AND type_entity='STUDENT' AND presence!=2 AND late > 0"
				]);

				$Student->abscent_count = VisitJournal::count([
					"condition" => "id_entity={$Student->id} AND type_entity='STUDENT' AND presence=2"
				]);

				$Student->abscent_percent = round($Student->abscent_count / ($Student->in_time + $Student->late_count + $Student->abscent_count) * 100);
			}

			usort($Students, function($a, $b) {
				return $b->abscent_percent - $a->abscent_percent;
			});

			$this->render("total_visit_students", [
				"ang_init_data" => $ang_init_data,
				"Students" 		=> $Students,
			]);
		}




		# ================= #
		# ==== ПРЕПОДЫ ==== #
		# ================= #







		public function actionTotalVisitTeachers()
		{
			$this->setTabTitle("Общая посещаемость по преподавателям");

			// $Teachers = Teacher::getActiveGroups();

			// получаем все ID преподов из журнала
			$result = dbConnection()->query("
				SELECT id_entity FROM visit_journal
				WHERE type_entity='TEACHER'
				GROUP BY id_entity"
			);
			while ($row = $result->fetch_object()) {
				$teacher_ids[] = $row->id_entity;
			}

			$Teachers = Teacher::findAll([
				"condition" => "in_egecentr = 1 AND id IN (" . implode(",", $teacher_ids) . ")",
				"order"		=> "last_name ASC, first_name ASC, middle_name ASC"
			]);

			foreach ($Teachers as $index => &$Teacher) {
				$Teacher->lesson_count = VisitJournal::count([
					"condition" => "type_entity='TEACHER' AND id_entity=" . $Teacher->id
				]);

				$Teacher->in_time = VisitJournal::count([
					"condition" => "id_teacher={$Teacher->id} AND type_entity='STUDENT' AND presence!=2 AND late IS NULL"
				]);

				$Teacher->late_count = VisitJournal::count([
					"condition" => "id_teacher={$Teacher->id} AND type_entity='STUDENT' AND presence!=2 AND late > 0"
				]);

				$Teacher->abscent_count = VisitJournal::count([
					"condition" => "id_teacher={$Teacher->id} AND type_entity='STUDENT' AND presence=2"
				]);

				$Teacher->abscent_percent = round($Teacher->abscent_count / ($Teacher->in_time + $Teacher->late_count + $Teacher->abscent_count) * 100);
			}

			$this->render("total_visit_teachers", [
				"ang_init_data" => $ang_init_data,
				"Teachers" 		=> $Teachers,
			]);
		}




		# ================= #
		# ==== КЛАССЫ ==== #
		# ================= #







		public function actionTotalVisitGrades()
		{
			$this->setTabTitle("Общая посещаемость по классам");

			foreach (range(9, 11) as $grade) {
				$return[$grade]['lesson_count'] = VisitJournal::count([
					"condition" => "type_entity='TEACHER' AND grade=$grade"
				]);

				$return[$grade]['in_time'] = VisitJournal::count([
					"condition" => "grade=$grade AND  type_entity='STUDENT' AND presence!=2 AND late IS NULL"
				]);

				$return[$grade]['late_count'] = VisitJournal::count([
					"condition" => "grade=$grade AND  type_entity='STUDENT' AND presence!=2 AND late > 0"
				]);

				$return[$grade]['abscent_count'] = VisitJournal::count([
					"condition" => "grade=$grade AND type_entity='STUDENT' AND presence=2"
				]);

				$return[$grade]['abscent_percent'] = round($return[$grade]['abscent_count'] / ($return[$grade]['in_time'] + $return[$grade]['late_count'] + $return[$grade]['abscent_count']) * 100);
			}

			$this->render("total_visit_grades", [
				"ang_init_data" => $ang_init_data,
				"stats"			=> $return,
			]);
		}


		# ================= #
		# ==== ПРЕДМЕТЫ ==== #
		# ================= #







		public function actionTotalVisitSubjects()
		{
			$this->setTabTitle("Общая посещаемость по предметам");

			foreach (Subjects::$all as $id_subject => $subject) {
				$return[$id_subject]['title'] = $subject;

				$return[$id_subject]['lesson_count'] = VisitJournal::count([
					"condition" => "type_entity='TEACHER' AND id_subject=$id_subject"
				]);

				$return[$id_subject]['in_time'] = VisitJournal::count([
					"condition" => "id_subject=$id_subject AND  type_entity='STUDENT' AND presence!=2 AND late IS NULL"
				]);

				$return[$id_subject]['late_count'] = VisitJournal::count([
					"condition" => "id_subject=$id_subject AND  type_entity='STUDENT' AND presence!=2 AND late > 0"
				]);

				$return[$id_subject]['abscent_count'] = VisitJournal::count([
					"condition" => "id_subject=$id_subject AND type_entity='STUDENT' AND presence=2"
				]);

				$return[$id_subject]['abscent_percent'] = round($return[$id_subject]['abscent_count'] / ($return[$id_subject]['in_time'] + $return[$id_subject]['late_count'] + $return[$id_subject]['abscent_count']) * 100);
			}

			$this->render("total_visit_subjects", [
				"ang_init_data" => $ang_init_data,
				"stats"			=> $return,
			]);
		}




		# ================= #
		# ==== ПЛАТЕЖИ ==== #
		# ================= #






		private function _getPayments($date_start, $date_end = false)
		{
			$date_start_formatted 	= date("Y-m-d", strtotime($date_start));
			$date_end_formatted		= date("Y-m-d", strtotime($date_end));

			$Payments = Payment::findAll([
				"condition" => "entity_type = '".Student::USER_TYPE."' and ".
					($date_end 	? "STR_TO_DATE(date, '%d.%m.%Y') > '$date_start_formatted' AND STR_TO_DATE(date, '%d.%m.%Y') <= '$date_end_formatted'"
								: "date = '$date_start'")
			]);

			foreach ($Payments as $Payment) {
				if ($Payment->id_type == PaymentTypes::PAYMENT) {
					if ($Payment->confirmed) {
						$stats[$Payment->id_status]['payment_confirmed'] += $Payment->sum;
						$stats['payment_total_confirmed'] += $Payment->sum;
					} else {
						$stats[$Payment->id_status]['payment_unconfirmed'] += $Payment->sum;
						$stats['payment_total_unconfirmed'] += $Payment->sum;
					}
				} else
				if ($Payment->id_type == PaymentTypes::RETURNN) {
					if ($Payment->confirmed) {
						$stats[$Payment->id_status]['return_confirmed'] += $Payment->sum;
						$stats['return_total_confirmed'] += $Payment->sum;
					} else {
						$stats[$Payment->id_status]['return_unconfirmed'] += $Payment->sum;
						$stats['return_total_unconfirmed'] += $Payment->sum;
					}
				}
			}

			return $stats;
		}


		private function getPaymentsByDays()
		{
			$page = $_GET['page'];
			if (!$page) {
				$page = 1;
			}

			$start = ($page - 1) * self::PER_PAGE;

			for ($i = (self::PER_PAGE * $page); $i >= $start + ($page > 1 ? 1 : 0); $i--) {
				$date = date("d.m.Y", strtotime("today -$i day"));
				$stats[$date] = self::_getPayments($date);
			}


			uksort($stats, function($a, $b) {
				$d1 = date("Y-m-d", strtotime($a));
				$d2 = date("Y-m-d", strtotime($b));

				if ($d1 > $d2) {
					return -1;
				} else
				if ($d1 < $d2) {
					return 1;
				} else {
					return 0;
				}
			});

			return $stats;
		}


		private function getPaymentsByWeeks()
		{
			$date_end = date("d.m.Y", time());

			for ($i = 0; $i <= Payment::timeFromFirst('weeks'); $i++) {
				$last_sunday = strtotime("last sunday -$i weeks");
				$date_start = date("d.m.Y", $last_sunday);

				$stats[$date_end] = self::_getPayments($date_start, $date_end);

				$date_end = $date_start;
			}

			return $stats;
		}


		private function getPaymentsByMonths()
		{
			$date_end = date("d.m.Y", time());

			for ($i = 1; $i <= Payment::timeFromFirst('months'); $i++) {
				$last_day_of_month = strtotime("last day of -$i months");
				$date_start = date("d.m.Y", $last_day_of_month);

				$stats[$date_end] = self::_getPayments($date_start, $date_end);

				$date_end = $date_start;
			}

			return $stats;
		}

		private function getPaymentsByYears()
		{
			$date_end = date("d.m.Y", time());

			for ($i = 1; $i <= Payment::timeFromFirst('years'); $i++) {
				$last_day_of_july = strtotime("last day of july -$i year");
				$date_start = date("d.m.Y", $last_day_of_july);

				$stats[$date_end] = self::_getPayments($date_start, $date_end);

				$date_end = $date_start;
			}

			return $stats;
		}


		public function actionPayments()
		{
			$this->setTabTitle("Итоги");

			switch ($_GET["group"]) {
				case "w": {
					$stats = self::getPaymentsByWeeks();
					break;
				}
				case "m": {
					$stats = self::getPaymentsByMonths();
					break;
				}
				case "y": {
					$stats = self::getPaymentsByYears();
					break;
				}
				default: {
					$stats = self::getPaymentsByDays();
					break;
				}
			}

			$ang_init_data = angInit([
				"currentPage" => $_GET['page'],
			]);

			$this->render("payments", [
				"ang_init_data" => $ang_init_data,
				"stats" 		=> $stats,
			]);
		}




		public function actionUsers()
		{
			if (!empty($_GET['date_start'])) {
				$date_start = $_GET['date_start'];
				$date_end 	= $_GET['date_end'];

				$date_condition = " AND (date >= '". $date_start ."' AND date <= '" . $date_end . "')";
			} else {
				$date_condition = " AND (date >= '2015-09-01' AND date <= '2015-09-31')";
			}

			$Sources = Sources::$all;
			$Sources[0] = 'не установлен';

			$Users = User::findAll([
				"condition" => "type='USER'"
			]);


			// Все решающие заявки
			$success_request_ids = Request::getIds([
				"condition" => "id_user > 0",
				"group"		=> "id_student",
				"order"		=> "id ASC",
			]);

			$success_request_ids = implode(",", $success_request_ids);

			foreach ($Users as &$User) {
				// заявок всего
				$User->total_requests = Request::count([
					'condition' => 'id_user=' . $User->id . $date_condition,
				]);

				// решающих заявок
				$user_success_request_ids = Request::getIds([
					"condition" => "id_user={$User->id} AND id IN ($success_request_ids)" . $date_condition,
				]);

				// статусы заявок
				foreach ($Sources as $id_source => $name) {
					$count = Request::count([
						"condition" => "id_source=".$id_source." AND id_user={$User->id} AND id IN ($success_request_ids)" . $date_condition,
					]);

					if ($count > 0) {
						$User->counts[$id_source] = $count;
					}
				}

				if ($user_success_request_ids) {
					$User->total_success_requests = count($user_success_request_ids);


					// кол-во договоров
					$result = dbConnection()->query("SELECT id_student, id_source FROM requests WHERE id IN (" . implode(",", $user_success_request_ids) .")");

					$student_ids = [];
					while ($row = $result->fetch_object()) {
						$student_ids[] = $row->id_student;

						// кол-во разных учеников
						$User->count_students[$row->id_source]++;
					}

					$User->student_count = count($student_ids);

					$student_ids = implode(",", $student_ids);


					// получаем договоры ученика
					$Contracts = Contract::findAll([
						"condition" => "id_student IN ($student_ids)"
					]);

					if ($Contracts) {
						$User->total_contracts = count($Contracts);

						foreach ($Contracts as $Contract) {

							$result = dbConnection()->query("
								SELECT id_source FROM requests r
								LEFT JOIN contracts c on c.id_student = r.id_student
								WHERE r.id_student = {$Contract->id_student} AND r.id_user = {$User->id}
							");

							$id_source = $result->fetch_object()->id_source;

							$User->count_contracts[$id_source]++;
							$User->count_contracts_sum[$id_source] += $Contract->sum;

							if ($Contract->History) {
								$User->total_contract_sum += $Contract->History[0]->sum;
							} else {
								$User->total_contract_sum += $Contract->sum;
							}
						}
					} else {
						$User->total_contracts = 0;
					}
				} else {
					$User->total_success_requests = 0;
				}
			}

			$ang_init_data = angInit([
				"date_start"	=> $date_start,
				"date_end"		=> $date_end,
				"Sources" 		=> $Sources,
				"Users" => $Users
			]);

			$this->setTabTitle("Статистика по пользователям");
			$this->render("users", [
				"ang_init_data" => $ang_init_data
			]);

// 			preType($Users);
		}

	}
