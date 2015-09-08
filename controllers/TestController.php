<?php

	// Контроллер
	class TestController extends Controller
	{
		public $defaultAction = "test";

		// Папка вьюх
		protected $_viewsFolder	= "test";
		
		public function actionBeforeAction()
		{
// 			$this->setTabTitle("Тест");
//			$this->addJs("ng-test-app");
		}
		
		public function actionSession()
		{
			preType($_SESSION);
		}
		
		public function actionTransferFreetime()
		{
			$Students = Student::getWithContract(true);
			
			foreach ($Students as $Student) {
				$Student->freetime = $Student->getFreetime();
				
				foreach ($Student->freetime as $Freetime) {
					// понедельник - пятница
					if ($Freetime->day >= 1 && $Freetime->day < 6) {
						if ($Freetime->start <= "17:00" && $Freetime->end >= "17:00") {
							self::addFreetime($Student, $Freetime->day, "16:15");
						}
						if ($Freetime->start <= "19:00" && $Freetime->end >= "19:00") {
							self::addFreetime($Student, $Freetime->day, "18:40");
						}
					} else {
						if (($Freetime->start <= "11:00" && $Freetime->end >= "11:00") 
						|| ($Freetime->start <= "12:00" && $Freetime->end >= "12:00")
						|| ($Freetime->start <= "13:00" && $Freetime->end >= "13:00")) {
							self::addFreetime($Student, $Freetime->day, "11:00");
						}
						if (($Freetime->start <= "14:00" && $Freetime->end >= "14:00") 
						|| ($Freetime->start <= "15:00" && $Freetime->end >= "15:00")
						|| ($Freetime->start <= "16:00" && $Freetime->end >= "16:00")) {
							self::addFreetime($Student, $Freetime->day, "13:30");
						}
						if (($Freetime->start <= "17:00" && $Freetime->end >= "17:00") 
						|| ($Freetime->start <= "18:00" && $Freetime->end >= "18:00")) {
							self::addFreetime($Student, $Freetime->day, "16:00");
						}
						if (($Freetime->start <= "19:00" && $Freetime->end >= "19:00") 
						|| ($Freetime->start <= "20:00" && $Freetime->end >= "20:00") 
						|| ($Freetime->start <= "21:00" && $Freetime->end >= "21:00")) {
							self::addFreetime($Student, $Freetime->day, "18:30");
						}
					}
				}
			}
		}
		
		private static function addFreetime($Student, $day, $time) {
			foreach ($Student->branches as $id_branch) {
				if (!$id_branch) {
					continue;
				}
				$FreetimeNew = new FreetimeNew([
					"id_student"	=> $Student->id,
					"id_branch"		=> $id_branch,
					"day"			=> $day,
					"time"			=> $time,
				]);
				$FreetimeNew->save();
			}
		}
		
		public function actionBranchColor()
		{
			$r = Branches::getShortColored();
			
			preType($r);
		}
		
		public function actionTesty()
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
							if ($Student->inOtherGradeSubjectGroup($grade, $subject)) {
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
							if ($Student->inOtherGradeSubjectGroup($grade, $subject)) {
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
			
			preType($Groups, true);
			
			// Сортируем по количеству учеников
			usort($Groups, function($a, $b) {
				return ($a['count'] > $b['count'] ? -1 : 1);
			});
/*
			returnJsonAng([
				"GroupsShort"	=> $Groups,
				"GroupsFull"	=> $GroupsFull,
			]);
*/
		}
		
		public function actionClientsMap()
		{
			$this->setTabTitle("Карта клиентов");
			
			$this->addJs("//maps.google.ru/maps/api/js?libraries=places", true);
			$this->addJs("maps.controller, ng-test-app");
			
			$ang_init_data = angInit([
				"Branches"	=> Branches::$all,
				"Grades"	=> Grades::$all,
				"Subjects"	=> Subjects::$all,
			]);
			
			$this->render("clientsmap", [
				"ang_init_data" => $ang_init_data,
			]);
		}
		
		public function actionImap()
		{
			$mailbox = new PhpImap\Mailbox('{imap.yandex.ru:993/imap/ssl}', 'makcyxa-k', 'rrn1840055');
			
			$mailsIds = $mailbox->searchMailBox('FROM maksim@kolyaidn.com');
			
//			$inboxMail = $mailbox->lecplay();
			
			var_dump($mailbox);
		}
		
		// Перевести номера телефонов из форматированных
		public function actionUpdatePhones()
		{
			$Requests = Request::findAll([
				"condition" => "adding = 0 && (phone !='' OR phone2 != '' OR phone3 != '')",
			]);
			
			$Students = Student::findAll([
				"condition" => "phone !='' OR phone2 != '' OR phone3 != ''"
			]);
			
			$Representatives = Representative::findAll([
				"condition" => "phone !='' OR phone2 != '' OR phone3 != ''"
			]);
			
			foreach ($Requests as &$Request) {
				foreach (Request::$_phone_fields as $phone_field) {
					if ($Request->{$phone_field} != "") {
						$Request->{$phone_field} = cleanNumber($Request->{$phone_field});
						$Request->save($phone_field);
					}	
				}
			}
			
			foreach ($Students as &$Student) {
				foreach (Request::$_phone_fields as $phone_field) {
					if ($Student->{$phone_field} != "") {
						$Student->{$phone_field} = cleanNumber($Student->{$phone_field});
						$Student->save($phone_field);
					}	
				}
			}
			
			foreach ($Representatives as &$Representative) {
				foreach (Request::$_phone_fields as $phone_field) {
					if ($Representative->{$phone_field} != "") {
						$Representative->{$phone_field} = cleanNumber($Representative->{$phone_field});
						$Representative->save($phone_field);
					}	
				}
			}
		}
		
		public function actionMap()
		{
			$this->setTabTitle("Тестирование алгоритма метро");
			
			$this->addJs("//maps.google.ru/maps/api/js?libraries=places", true);
			$this->addJs("maps.controller, ng-test-app");
			
			$this->render("map");
		}
		
		public function actionMailer()
		{
			$mail = initMailer();
			
			$mail->addAddress("makcyxa-k@yandex.ru");
			$mail->Body = "Замалым";
			$mail->Subject = 'Here is the subject';

			if(!$mail->send()) {
			    echo 'Message could not be sent.';
			    echo 'Mailer Error: ' . $mail->ErrorInfo;
			} else {
			    echo 'Message has been sent';
			}			
		}
		
		public function actionDuplicate()
		{
			$res = isDuplicate("79205556776", 19);
			var_dump($res);
		}
		
		function actionSwc()
		{
			$q = Student::getWithoutContract();
			
			var_dump($q);
		}
		
		function actionAddTask()
		{
// 			$this->addJs("//cdn.ckeditor.com/4.5.2/full-all/ckeditor.js", true);
			$this->setTabTitle("Редактирование задачи");
			$this->render("add_task");
		}
		
		##################################################
		###################### AJAX ######################
		##################################################
	
	
	}
