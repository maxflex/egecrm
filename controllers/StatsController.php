<?php

	// Контроллер
	class StatsController extends Controller
	{
		public $defaultAction = "list";

		const PER_PAGE = 30; # указтель по скольку выводить на страницу

		// Папка вьюх
		protected $_viewsFolder	= "stats";

		public function beforeAction()
		{
            # @rights-refactored
            $this->checkRights(Shared\Rights::SHOW_STATS);
			$this->addJs("ng-stats-app");
		}


		# ============================= #
		# ==== ОСНОВНАЯ СТАТИСТИКА ==== #
		# ============================= #


		private function _getStats($date_start, $date_end = false)
		{
			$date_start_formatted 	= date("Y-m-d", strtotime($date_start));
			$date_end_formatted		= date("Y-m-d", strtotime($date_end));

            // @contract-refactored
			$Contracts = Contract::findAll([
				"condition" =>
					$date_end 	? "STR_TO_DATE(date, '%d.%m.%Y') > '$date_start_formatted' AND STR_TO_DATE(date, '%d.%m.%Y') <= '$date_end_formatted'"
								: "date='$date_start'"
			]);

			$Payments = Payment::findAll([
				"condition" => "entity_type='" . Student::USER_TYPE . "' and ".
					($date_end 	? "STR_TO_DATE(date, '%d.%m.%Y') > '$date_start_formatted' AND STR_TO_DATE(date, '%d.%m.%Y') <= '$date_end_formatted'"
								: "date = '$date_start'")
			]);

			foreach ($Contracts as $index => $Contract) {
				if ($Contract->isFirstInYear()) {
					if ($Contract->info->grade == Grades::EXTERNAL) {
						$stats['contract_new']['external']++;
						$stats['subjects_new']['external'] += count($Contract->subjects);
						// сумма заключенных дагаваров
						$stats['contract_sum_new']['external'] += $Contract->final_sum;
					} else {
						$stats['contract_new']['basic']++;
						$stats['subjects_new']['basic'] += count($Contract->subjects);
						$stats['contract_sum_new']['basic'] += $Contract->final_sum;
					}
					continue; # если договор оригинальный, у него не может быть предыдущих версий
				}

				// если есть версия договора
				$PreviousContract = $Contract->getPreviousVersionInYear();
				if ($PreviousContract) {
					// если сумма увеличилась
					if ($Contract->final_sum > $PreviousContract->final_sum) {
						$stats['contract_sum_changed'] += ($Contract->final_sum - $PreviousContract->final_sum);
					}

					// если сумма уменьшилась
					if ($PreviousContract->final_sum > $Contract->final_sum) {
						if (!isset($stats['contract_sum_changed'])) {
							$stats['contract_sum_changed'] = 0;
						}
						$stats['contract_sum_changed'] -= ($PreviousContract->final_sum - $Contract->final_sum);
					}

					// уменьшение услуг (было БОЛЬШЕ стало МЕНЬШЕ)
					if ($PreviousContract->activeSubjectsCount() - $Contract->activeSubjectsCount() > 0) {
						$stats['subjects_minus'] += $PreviousContract->activeSubjectsCount() - $Contract->activeSubjectsCount();
					}

					// увеличение услуг
					if ($Contract->activeSubjectsCount() - $PreviousContract->activeSubjectsCount() > 0) {
						$stats['subjects_plus'] += $Contract->activeSubjectsCount() - $PreviousContract->activeSubjectsCount();
					}
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
            # получаем значение текущей страницы
            $page = (!empty($_GET['page'])) ? intval($_GET['page']) : 1;

            # получаем указатель с какого по какое загружать
            $start = ($page - 1) * self::PER_PAGE;
            $end = $start + self::PER_PAGE;

            if ($page == 1) { # текущая неделя
                $date_end = date("d.m.Y", time());
            } else { # первая дата для текущего набора данных
                $date_end = date("d.m.Y", strtotime("last sunday -" . ($start - 1) . " weeks"));
            }

            for ($i = 0; $i <= Request::timeFromFirst('weeks'); $i++) {
                if ($i < $start) {
                    continue;
                }
                if ($i >= $end) {
                    continue;
                }

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
            # получаем значение текущей страницы
            $page = (!empty($_GET['page'])) ? intval($_GET['page']) : 1;

            # получаем указатель с какого по какое загружать
            $start = ($page - 1) * self::PER_PAGE;
            $end = $start + self::PER_PAGE;

            if ($page == 1) { # текущий месяц
                $date_end = date("d.m.Y", time());
            } else { # первая дата для текущего набора данных
                $date_end = date("d.m.Y", strtotime("last day of -" . ($start - 1) . " months"));
            }

			for ($i = 1; $i <= Request::timeFromFirst('months'); $i++) {
                if ($i < $start) {
                    continue;
                }
                if ($i >= $end) {
                    continue;
                }
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

            //определяем текущий учебный год
            if (date("j", time()) > 1 && date("n", time()) >= 5) {
                $current_year = date("Y", time());
            } else {
                $current_year = date("Y", time()) - 1;
            }

            for ($i = 0; $i <= Request::timeFromFirst('years') - 1; $i++) {
                $year = $current_year - $i;
                $date_start = date("d.m.Y", mktime(0, 0, 0, 5, 1, $year));
                if ($i == 0) {
                    $date_end = date("d.m.Y");
                } else {
                    $date_end = date("d.m.Y", mktime(0, 0, 0, 4, 30, $year + 1));
                }

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
					$timeFromFirstText = 'weeks'; # текстовый казатель недели для Request::timeFromFirst
					break;
				}
				case "m": {
					$stats = self::getByMonths();
                    $timeFromFirstText = 'months'; # текстовый казатель месяца для Request::timeFromFirst
					break;
				}
				case "y": {
					$stats = self::getByYears();
                    $timeFromFirstText = 'years'; # текстовый казатель года для Request::timeFromFirst
					break;
				}
				default: {
					$stats = self::getByDays();
                    $timeFromFirstText = 'days'; # текстовый казатель дня для Request::timeFromFirst
					break;
				}
			}

			$ang_init_data = angInit([
				"currentPage" => $_GET['page'],
			]);


			$this->render("list", [
				"ang_init_data" 	=> $ang_init_data,
				"stats" => $stats,
                "group" => (empty($_GET["group"])) ? 'd' : $_GET["group"], # указатель группировки
                "total_items" => Request::timeFromFirst($timeFromFirstText) # всего элементов
			]);
		}






		# ================= #
		# ==== ПО ДНЯМ ==== #
		# ================= #





        // @schedule-refactored
		private function _totalVisits($date_start, $date_end = false)
		{
			$return['lesson_count'] = VisitJournal::count([
				"condition" => ($date_end ? "lesson_date > '$date_start' AND lesson_date <= '$date_end'" : "lesson_date='$date_start'")
					. " AND type_entity='TEACHER'"
			]);

			// кол-во запланированных занятий
			if ($date_start >= date('Y-m-d') && !$date_end) {
				$return['planned_lesson_count'] = GroupSchedule::count([
					"condition" => "date='$date_start' AND id_group>0"
				]);
				// вычитаем кол-во прошедших занятий
				$return['planned_lesson_count'] -= $return['lesson_count'];
			}

			$return['in_time'] = VisitJournal::count([
				"condition" => ($date_end ? "lesson_date > '$date_start' AND lesson_date <= '$date_end'" : "lesson_date='$date_start'")
					. " AND type_entity='STUDENT' AND presence=1 AND (late is null or late = 0)"
			]);

			$return['late_count'] = VisitJournal::count([
				"condition" => ($date_end ? "lesson_date > '$date_start' AND lesson_date <= '$date_end'" : "lesson_date='$date_start'")
					. " AND type_entity='STUDENT' AND presence=1 AND late > 0"
			]);

			$return['abscent_count'] = VisitJournal::count([
				"condition" => ($date_end ? "lesson_date > '$date_start' AND lesson_date <= '$date_end'" : "lesson_date='$date_start'")
					. " AND type_entity='STUDENT' AND presence=2"
			]);

			$return['unset_count'] = VisitJournal::count([
				"condition" => ($date_end ? "lesson_date > '$date_start' AND lesson_date <= '$date_end'" : "lesson_date='$date_start'")
					. " AND type_entity='STUDENT' AND (presence is null or presence=0)"
			]);

			$denominator = $return['in_time'] + $return['late_count'] + $return['abscent_count'];
			$return['abscent_percent'] = $denominator ? round($return['abscent_count'] / $denominator * 100) : 0;

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
				$minus_days = $i - 6; // на неделю выше от сегодняшней даты показываем
				$date = date("Y-m-d", strtotime("today -$minus_days day"));
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
			foreach(Time::WEEKDAYS as $day => $title) {
				if ($day == 7) {
					$day_number = 0;
				} else {
					$day_number = $day;
				}

				$return[$day]['lesson_count'] = VisitJournal::count([
					"condition" => "DATE_FORMAT(lesson_date, '%w')=$day_number AND type_entity='TEACHER'"
				]);

				$return[$day]['in_time'] = VisitJournal::count([
					"condition" => "DATE_FORMAT(lesson_date, '%w')=$day_number AND type_entity='STUDENT' AND presence!=2 AND (late is null or late = 0)"
				]);

				$return[$day]['late_count'] = VisitJournal::count([
					"condition" => "DATE_FORMAT(lesson_date, '%w')=$day_number AND type_entity='STUDENT' AND presence!=2 AND late > 0"
				]);

				$return[$day]['abscent_count'] = VisitJournal::count([
					"condition" => "DATE_FORMAT(lesson_date, '%w')=$day_number AND type_entity='STUDENT' AND presence=2"
				]);

				$denominator = $return[$day]['in_time'] + $return[$day]['late_count'] + $return[$day]['abscent_count'];
				$return[$day]['abscent_percent'] = $denominator ? round($return[$day]['abscent_count'] / $denominator * 100) : 0;

				$return[$day]['title'] = $title;
			}

			return $return;
		}

		// @time-refactored @time-checked
		private function getTotalVisitsBySchedule()
		{
			$Time = Time::getLight();
			foreach(Time::MAP as $day => $data) {
				foreach ($data as $id_time) {
					$key = $day . "_" . $id_time;

					if ($day == 7) {
						$day_number = 0;
					} else {
						$day_number = $day;
					}

					$return[$key]['lesson_count'] = VisitJournal::count([
						"condition" => "DATE_FORMAT(lesson_date, '%w')=$day_number AND lesson_time='" . $Time[$id_time] . ":00' AND type_entity='TEACHER'"
					]);

					$return[$key]['in_time'] = VisitJournal::count([
						"condition" => "DATE_FORMAT(lesson_date, '%w')=$day_number AND lesson_time='" . $Time[$id_time] . ":00' AND type_entity='STUDENT' AND presence!=2 AND (late IS NULL or late = 0)"
					]);

					$return[$key]['late_count'] = VisitJournal::count([
						"condition" => "DATE_FORMAT(lesson_date, '%w')=$day_number AND lesson_time='" . $Time[$id_time] . ":00' AND type_entity='STUDENT' AND presence!=2 AND late > 0"
					]);

					$return[$key]['abscent_count'] = VisitJournal::count([
						"condition" => "DATE_FORMAT(lesson_date, '%w')=$day_number AND lesson_time='" . $Time[$id_time] . ":00' AND type_entity='STUDENT' AND presence=2"
					]);

					$denominator = $return[$key]['in_time'] + $return[$key]['late_count'] + $return[$key]['abscent_count'];
					$return[$key]['abscent_percent'] = $denominator ? round($return[$key]['abscent_count'] / $denominator * 100) : 0;

					$return[$key]['title'] = Time::WEEKDAYS[$day] . " в " . $Time[$id_time];
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
					$days_mode = 1; // в режиме просмотра по дням доступно намного больше функций
					break;
				}
			}

			$ang_init_data = angInit([
				"currentPage" 	=> $_GET['page'],
				"Subjects" 		=> Subjects::$three_letters,
				"stats"			=> $stats,
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
					"condition" => "id_entity={$Student->id} AND type_entity='STUDENT' AND presence!=2 AND (late is null or late = 0)"
				]);

				$Student->late_count = VisitJournal::count([
					"condition" => "id_entity={$Student->id} AND type_entity='STUDENT' AND presence!=2 AND late > 0"
				]);

				$Student->abscent_count = VisitJournal::count([
					"condition" => "id_entity={$Student->id} AND type_entity='STUDENT' AND presence=2"
				]);

				$denominator = $Student->in_time + $Student->late_count + $Student->abscent_count;
				$Student->abscent_percent = $denominator ? round($Student->abscent_count / $denominator * 100) : 0;
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
					"condition" => "id_teacher={$Teacher->id} AND type_entity='STUDENT' AND presence!=2 AND (late is null or late = 0)"
				]);

				$Teacher->late_count = VisitJournal::count([
					"condition" => "id_teacher={$Teacher->id} AND type_entity='STUDENT' AND presence!=2 AND late > 0"
				]);

				$Teacher->abscent_count = VisitJournal::count([
					"condition" => "id_teacher={$Teacher->id} AND type_entity='STUDENT' AND presence=2"
				]);

				$denominator = $Teacher->in_time + $Teacher->late_count + $Teacher->abscent_count;
				$Teacher->abscent_percent = $denominator ? round($Teacher->abscent_count / $denominator * 100) : 0;
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

			foreach (array_merge(range(9, 11), [Grades::EXTERNAL]) as $grade) {
				$return[$grade]['lesson_count'] = VisitJournal::count([
					"condition" => "type_entity='TEACHER' AND grade=$grade"
				]);

				$return[$grade]['in_time'] = VisitJournal::count([
					"condition" => "grade=$grade AND  type_entity='STUDENT' AND presence!=2 AND (late is null or late = 0)"
				]);

				$return[$grade]['late_count'] = VisitJournal::count([
					"condition" => "grade=$grade AND  type_entity='STUDENT' AND presence!=2 AND late > 0"
				]);

				$return[$grade]['abscent_count'] = VisitJournal::count([
					"condition" => "grade=$grade AND type_entity='STUDENT' AND presence=2"
				]);

				$denominator = $return[$grade]['in_time'] + $return[$grade]['late_count'] + $return[$grade]['abscent_count'];
				$return[$grade]['abscent_percent'] = $denominator ? round($return[$grade]['abscent_count'] / $denominator * 100) : 0;
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
					"condition" => "id_subject=$id_subject AND  type_entity='STUDENT' AND presence!=2 AND (late is null or late = 0)"
				]);

				$return[$id_subject]['late_count'] = VisitJournal::count([
					"condition" => "id_subject=$id_subject AND  type_entity='STUDENT' AND presence!=2 AND late > 0"
				]);

				$return[$id_subject]['abscent_count'] = VisitJournal::count([
					"condition" => "id_subject=$id_subject AND type_entity='STUDENT' AND presence=2"
				]);

				$denominator = $return[$id_subject]['in_time'] + $return[$id_subject]['late_count'] + $return[$id_subject]['abscent_count'];
				$return[$id_subject]['abscent_percent'] = $denominator ? round($return[$id_subject]['abscent_count'] / $denominator * 100) : 0;
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
				"condition" => "entity_type = '" . (isset($_GET['teachers']) ? Teacher::USER_TYPE : Student::USER_TYPE) . "' and ".
					($date_end 	? "STR_TO_DATE(date, '%d.%m.%Y') > '$date_start_formatted' AND STR_TO_DATE(date, '%d.%m.%Y') <= '$date_end_formatted'"
								: "date = '$date_start'"),
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
            # получаем значение текущей страницы
            $page = (!empty($_GET['page'])) ? intval($_GET['page']) : 1;

            # получаем указатель с какого по какое загружать
            $start = ($page - 1) * self::PER_PAGE;
            $end = $start + self::PER_PAGE;

            if ($page == 1) { # текущая неделя
                $date_end = date("d.m.Y", time());
            } else { # первая дата для текущего набора данных
                $date_end = date("d.m.Y", strtotime("last sunday -" . ($start - 1) . " weeks"));
            }

			for ($i = 0; $i <= Payment::timeFromFirst('weeks'); $i++) {
                if ($i < $start) {
                    continue;
                }
                if ($i >= $end) {
                    continue;
                }
				$last_sunday = strtotime("last sunday -$i weeks");
				$date_start = date("d.m.Y", $last_sunday);

				$stats[$date_end] = self::_getPayments($date_start, $date_end);

				$date_end = $date_start;
			}

			return $stats;
		}


		private function getPaymentsByMonths()
		{
            # получаем значение текущей страницы
            $page = (!empty($_GET['page'])) ? intval($_GET['page']) : 1;

            # получаем указатель с какого по какое загружать
            $start = ($page - 1) * self::PER_PAGE;
            $end = $start + self::PER_PAGE;

            if ($page == 1) { # текущий месяц
                $date_end = date("d.m.Y", time());
            } else { # первая дата для текущего набора данных
                $date_end = date("d.m.Y", strtotime("last day of -" . ($start - 1) . " months"));
            }

			for ($i = 1; $i <= Payment::timeFromFirst('months'); $i++) {
                if ($i < $start) {
                    continue;
                }
                if ($i >= $end) {
                    continue;
                }
				$last_day_of_month = strtotime("last day of -$i months");
				$date_start = date("d.m.Y", $last_day_of_month);

				$stats[$date_end] = self::_getPayments($date_start, $date_end);

				$date_end = $date_start;
			}

			return $stats;
		}

		/**
		 * Возвращает данные по годам, с даты первого платежа по текущий год
		 * @return array
		 */
        private function getPaymentsByYears()
        {
            $date_end = date("d.m.Y", time());

            //определяем текущий учебный год
            if (date("j", time()) > 1 && date("n", time()) >= 5) {
                $current_year = date("Y", time());
            } else {
                $current_year = date("Y", time()) - 1;
            }

            for ($i = 0; $i <= Request::timeFromFirst('years') - 1; $i++) {
                $year = $current_year - $i;
                $date_start = date("d.m.Y", mktime(0, 0, 0, 5, 1, $year));

                if ($i == 0) {
                    $date_end = date("d.m.Y");
                } else {
                    $date_end = date("d.m.Y", mktime(0, 0, 0, 5, 1, $year) + (60 * 60 * 24 * 365));
                }

                $stats[$date_end] = self::_getPayments($date_start, $date_end);
                $date_end = $date_start;
            }

            return $stats;
		}


		public function actionPayments()
		{
			$this->setTabTitle(isset($_GET['teachers']) ? 'Детализация по платежам преподавателей' : 'Детализация по платежам');

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
	}
