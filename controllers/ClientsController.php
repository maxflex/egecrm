<?php

	// Контроллер
	class ClientsController extends Controller
	{
		public $defaultAction = "list";
		
		// Папка вьюх
		protected $_viewsFolder	= "clients";
		
		public function beforeAction()
		{
			$this->addJs("ng-clients-app");
		}
		
		public function actionList()
		{
			// не надо панель рисовать
			$this->_custom_panel = true;
			
			$this->addJs("bootstrap-select");
			$this->addCss("bootstrap-select");
			
			$ang_init_data = angInit([
				'currentPage'	=> $_GET['page'] ? $_GET['page'] : 1,
			]);
			
			$this->render("list", [
				"sort"		=> $_GET['sort'],
				"ang_init_data" => $ang_init_data,
			]);
		}
		
/*
		public function actionAjaxGetStudents()
		{
			$Students = Student::getWithContract();
			
			foreach ($Students as &$Student) {
				$Student->Contract 	= $Student->getLastContract();
				$Student->CurrentYearContract = $Student->getCurrentYearLastContract();
				
				//
				foreach ($Student->CurrentYearContract->subjects as $subject) {
					$Student->sc[$subject['status']] += $subject['count'] + $subject['count2'];
				}
				
				foreach (range(3, 1) as $status) {
					if ($Student->sc[$status]) {
						$Student->subject_count[] = [
							'status' 	=> $status,
							'count'		=> $Student->sc[$status],
						];
					}
				}
				
				// у данного ученика больше не планируется занятий (серьенькие точки в посещаемости)
				// текущая версия договора последней цепи договоров содержит хотя бы 1 желтый или зеленый предмет
				$Student->red_circle = !$Student->hasFutureLessons() && ($Student->sc[3] || $Student->sc[2]);
												
				
				$Student->User = User::find(["condition" => "id_entity=" . $Student->id]);
				
				$date_formatted = new DateTime($Student->Contract->date);
				$Student->date_formatted = $date_formatted->format("Y-m-d");
 			}
 			
 			returnJsonAng($Students);
		}
*/
		
		public function actionAjaxGetStudents()
		{
			extract($_POST);
			
			returnJsonAng(
				Student::getData($page)
			);
		}
	}