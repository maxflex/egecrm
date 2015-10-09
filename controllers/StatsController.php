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
		
		
		
		
				
		
		
		
		# ============================= #
		# ==== ОСНОВНАЯ СТАТИСТИКА ==== #
		# ============================= #
		
		
		

		
		
		private function _getStats($date_start, $date_end = false)
		{
			$date_start_formatted 	= date("Y-m-d", strtotime($date_start));
			$date_end_formatted		= date("Y-m-d", strtotime($date_end));
			
			$Contracts = Contract::findAll([
				"condition" => 
					$date_end 	? "STR_TO_DATE(date, '%d.%m.%Y') > '$date_start_formatted' AND STR_TO_DATE(date, '%d.%m.%Y') <= '$date_end_formatted' "
								: "date = '$date_start'"
			]);
			
			$Payments = Payment::findAll([
				"condition" => 
					$date_end 	? "STR_TO_DATE(date, '%d.%m.%Y') > '$date_start_formatted' AND STR_TO_DATE(date, '%d.%m.%Y') <= '$date_end_formatted'"
								: "date = '$date_start'"
			]);
			
			foreach ($Contracts as $index => $Contract) {
				if ($Contract->isOriginal()) {
					$stats['contract_new']++;
					// сумма заключенных дагаваров
					$stats['contract_sum_new'] += $Contract->sum;
					continue; # если договор оригинальный, у него не может быть предыдущих версий
				}
				
				// если есть версия договора
				$PreviousContract = $Contract->getPreviousVersion();
				if ($PreviousContract) {
					// если сумма увеличилась
					if ($Contract->sum > $PreviousContract->sum && !$PreviousContract->cancelled) {
						$stats['contract_sum_changed'] += ($Contract->sum - $PreviousContract->sum);
					}
					
					// если сумма уменьшилась
					if ($PreviousContract->sum > $Contract->sum && !$PreviousContract->cancelled) {
						if (!isset($stats['contract_sum_changed'])) {
							$stats['contract_sum_changed'] = 0;
						}
						$stats['contract_sum_changed'] -= ($PreviousContract->sum - $Contract->sum);
					}
					
					// если был НЕ расторжен и стал расторжен
					if ($Contract->cancelled &&  !$PreviousContract->cancelled) {
						// кол-во расторгнутых
						$stats['contract_cancelled']++;
						
						// сумма расторгнутых
						$stats['contract_sum_cancelled'] += $Contract->sum;
					}
					
					
					// если расторжен и стал НЕ расторжен
					if (!$Contract->cancelled && $PreviousContract->cancelled) {
						// кол-во реанимированых
						$stats['contract_restored']++;
						
						// сумма реанимированых
						$stats['contract_sum_restored'] += $Contract->sum;
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
				$last_day_of_july = strtotime("last day of july -$i year");
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
		# ==== УЧЕНИКИ ==== #
		# ================= #
		
		
		
		
		
		
		private function _studentVisits($date_start, $date_end = false)
		{
			$return['visit_count'] = VisitJournal::count([
				"condition" => ($date_end ? "lesson_date > '$date_start' AND lesson_date <= '$date_end'" : "lesson_date='$date_start'")
					. " AND type_entity='STUDENT' AND presence!=2"
			]);
			
			$return['late_count'] = VisitJournal::count([
				"condition" => ($date_end ? "lesson_date > '$date_start' AND lesson_date <= '$date_end'" : "lesson_date='$date_start'")
					. " AND type_entity='STUDENT' AND presence=1 AND late > 0"
			]);
			
			$return['abscent_count'] = VisitJournal::count([
				"condition" => ($date_end ? "lesson_date > '$date_start' AND lesson_date <= '$date_end'" : "lesson_date='$date_start'")
					. " AND type_entity='STUDENT' AND presence=2"
			]);
			
			
			if (!$return['abscent_count']) {
				$return['late_percent'] = 0;
			} else {
				$return['late_percent'] = round($return['abscent_count'] / $return['visit_count'] * 100);
			}
			
			return $return;
		}
		
		
		private function getStudentVisitsByDays()
		{
			$page = $_GET['page'];
			if (!$page) {
				$page = 1;
			}
			
			$start = ($page - 1) * self::PER_PAGE;

			for ($i = (self::PER_PAGE * $page); $i >= $start + ($page > 1 ? 1 : 0); $i--) {
				$date = date("Y-m-d", strtotime("today -$i day"));
				
				// show today only if there are lessons present
				if ($date == date("Y-m-d")) {
					// if it's today and there's no lessons, don't show the empty line
					if (!VisitJournal::find(["condition" => "lesson_date='$date'"])) {
						continue;
					}
				}
								
				$stats[$date] = self::_studentVisits($date);
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
		
		
		private function getStudentVisitsByWeeks()
		{
			$date_end = date("Y-m-d", time());
			
			for ($i = 0; $i <= VisitJournal::fromFirstLesson('weeks'); $i++) {
				$last_sunday = strtotime("last sunday -$i weeks");
				$date_start = date("Y-m-d", $last_sunday);
				
				$stats[$date_end] = self::_studentVisits($date_start, $date_end);
				
				$date_end = $date_start;				
			}
			
			return $stats;
		}

		
		private function getStudentVisitsByMonths()
		{
			$date_end = date("Y-m-d", time());
			
			for ($i = 1; $i <= VisitJournal::fromFirstLesson('months'); $i++) {
				$last_day_of_month = strtotime("last day of -$i months");
				$date_start = date("Y-m-d", $last_day_of_month);
				
				$stats[$date_end] = self::_studentVisits($date_start, $date_end);
				
				$date_end = $date_start;
			}
			
			return $stats;
		}
		
		private function getStudentVisitsByYears()
		{
			$date_end = date("Y-m-d", time());
			
			for ($i = 1; $i <= VisitJournal::fromFirstLesson('years'); $i++) {
				$last_day_of_july = strtotime("last day of july -$i year");
				$date_start = date("Y-m-d", $last_day_of_july);
				
				$stats[$date_end] = self::_studentVisits($date_start, $date_end);
				
				$date_end = $date_start;
			}
			
			return $stats;
		}
		
		
		public function actionTotalVisitStudents()
		{
			$this->setTabTitle("Общая посещаемость");
			
			switch ($_GET["group"]) {
				case "w": {
					$stats = self::getStudentVisitsByWeeks();
					break;
				}
				case "m": {
					$stats = self::getStudentVisitsByMonths();
					break;
				}
				case "y": {
					$stats = self::getStudentVisitsByYears();
					break;
				}
				default: {
					$stats = self::getStudentVisitsByDays();
					break;
				}
			}
			
			$ang_init_data = angInit([
				"currentPage" => $_GET['page'],
			]);
			
			$this->render("total_visit_students", [
				"ang_init_data" => $ang_init_data,
				"stats" 		=> $stats,
			]);
		}
		
		
		
		
		
		
		
		# ================= #
		# ==== ПРЕПОДЫ ==== #
		# ================= #
		
		
		
		
		
		
		
		public function actionTotalVisitTeachers()
		{
			$this->setTabTitle("Общая посещаемость по преподавателям");
			
			$Teachers = Teacher::getActiveGroups();
			
			foreach ($Teachers as $index => &$Teacher) {
				$teacher_group_ids = Group::getIds([
					"condition" => "id_teacher=" . $Teacher->id,
				]);
				$teacher_group_ids = implode(",", $teacher_group_ids);
				
				$Teacher->visit_count = VisitJournal::count([
					"condition" => "id_group IN ({$teacher_group_ids}) AND type_entity='STUDENT' AND presence!=2"
				]);
				
				// если у учителя не было занятий, не показываем его
				if (!$Teacher->visit_count) {
					unset($Teachers[$index]);
					continue;
				}
				
				$Teacher->late_count = VisitJournal::count([
					"condition" => "id_group IN ({$teacher_group_ids}) AND type_entity='STUDENT' AND presence=1 AND late > 0"
				]);
				
				$Teacher->abscent_count = VisitJournal::count([
					"condition" => "id_group IN ({$teacher_group_ids}) AND type_entity='STUDENT' AND presence=2"
				]);
				
				if (!$Teacher->abscent_count) {
					$Teacher->late_percent = 0;
				} else {
					$Teacher->late_percent = round($Teacher->abscent_count / $Teacher->visit_count * 100);
				}	
			}
			
			$this->render("total_visit_teachers", [
				"ang_init_data" => $ang_init_data,
				"Teachers" 		=> $Teachers,
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
				"condition" => 
					$date_end 	? "STR_TO_DATE(date, '%d.%m.%Y') > '$date_start_formatted' AND STR_TO_DATE(date, '%d.%m.%Y') <= '$date_end_formatted'"
								: "date = '$date_start'"
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
		
	}