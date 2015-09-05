<?php

	// Контроллер
	class StatsController extends Controller
	{
		public $defaultAction = "list";
		
		// Папка вьюх
		protected $_viewsFolder	= "stats";
		
		// условие, которое не берет в расчет версии договора
		const ZERO_OR_NULL_CONDITION = "AND (id_contract=0 OR id_contract IS NULL)";
				
		
		public function beforeAction() 
		{
			$this->addJs("ng-stats-app");
		}
		
		protected function getByDays()
		{
			$days = 75 + 43;
			
			
			for ($i = $days; $i >= 0; $i--) {
				$date = date("d.m.Y", strtotime("today -$i day"));
				
				$Contracts = Contract::findAll([
					"condition" => "date = '$date' ",
				]);
				
				
				$id_status = $_COOKIE["stats_payment_status"];
				$Payments = Payment::findAll([
					"condition" => "date = '$date'".($id_status ? " AND id_status=$id_status" : "")
				]);
				
				if (!isset($stats[$date])) {
					$stats[$date] = array();
				}
				
				foreach ($Contracts as $index => $Contract) {
					if ($Contract->isOriginal()) {
						$stats[$date]['count']++;
						$stats[$date]['total'] += $Contract->sum;
					}
					
					// если есть версия договора
					$PreviousContract = $Contract->getPreviousVersion();
					if ($PreviousContract) {
						// если сумма увеличилась
						if ($Contract->sum > $PreviousContract->sum && !$PreviousContract->cancelled) {
							$stats[$date]['plus_sum'] += ($Contract->sum - $PreviousContract->sum);
						}
						
						// если сумма уменьшилась
						if ($PreviousContract->sum > $Contract->sum) {
							if (!isset($stats[$date]['plus_sum'])) {
								$stats[$date]['plus_sum'] = 0;
							}
							$stats[$date]['plus_sum'] -= ($PreviousContract->sum - $Contract->sum);
						}
						
						// если был НЕ расторжен и стал расторжен
						if ($Contract->cancelled &&  !$PreviousContract->cancelled) {
							if (!isset($stats[$Contract->cancelled_date]['plus_contracts'])) {
								$stats[$Contract->cancelled_date]['plus_contracts'] = 0;
							}
							$stats[$Contract->cancelled_date]['plus_contracts']--;
							
							if (!isset($stats[$Contract->cancelled_date]['plus_sum'])) {
								$stats[$Contract->cancelled_date]['plus_sum'] = 0;
							}
							
							$stats[$Contract->cancelled_date]['plus_sum'] -= $Contract->sum;
						}
						
						// если расторжен и стал НЕ расторжен
						if (!$Contract->cancelled && $PreviousContract->cancelled) {
							$stats[$date]['plus_contracts']++;
							
							$stats[$date]['plus_sum'] += $Contract->sum;
						}
					}
				}
				
				foreach ($Payments as $Payment) {
					if ($Payment->id_type == PaymentTypes::PAYMENT) {
						if ($Payment->confirmed) {
							$stats[$date]['total_payment'] += $Payment->sum;					
						} else {
							$stats[$date]['total_payment_plus'] += $Payment->sum;
						}
					} else
					if ($Payment->id_type == PaymentTypes::RETURNN) {
						if (!isset($stats[$date]['payment_minus'])) {
							$stats[$date]['payment_minus'] = 0;
						}
						$stats[$date]['payment_minus'] -= $Payment->sum;
					}
				}
				
				$requests_count = Request::count([
					"condition" => "DATE(date) = '". date("Y-m-d", strtotime($date)) ."' AND adding=0"
				]);
				
				$stats[$date]['requests'] = $requests_count;
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
			$weeks = 20;
			
			$date_end = date("d.m.Y", time());
			
			for ($i = 0; $i <= $weeks; $i++) {
				$last_sunday = strtotime("last sunday -$i weeks");
				$date_start = date("d.m.Y", $last_sunday);
				//h1($date_start. " - ".$date_end);
				
				$date_start_formatted 	= date("Y-m-d", strtotime($date_start));
				$date_end_formatted		= date("Y-m-d", strtotime($date_end));
				
				$Contracts = Contract::findAll([
					"condition" => "STR_TO_DATE(date, '%d.%m.%Y') > '$date_start_formatted' AND STR_TO_DATE(date, '%d.%m.%Y') <= '$date_end_formatted' ",
				]);
				
//				echo "STR_TO_DATE(date, '%d.%m.%Y') > '$date_start_formatted' AND STR_TO_DATE(date, '%d.%m.%Y') <= '$date_end_formatted' $zero_or_null_contracts <br>";
				
				$id_status = $_COOKIE["stats_payment_status"];
				$Payments = Payment::findAll([
					"condition" => "STR_TO_DATE(date, '%d.%m.%Y') > '$date_start_formatted' AND STR_TO_DATE(date, '%d.%m.%Y') <= '$date_end_formatted'"
						.($id_status ? " AND id_status=$id_status" : "")
				]);
				
				$stats[$date_end_formatted] = array();
			//	$stats[$date]['count'] = 0;
			//	$stats[$date]['total'] = $total ? $total : 0;
			//	$stats[$date]['total_payment'] = $total_payment ? $total_payment : 0;
				
				foreach ($Contracts as $Contract) {
					if ($Contract->isOriginal()) {
						$stats[$date_end_formatted]['count']++;
						$stats[$date_end_formatted]['total'] += $Contract->sum;
					}
					
					// если есть версия договора
					$PreviousContract = $Contract->getPreviousVersion();
					if ($PreviousContract) {
						// если сумма увеличилась
						if ($Contract->sum > $PreviousContract->sum && !$PreviousContract->cancelled) {
							$stats[$date_end_formatted]['plus_sum'] += ($Contract->sum - $PreviousContract->sum);
						}
						
						// если сумма уменьшилась
						if ($PreviousContract->sum > $Contract->sum) {
							if (!isset($stats[$date_end_formatted]['plus_sum'])) {
								$stats[$date_end_formatted]['plus_sum'] = 0;
							}
							$stats[$date_end_formatted]['plus_sum'] -= ($PreviousContract->sum - $Contract->sum);
						}
						
						// если был НЕ расторжен и стал расторжен
						if ($Contract->cancelled &&  !$PreviousContract->cancelled) {
							if (!isset($stats_additional[$Contract->cancelled_date]['plus_contracts'])) {
								$stats_additional[$Contract->cancelled_date]['plus_contracts'] = 0;
							}
							$stats_additional[$Contract->cancelled_date]['plus_contracts']--;
							
							if (!isset($stats_additional[$Contract->cancelled_date]['plus_sum'])) {
								$stats_additional[$Contract->cancelled_date]['plus_sum'] = 0;
							}
							
							$stats_additional[$Contract->cancelled_date]['plus_sum'] -= $Contract->sum;
						}
						
						// если расторжен и стал НЕ расторжен
						if (!$Contract->cancelled && $PreviousContract->cancelled) {
							$stats[$date_end_formatted]['plus_contracts']++;
							
							$stats[$date_end_formatted]['plus_sum'] += $Contract->sum;
						}
					}
				}
				
				foreach ($Payments as $Payment) {
					if ($Payment->id_type == PaymentTypes::PAYMENT) {
						if ($Payment->confirmed) {
							$stats[$date_end_formatted]['total_payment'] += $Payment->sum;					
						} else {
							$stats[$date_end_formatted]['total_payment_plus'] += $Payment->sum;
						}
					} else
					if ($Payment->id_type == PaymentTypes::RETURNN) {
						if (!isset($stats[$date_end_formatted]['payment_minus'])) {
							$stats[$date_end_formatted]['payment_minus'] = 0;
						}
						$stats[$date_end_formatted]['payment_minus'] -= $Payment->sum;
					}
				}
				
				$requests_count = Request::count([
					"condition" => "DATE(date) > '". $date_start_formatted ."' AND DATE(date) <= '". $date_end_formatted ."'
						AND adding=0"
				]);
				
				$stats[$date_end_formatted]['requests'] = $requests_count;
				
				$date_end = $date_start;
			}
			
			// добавляем расторгнутые
			foreach ($stats_additional as $date => $stat) {
				$D = new DateTime($date);
				$new_date = $D->modify("next sunday")->format("Y-m-d");
				
				if ($new_date > date("Y-m-d")) {
					$new_date = date("Y-m-d");
				}
				
				if (isset($stat['plus_sum'])) {
					if (!isset($stats[$new_date]['plus_sum'])) {
						$stats[$new_date]['plus_sum'] = 0;	
					}
					
					$stats[$new_date]['plus_sum'] += $stat['plus_sum'];
				}
				
				if (isset($stat['plus_contracts'])) {
					if (!isset($stats[$new_date]['plus_contracts'])) {
						$stats[$new_date]['plus_contracts'] = 0;	
					}
					
					$stats[$new_date]['plus_contracts'] += $stat['plus_contracts'];
				}
			}
			
			return $stats;
		}
		
		protected function getByMonths()
		{
			$months = 6;
			
			$date_end = date("d.m.Y", time());
			
			for ($i = 1; $i <= $months; $i++) {
				$last_day_of_month = strtotime("last day of -$i months");
				$date_start = date("d.m.Y", $last_day_of_month);
				// h1($date_start. " - ".$date_end);
				
				$date_start_formatted 	= date("Y-m-d", strtotime($date_start));
				$date_end_formatted		= date("Y-m-d", strtotime($date_end));
				
				$Contracts = Contract::findAll([
					"condition" => "STR_TO_DATE(date, '%d.%m.%Y') > '$date_start_formatted' AND STR_TO_DATE(date, '%d.%m.%Y') <= '$date_end_formatted' ",
				]);
				
				
				$id_status = $_COOKIE["stats_payment_status"];
				$Payments = Payment::findAll([
					"condition" => "STR_TO_DATE(date, '%d.%m.%Y') > '$date_start_formatted' AND STR_TO_DATE(date, '%d.%m.%Y') <= '$date_end_formatted'"
						.($id_status ? " AND id_status=$id_status" : "")
				]);
				
				$stats[$date_end_formatted] = array();
				
				foreach ($Contracts as $Contract) {
					if ($Contract->isOriginal()) {
						$stats[$date_end_formatted]['count']++;
						$stats[$date_end_formatted]['total'] += $Contract->sum;
					}
					
					// если есть версия договора
					$PreviousContract = $Contract->getPreviousVersion();
					if ($PreviousContract) {
						// если сумма увеличилась
						if ($Contract->sum > $PreviousContract->sum && !$PreviousContract->cancelled) {
							$stats[$date_end_formatted]['plus_sum'] += ($Contract->sum - $PreviousContract->sum);
						}
						
						// если сумма уменьшилась
						if ($PreviousContract->sum > $Contract->sum) {
							if (!isset($stats[$date_end_formatted]['plus_sum'])) {
								$stats[$date_end_formatted]['plus_sum'] = 0;
							}
							$stats[$date_end_formatted]['plus_sum'] -= ($PreviousContract->sum - $Contract->sum);
						}
						
						// если был НЕ расторжен и стал расторжен
						if ($Contract->cancelled &&  !$PreviousContract->cancelled) {
							if (!isset($stats_additional[$Contract->cancelled_date]['plus_contracts'])) {
								$stats_additional[$Contract->cancelled_date]['plus_contracts'] = 0;
							}
							$stats_additional[$Contract->cancelled_date]['plus_contracts']--;
							
							if (!isset($stats_additional[$Contract->cancelled_date]['plus_sum'])) {
								$stats_additional[$Contract->cancelled_date]['plus_sum'] = 0;
							}
							
							$stats_additional[$Contract->cancelled_date]['plus_sum'] -= $Contract->sum;
						}
						
						// если расторжен и стал НЕ расторжен
						if (!$Contract->cancelled && $PreviousContract->cancelled) {
							$stats[$date_end_formatted]['plus_contracts']++;
							
							$stats[$date_end_formatted]['plus_sum'] += $Contract->sum;
						}
					}
				}
				
				foreach ($Payments as $Payment) {
					if ($Payment->id_type == PaymentTypes::PAYMENT) {
						if ($Payment->confirmed) {
							$stats[$date_end_formatted]['total_payment'] += $Payment->sum;					
						} else {
							$stats[$date_end_formatted]['total_payment_plus'] += $Payment->sum;
						}
					} else
					if ($Payment->id_type == PaymentTypes::RETURNN) {
						if (!isset($stats[$date_end_formatted]['payment_minus'])) {
							$stats[$date_end_formatted]['payment_minus'] = 0;
						}
						$stats[$date_end_formatted]['payment_minus'] -= $Payment->sum;
					}
				}
				
				$requests_count = Request::count([
					"condition" => "DATE(date) > '". $date_start_formatted ."' AND DATE(date) <= '". $date_end_formatted ."'
						AND adding=0"
				]);
				
				$stats[$date_end_formatted]['requests'] = $requests_count;
				
				$date_end = $date_start;
			}
			
			// добавляем расторгнутые
			foreach ($stats_additional as $date => $stat) {
				$D = new DateTime($date);
				$new_date = $D->modify("last day of next month")->format("Y-m-d");
				
				if ($new_date > date("Y-m-d")) {
					$new_date = date("Y-m-d");
				}
				
				if (isset($stat['plus_sum'])) {
					if (!isset($stats[$new_date]['plus_sum'])) {
						$stats[$new_date]['plus_sum'] = 0;	
					}
					
					$stats[$new_date]['plus_sum'] += $stat['plus_sum'];
				}
				
				if (isset($stat['plus_contracts'])) {
					if (!isset($stats[$new_date]['plus_contracts'])) {
						$stats[$new_date]['plus_contracts'] = 0;	
					}
					
					$stats[$new_date]['plus_contracts'] += $stat['plus_contracts'];
				}
			}
			return $stats;
		}
		
		protected function getByYears()
		{
			$years = 1;
			
			$date_end = date("d.m.Y", time());
			
			for ($i = 1; $i <= $years; $i++) {
				$last_day_of_july = strtotime("last day of july -$i year");
				$date_start = date("d.m.Y", $last_day_of_july);
				// h1($date_start. " - ".$date_end);
				
				$date_start_formatted 	= date("Y-m-d", strtotime($date_start));
				$date_end_formatted		= date("Y-m-d", strtotime($date_end));
				
				$Contracts = Contract::findAll([
					"condition" => "STR_TO_DATE(date, '%d.%m.%Y') > '$date_start_formatted' AND STR_TO_DATE(date, '%d.%m.%Y') <= '$date_end_formatted' "
				]);
				
				$id_status = $_COOKIE["stats_payment_status"];
				$Payments = Payment::findAll([
					"condition" => "STR_TO_DATE(date, '%d.%m.%Y') > '$date_start_formatted' AND STR_TO_DATE(date, '%d.%m.%Y') <= '$date_end_formatted'"
						.($id_status ? " AND id_status=$id_status" : "")
				]);
				
				$stats[$date_end_formatted] = array();
				
				foreach ($Contracts as $Contract) {
					if ($Contract->isOriginal()) {
						$stats[$date_end_formatted]['count']++;
						$stats[$date_end_formatted]['total'] += $Contract->sum;
					}
					
					// если есть версия договора
					$PreviousContract = $Contract->getPreviousVersion();
					if ($PreviousContract) {
						// если сумма увеличилась
						if ($Contract->sum > $PreviousContract->sum && !$PreviousContract->cancelled) {
							$stats[$date_end_formatted]['plus_sum'] += ($Contract->sum - $PreviousContract->sum);
						}
						
						// если сумма уменьшилась
						if ($PreviousContract->sum > $Contract->sum) {
							if (!isset($stats[$date_end_formatted]['plus_sum'])) {
								$stats[$date_end_formatted]['plus_sum'] = 0;
							}
							$stats[$date_end_formatted]['plus_sum'] -= ($PreviousContract->sum - $Contract->sum);
						}
						
						// если был НЕ расторжен и стал расторжен
						if ($Contract->cancelled &&  !$PreviousContract->cancelled) {
							if (!isset($stats_additional[$Contract->cancelled_date]['plus_contracts'])) {
								$stats_additional[$Contract->cancelled_date]['plus_contracts'] = 0;
							}
							$stats_additional[$Contract->cancelled_date]['plus_contracts']--;
							
							if (!isset($stats_additional[$Contract->cancelled_date]['plus_sum'])) {
								$stats_additional[$Contract->cancelled_date]['plus_sum'] = 0;
							}
							
							$stats_additional[$Contract->cancelled_date]['plus_sum'] -= $Contract->sum;
						}
						
						// если расторжен и стал НЕ расторжен
						if (!$Contract->cancelled && $PreviousContract->cancelled) {
							$stats[$date_end_formatted]['plus_contracts']++;
							
							$stats[$date_end_formatted]['plus_sum'] += $Contract->sum;
						}
					}
				}
				
				foreach ($Payments as $Payment) {
					if ($Payment->id_type == PaymentTypes::PAYMENT) {
						if ($Payment->confirmed) {
							$stats[$date_end_formatted]['total_payment'] += $Payment->sum;					
						} else {
							$stats[$date_end_formatted]['total_payment_plus'] += $Payment->sum;
						}
					} else
					if ($Payment->id_type == PaymentTypes::RETURNN) {
						if (!isset($stats[$date_end_formatted]['payment_minus'])) {
							$stats[$date_end_formatted]['payment_minus'] = 0;
						}
						$stats[$date_end_formatted]['payment_minus'] -= $Payment->sum;
					}
				}
				
				$requests_count = Request::count([
					"condition" => "DATE(date) > '". $date_start_formatted ."' AND DATE(date) <= '". $date_end_formatted ."'
						AND adding=0"
				]);
				
				$stats[$date_end_formatted]['requests'] = $requests_count;
				
				$date_end = $date_start;
			}
			
			// добавляем расторгнутые
			foreach ($stats_additional as $date => $stat) {
				$D = new DateTime($date);
				$new_date = $D->modify("last day of july")->format("Y-m-d");
				
				if ($new_date > date("Y-m-d")) {
					$new_date = date("Y-m-d");
				}
				
				$new_date = date("Y-m-d");
				if (isset($stat['plus_sum'])) {
					if (!isset($stats[$new_date]['plus_sum'])) {
						$stats[$new_date]['plus_sum'] = 0;	
					}
					
					$stats[$new_date]['plus_sum'] += $stat['plus_sum'];
				}
				
				if (isset($stat['plus_contracts'])) {
					if (!isset($stats[$new_date]['plus_contracts'])) {
						$stats[$new_date]['plus_contracts'] = 0;	
					}
					
					$stats[$new_date]['plus_contracts'] += $stat['plus_contracts'];
				}
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
			
			$this->render("list", [
				"stats" => $stats
			]);
		}
		
	}