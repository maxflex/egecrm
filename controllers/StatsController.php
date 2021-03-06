<?php

	// Контроллер
	class StatsController extends Controller
	{
		public $defaultAction = "list";

		const PER_PAGE = 400; # указтель по скольку выводить на страницу

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


		private function _getStats($date_start, $date_end = false, $year = false)
		{
			if ($year) {
				$Contracts = Contract::findAllByYear($year);
			} else {
				$Contracts = Contract::findAll([
					"condition" =>
					$date_end 	? "`date` > '$date_start' AND `date` <= '$date_end'"
					: "date='$date_start'"
				]);
			}

			$Payments = Payment::findAll([
				"condition" => "(entity_type='" . Student::USER_TYPE . "' or  (entity_type='' or entity_type is null)) and ".
					($year ? "`year`={$year}" :
					($date_end 	? "`date` > '$date_start' AND `date` <= '$date_end'"
								: "date = '$date_start'"))
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

                // если договор первый в цепи (и, получается, не первый в году), то добавлять в категорию «изменения»
                if ($Contract->isOriginal()) {
                    $stats['contract_sum_changed'] += $Contract->final_sum;
                    $stats['subjects_plus'] += $Contract->activeSubjectsCount();
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

			$request_condition = "AND adding=0 AND id_status NOT IN (" . implode(',', [RequestStatuses::SPAM, RequestStatuses::DUPLICATE]) . ")";

			$requests_count = Request::count([
				"condition" =>
					$date_end 	? "DATE(date) > '". $date_start ."' AND DATE(date) <= '". $date_end ."' {$request_condition}"
								: "DATE(date) = '". $date_start ."' {$request_condition}"
			]);

			$stats['requests'] = $requests_count;

			$stats['incoming_calls'] = CallStats::sum($date_start, $date_end);

            $teachers_count = Teacher::count([
				"condition" =>
					$date_end 	? "DATE(created_at) > '". $date_start ."' AND DATE(created_at) <= '". $date_end ."' AND `source`=1"
								: "DATE(created_at) = '". $date_start ."' AND `source`=1"
			]);

			$stats['teachers'] = $teachers_count;

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
				$date = date("Y-m-d", strtotime("today -$i day"));
				$stats[$date] = self::_getStats($date);
			}


			uksort($stats, function($a, $b) {
				if ($a > $b) {
					return -1;
				} else
				if ($a < $b) {
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
                $date_end = date("Y-m-d", time());
            } else { # первая дата для текущего набора данных
                $date_end = date("Y-m-d", strtotime("last sunday -" . ($start - 1) . " weeks"));
            }

            for ($i = 0; $i <= Request::timeFromFirst('weeks'); $i++) {
                if ($i < $start) {
                    continue;
                }
                if ($i >= $end) {
                    continue;
                }

                $last_sunday = strtotime("last sunday -$i weeks");
                $date_start = date("Y-m-d", $last_sunday);

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
                $date_end = date("Y-m-d", time());
            } else { # первая дата для текущего набора данных
                $date_end = date("Y-m-d", strtotime("last day of -" . ($start - 1) . " months"));
            }

			for ($i = 1; $i <= Request::timeFromFirst('months'); $i++) {
                if ($i < $start) {
                    continue;
                }
                if ($i >= $end) {
                    continue;
                }
				$last_day_of_month = strtotime("last day of -$i months");
				$date_start = date("Y-m-d", $last_day_of_month);

				$stats[$date_end] = self::_getStats($date_start, $date_end);

				$date_end = $date_start;
			}

			return $stats;
		}

        protected function getByYears()
        {
            $date_end = date("Y-m-d", time());

			//определяем текущий учебный год
			$current_year = end(Years::$all);

            for ($i = 0; $i < count(Years::$all); $i++) {
                $year = $current_year - $i;
                $date_start = date("Y-m-d", mktime(0, 0, 0, 4, 2, $year));
                if ($i == 0) {
                    $date_end = date("Y-m-d");
                } else {
                    $date_end = date("Y-m-d", mktime(0, 0, 0, 4, 1, $year + 1));
                }

                $stats[$date_end] = self::_getStats($date_start, $date_end, $year);

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





		private function _totalVisits($date_start, $date_end = false, $by_year = false)
		{
			if ($by_year) {
				$year = date('Y', strtotime($date_start));
			}
			// профориентация
			$return['payments_prof'] = Payment::count([
				'condition' => ($by_year ? "`year`={$year}" :
					($date_end ? "date > '$date_start' AND date <= '$date_end'" : "date='$date_start'"))
					. " AND category=2"
			]);
			// пробный ЕГЭ
			$return['payments_ege'] = Payment::count([
				'condition' => ($by_year ? "`year`={$year}" :
					($date_end ? "date > '$date_start' AND date <= '$date_end'" : "date='$date_start'"))
					. " AND category=3"
			]);
			// всего занятий без учета отмененных и доп.занятий (доп. занятия вычитаются ниже)
			$return['lesson_count'] = VisitJournal::count([
				"condition" => ($by_year ? "`year`={$year}" : ($date_end ? "lesson_date > '$date_start' AND lesson_date <= '$date_end'" : "lesson_date='$date_start'"))
					. " AND type_entity='TEACHER' AND cancelled=0"
			]);
			// кол-во запланированных занятий
			if ($date_start >= date('Y-m-d') && !$date_end) {
				$return['planned_lesson_count'] = VisitJournal::count([
					"condition" => "lesson_date='$date_start'"
						. " AND cancelled=0 AND " . VisitJournal::PLANNED_CONDITION
				]);
			}
			if ($by_year && $date_end == now(true)) {
				$return['planned_lesson_count'] = VisitJournal::count([
					"condition" => "cancelled=0 AND " . VisitJournal::PLANNED_CONDITION
				]);
			}
			// всего отмененных занятий
			$return['cancelled_count'] = VisitJournal::count([
				"condition" => ($by_year ? "`year`={$year}" : ($date_end ? "lesson_date > '$date_start' AND lesson_date <= '$date_end'" : "lesson_date='$date_start'"))
					. " AND cancelled=1"
			]);
			//всего доп.занятий
			$return['additional_count'] = dbConnection()->query("
				SELECT COUNT(*) AS cnt FROM visit_journal vj
				JOIN groups g ON g.id = vj.id_group
				WHERE " . ($by_year ? "vj.year={$year}" : ($date_end ? "vj.lesson_date > '$date_start' AND vj.lesson_date <= '$date_end'" : "vj.lesson_date='$date_start'"))
					. " AND vj.type_entity='TEACHER' AND vj.cancelled=0 AND g.is_unplanned=1
			")->fetch_object()->cnt;
			$return['planned_additional_count'] = dbConnection()->query("
				SELECT COUNT(*) AS cnt FROM visit_journal vj
				JOIN groups g ON g.id = vj.id_group
				WHERE " . ($by_year ? "vj.year={$year}" : ($date_end ? "vj.lesson_date > '$date_start' AND vj.lesson_date <= '$date_end'" : "vj.lesson_date='$date_start'"))
					. " AND " . VisitJournal::PLANNED_CONDITION . " AND vj.cancelled=0 AND g.is_unplanned=1
			")->fetch_object()->cnt;
			// всего занятий без учета отмененных и доп.занятий
			$return['lesson_count'] = intval($return['lesson_count']) - intval($return['additional_count']);
			VisitJournal::count([
				"condition" => ($by_year ? "`year`={$year}" : ($date_end ? "lesson_date > '$date_start' AND lesson_date <= '$date_end'" : "lesson_date='$date_start'"))
					. " AND (type_entity='TEACHER' OR " . VisitJournal::PLANNED_CONDITION . ") AND cancelled=0"
			]);
			$students_total = VisitJournal::count([
				"condition" => ($by_year ? "`year`={$year}" : ($date_end ? "lesson_date > '$date_start' AND lesson_date <= '$date_end'" : "lesson_date='$date_start'"))
					. " AND type_entity='STUDENT'"
			]);
			$students_skipped = VisitJournal::count([
				"condition" => ($by_year ? "`year`={$year}" : ($date_end ? "lesson_date > '$date_start' AND lesson_date <= '$date_end'" : "lesson_date='$date_start'"))
					. " AND type_entity='STUDENT' AND presence=2"
			]);
			$return['abscent_percent'] = $students_total ? round($students_skipped / $students_total * 100) : 0;
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
				$last_day_of_july = strtotime("-$i years last day of july");
				$date_start = date("Y-m-d", $last_day_of_july);
				$stats[$date_end] = self::_totalVisits($date_start, $date_end, true);
				$date_end = $date_start;
			}
			return $stats;
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
		# ==== ПЛАТЕЖИ ==== #
		# ================= #






		private function _getPayments($date_start, $date_end = false, $year = false)
		{
			$Payments = Payment::findAll([
				"condition" =>
					(isset($_GET['teachers']) ?
						"entity_type='" . Teacher::USER_TYPE . "'" :
						"(entity_type='" . Student::USER_TYPE . "' or  (entity_type='' or entity_type is null))") . " AND " .
					($year ? "`year`={$year}" :
					($date_end 	? "`date` > '$date_start' AND `date` <= '$date_end'"
								: "date = '$date_start'"))
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
				$date = date("Y-m-d", strtotime("today -$i day"));
				$stats[$date] = self::_getPayments($date);
			}

			return array_reverse($stats, true);
		}


		private function getPaymentsByWeeks()
		{
            # получаем значение текущей страницы
            $page = (!empty($_GET['page'])) ? intval($_GET['page']) : 1;

            # получаем указатель с какого по какое загружать
            $start = ($page - 1) * self::PER_PAGE;
            $end = $start + self::PER_PAGE;

            if ($page == 1) { # текущая неделя
                $date_end = date("Y-m-d", time());
            } else { # первая дата для текущего набора данных
                $date_end = date("Y-m-d", strtotime("last sunday -" . ($start - 1) . " weeks"));
            }

			for ($i = 0; $i <= Payment::timeFromFirst('weeks'); $i++) {
                if ($i < $start) {
                    continue;
                }
                if ($i >= $end) {
                    continue;
                }
				$last_sunday = strtotime("last sunday -$i weeks");
				$date_start = date("Y-m-d", $last_sunday);

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
                $date_end = date("Y-m-d", time());
            } else { # первая дата для текущего набора данных
                $date_end = date("Y-m-d", strtotime("last day of -" . ($start - 1) . " months"));
            }

			for ($i = 1; $i <= Payment::timeFromFirst('months'); $i++) {
                if ($i < $start) {
                    continue;
                }
                if ($i >= $end) {
                    continue;
                }
				$last_day_of_month = strtotime("last day of -$i months");
				$date_start = date("Y-m-d", $last_day_of_month);

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
            $date_end = date("Y-m-d", time());

            //определяем текущий учебный год
			$current_year = end(Years::$all);

            for ($i = 0; $i < count(Years::$all); $i++) {
                $year = $current_year - $i;
                $date_start = date("Y-m-d", mktime(0, 0, 0, 5, 1, $year));

                if ($i == 0) {
                    $date_end = date("Y-m-d");
                } else {
                    $date_end = date("Y-m-d", mktime(0, 0, 0, 5, 1, $year) + (60 * 60 * 24 * 365));
                }

                $stats[$date_end] = self::_getPayments($date_start, $date_end, $year);
                $date_end = $date_start;
            }

            return $stats;
		}


		public function actionPayments()
		{
			$mode = isset($_GET['teachers']) ? 'teachers' : 'students';
			$this->setTabTitle($mode == 'teachers' ? 'Платежи преподавателям' : 'Платежи клиентов');

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
				"mode" => $mode,
			]);

			$this->render("payments_{$mode}", [
				"ang_init_data" => $ang_init_data,
				"stats" 		=> $stats,
			]);
		}
	}
