<?php

	// Контроллер
	class StatsController extends Controller
	{
		public $defaultAction = "list";
		
		// Папка вьюх
		protected $_viewsFolder	= "stats";
		
		// условие, которое не берет в расчет версии договора
		const ZERO_OR_NULL_CONDITION = "AND (id_contract=0 OR id_contract IS NULL)";
				
		protected function getByDays()
		{
			$days = 75;
			
			
			for ($i = $days; $i >= 0; $i--) {
				$date = date("d.m.Y", strtotime("today -$i day"));
				
				$Contracts = Contract::findAll([
					"condition" => "date = '$date' ".self::ZERO_OR_NULL_CONDITION,
				]);
				
				$Payments = Payment::findAll([
					"condition" => "date = '$date'"
				]);
				
				$stats[$date] = array();
			//	$stats[$date]['count'] = 0;
			//	$stats[$date]['total'] = $total ? $total : 0;
			//	$stats[$date]['total_payment'] = $total_payment ? $total_payment : 0;
				
				foreach ($Contracts as $Contract) {
					$stats[$date]['count']++;
					
					$stats[$date]['total'] += $Contract->sum;	
				}
				
				foreach ($Payments as $Payment) {
					if ($Payment->id_type == PaymentTypes::PAYMENT) {
						$stats[$date]['total_payment'] += $Payment->sum;					
					} else
					if ($Payment->id_type == PaymentTypes::RETURNN) {
					//	$total_payment -= $Payment->sum;
					}
				}
			}
			
			
			
			return array_reverse($stats);
		}
		
		protected function getByWeeks()
		{
			$weeks = 10;
			
			$date_end = date("d.m.Y", time());
			
			for ($i = 0; $i <= $weeks; $i++) {
				$last_sunday = strtotime("last sunday -$i weeks");
				$date_start = date("d.m.Y", $last_sunday);
				//h1($date_start. " - ".$date_end);
				
				$date_start_formatted 	= date("Y-m-d", strtotime($date_start));
				$date_end_formatted		= date("Y-m-d", strtotime($date_end));
				
				$Contracts = Contract::findAll([
					"condition" => "STR_TO_DATE(date, '%d.%m.%Y') > '$date_start_formatted' AND STR_TO_DATE(date, '%d.%m.%Y') <= '$date_end_formatted' ".self::ZERO_OR_NULL_CONDITION,
				]);
				
//				echo "STR_TO_DATE(date, '%d.%m.%Y') > '$date_start_formatted' AND STR_TO_DATE(date, '%d.%m.%Y') <= '$date_end_formatted' $zero_or_null_contracts <br>";
				
				$Payments = Payment::findAll([
					"condition" => "STR_TO_DATE(date, '%d.%m.%Y') > '$date_start_formatted' AND STR_TO_DATE(date, '%d.%m.%Y') <= '$date_end_formatted'"
				]);
				
				$stats[$date_end_formatted] = array();
			//	$stats[$date]['count'] = 0;
			//	$stats[$date]['total'] = $total ? $total : 0;
			//	$stats[$date]['total_payment'] = $total_payment ? $total_payment : 0;
				
				foreach ($Contracts as $Contract) {
					$stats[$date_end_formatted]['count']++;
					
					$stats[$date_end_formatted]['total'] += $Contract->sum;	
				}
				
				foreach ($Payments as $Payment) {
					if ($Payment->id_type == PaymentTypes::PAYMENT) {
						$stats[$date_end_formatted]['total_payment'] += $Payment->sum;					
					} else
					if ($Payment->id_type == PaymentTypes::RETURNN) {
					//	$total_payment -= $Payment->sum;
					}
				}
				
				
				$date_end = $date_start;
			}
			
			return $stats;
		}
		
		protected function getByMonths()
		{
			$months = 4;
			
			$date_end = date("d.m.Y", time());
			
			for ($i = 1; $i <= $months; $i++) {
				$last_day_of_month = strtotime("last day of -$i months");
				$date_start = date("d.m.Y", $last_day_of_month);
				// h1($date_start. " - ".$date_end);
				
				$date_start_formatted 	= date("Y-m-d", strtotime($date_start));
				$date_end_formatted		= date("Y-m-d", strtotime($date_end));
				
				$Contracts = Contract::findAll([
					"condition" => "STR_TO_DATE(date, '%d.%m.%Y') > '$date_start_formatted' AND STR_TO_DATE(date, '%d.%m.%Y') <= '$date_end_formatted' ".self::ZERO_OR_NULL_CONDITION,
				]);
				
				$Payments = Payment::findAll([
					"condition" => "STR_TO_DATE(date, '%d.%m.%Y') > '$date_start_formatted' AND STR_TO_DATE(date, '%d.%m.%Y') <= '$date_end_formatted'"
				]);
				
				$stats[$date_end_formatted] = array();
				
				foreach ($Contracts as $Contract) {
					$stats[$date_end_formatted]['count']++;
					
					$stats[$date_end_formatted]['total'] += $Contract->sum;	
				}
				
				foreach ($Payments as $Payment) {
					if ($Payment->id_type == PaymentTypes::PAYMENT) {
						$stats[$date_end_formatted]['total_payment'] += $Payment->sum;					
					} else
					if ($Payment->id_type == PaymentTypes::RETURNN) {
					//	$total_payment -= $Payment->sum;
					}
				}
				
				
				$date_end = $date_start;
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
					"condition" => "STR_TO_DATE(date, '%d.%m.%Y') > '$date_start_formatted' AND STR_TO_DATE(date, '%d.%m.%Y') <= '$date_end_formatted' ".self::ZERO_OR_NULL_CONDITION
				]);
				
				$Payments = Payment::findAll([
					"condition" => "STR_TO_DATE(date, '%d.%m.%Y') > '$date_start_formatted' AND STR_TO_DATE(date, '%d.%m.%Y') <= '$date_end_formatted'"
				]);
				
				$stats[$date_end_formatted] = array();
				
				foreach ($Contracts as $Contract) {
					$stats[$date_end_formatted]['count']++;
					
					$stats[$date_end_formatted]['total'] += $Contract->sum;	
				}
				
				foreach ($Payments as $Payment) {
					if ($Payment->id_type == PaymentTypes::PAYMENT) {
						$stats[$date_end_formatted]['total_payment'] += $Payment->sum;					
					} else
					if ($Payment->id_type == PaymentTypes::RETURNN) {
					//	$total_payment -= $Payment->sum;
					}
				}
				
				
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
			
			$this->render("list", [
				"stats" => $stats
			]);
		}
		
	}