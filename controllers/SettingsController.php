<?php

	// Контроллер
	class SettingsController extends Controller
	{
		public $defaultAction = "vocations";

		// Папка вьюх
		protected $_viewsFolder	= "settings";
		
		public function beforeAction()
		{
			$this->addCss("bootstrap-select");
			$this->addJs("ng-settings-app, bootstrap-select");
		}
		
		public function actionCabinets()
		{
			// Выводить только кабинеты, в которых есть хотя бы 1 группа.
			$result = dbConnection()->query("SELECT cabinet FROM groups GROUP BY cabinet");
			
			$cabinet_ids = [];
			while ($row = $result->fetch_object()) {
				if (!empty($row->cabinet)) {
					$cabinet_ids[] = $row->cabinet;
				}
			}
			
			$Cabinets = Cabinet::findAll([
				"condition" => "id IN (". implode(",", $cabinet_ids) .")",
				"order"		=> "ABS(number) ASC"
			]);
			
			foreach ($Cabinets as &$Cabinet) {
				$Cabinet->freetime = Cabinet::getFreetime(0, $Cabinet->id);
			}

			$ang_init_data = angInit([
				"Cabinets" 	=> $Cabinets,
				"Branches"	=> Branches::getBranches(),
			]);
			
			
			$this->setTabTitle("Свободное время кабинетов");
			$this->render("cabinets_freetime", [
				"ang_init_data" => $ang_init_data,
 			]);
		}
		
		public function actionVocations()
		{
			// не надо панель рисовать
			$this->_custom_panel = true;
			
			$id_group = $_GET['id'];		
			
			$Group = new Group([
				"id" => 0,
			]);
			
			$Group->Schedule = $Group->getSchedule();
			
			$ang_init_data = angInit([
				"Group" 	=> $Group,
				"Subjects"	=> Subjects::$three_letters,
				"exam_days" => ExamDay::getData(),
			]);
			
			$this->render("vocations", [
				"Group"			=> $Group,
				"ang_init_data" => $ang_init_data,	
			]);
		}

		/**
		 * Получить всех учеников, не принадлежащих группам
		 
		 1) Получить всех учеников с договорами
		 2) Отсортировать и выбрать только тех, кто не принадлежит никакой группе
		 3) Отсортировать учеников по классу
		 4) 
		 
		 	$Groups
		 		класс
		 		предмет
		 		кол-во учеников
		 		ученики
		 */
		public function actionAjaxStudentsWithNoGroup()
		{
			$Students = Student::getWithContract(true);
			
			// Добавляем догавары к студентам
			foreach ($Students as $index => $Student) {
				$Students[$index]->Contract 	= $Student->getLastContract();
				
				foreach ($Student->branches as $id_branch) {
					if (!$id_branch) {
						continue;
					}
					$Students[$index]->branch_short[$id_branch] = Branches::getShortColoredById($id_branch);
				}
			}
			
			// Формируем по классам, всех студентов, кто не принадлежит группам
			foreach ($Students as $Student) {
// 				if (!$Student->inAnyOtherGroup()) {
					$GroupsGrade[$Student->Contract->grade][] = $Student;	
// 				}
			}
			

			// Формируем по предметам
			foreach ($GroupsGrade as $grade => $GS)
			{
				foreach ($GS as $Student) {
					foreach ($Student->Contract->subjects as $subject) {
						foreach ($Student->branches as $id_branch) {
							if (!$id_branch) {
								continue;
							}
							$GroupStudents[$grade][$subject['id_subject']][$id_branch][] = $Student;
						}
					}
				}
			}
			
			// Формируем отдельные группы из массива (до примыкания к филиалу)
			foreach ($GroupStudents as $grade => $SubjectBranch) {
				foreach ($SubjectBranch as $subject => $Branch) {
					foreach ($Branch as $branch => $BS) {
						foreach ($BS as $index => $Student) {
							if ($Student->inOtherSubjectGroup($subject)) {
								unset($BS[$index]);
							}
						}
						// если есть ученики в группе
						if (count($BS)) {
							$GroupsFull[] = [
								"grade"		=> $grade,
								"subject"	=> $subject,
								"branch"	=> $branch,
								"branch_svg"=> Branches::getName($branch),
								"count"		=> count($BS),
								"Students"	=> $BS,
							];	
						}
					}
				}
			}
			
			// Сортируем по количеству учеников
			usort($GroupsFull, function($a, $b) {
				return ($a['count'] > $b['count'] ? -1 : 1);
			});
			
			// Присваеваем ученика только к максимально нагруженному филиалу, если указано несколько
			foreach ($GroupsGrade as $grade => $GS)
			{
				foreach ($GS as $Student) {
					foreach ($Student->Contract->subjects as $subject) {
						if (count($Student->branches) > 1) { 
							// выявляем максимально нагруженный филиал
							$max_count = -1;
							foreach ($Student->branches as $id_branch) {
								$grade_subject_branch_students_count = count($GroupStudents[$grade][$subject['id_subject']][$id_branch]);
								if ($grade_subject_branch_students_count > $max_count) {
									$max_branch	= $id_branch;
									$max_count 	= $grade_subject_branch_students_count;
								}
							}
							
							// после выявления максимально нагруженного филиала удаляем учеников
							// изо всех филиалов, кроме найденного (максимально нагруженного)
							foreach ($Student->branches as $id_branch) {
								if ($id_branch != $max_branch) {
									$BranchStudents = $GroupStudents[$grade][$subject['id_subject']][$id_branch];
									foreach ($BranchStudents as $index => $BranchStudent) {
										if ($BranchStudent->id == $Student->id) {
											unset($GroupStudents[$grade][$subject['id_subject']][$id_branch][$index]);
										}
									}	
								}
							}
						}
					}
				}
			}
			
			// Формируем отдельные группы из массива
			foreach ($GroupStudents as $grade => $SubjectBranch) {
				foreach ($SubjectBranch as $subject => $Branch) {
					foreach ($Branch as $branch => $BS) {
						foreach ($BS as $index => $Student) {
							if ($Student->inOtherSubjectGroup($subject)) {
								unset($BS[$index]);
							}
						}
						// если есть ученики в группе
						if (count($BS)) {
							$Groups[] = [
								"grade"		=> $grade,
								"subject"	=> $subject,
								"branch"	=> $branch,
								"branch_svg"=> Branches::getName($branch),
								"count"		=> count($BS),
								"Students"	=> $BS,
							];	
						}
					}
				}
			}
			
			// Сортируем по количеству учеников
			usort($Groups, function($a, $b) {
				return ($a['count'] > $b['count'] ? -1 : 1);
			});
			
			returnJsonAng([
				"GroupsShort"	=> $Groups,
				"GroupsFull"	=> $GroupsFull,
			]);
		}
		
		public function ___DEPRICATED___actionAjaxGetStudents()
		{
			$Students = Student::getWithContract(true);
			
			foreach ($Students as $index => &$Student) {
				$Student->Contract 	= $Student->getLastContract();
				
				if ($Student->Contract->cancelled) {
					unset($Students[$index]);
					continue;
				}
				
				foreach ($Student->branches as $id_branch) {
					if (!$id_branch) {
						continue;
					}
					$Student->branch_short[$id_branch] = Branches::getShortColoredById($id_branch);
				}
			}
			
			// сортировка по номеру договора
			usort($Students, function($a, $b) {
				return ($a->Contract->id < $b->Contract->id ? -1 : 1);
			});
			
			returnJsonAng($Students);
		}
		
		public function actionAjaxAddCabinet()
		{
			Cabinet::add($_POST);
		}
		
		public function actionAjaxRemoveCabinet()
		{
			extract($_POST);
			
			$Cabinet = Cabinet::findAll([
				"condition" => "id_branch=$id_branch",
				"limit"		=> "$index, 1"
			])[0];
			
			$Cabinet->delete();
		}
	}