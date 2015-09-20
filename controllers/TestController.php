<?php

	// Контроллер
	class TestController extends Controller
	{
		public $defaultAction = "test";
		// Папка вьюх
		protected $_viewsFolder	= "test";
		
		public function beforeAction()
		{
			ini_set("display_errors", 1);
//			error_reporting(E_ALL);
		}
		
		public function actionGo()
		{	
			$Groups = Group::findAll([
				"condition" => "id_branch=" . Branches::PVN,
			]);
			
// 			$add_students = [1851, 2111, 1910, 2051];
			
			foreach ($Groups as $Group) {
				if ($Group->id_teacher) {
					$Teacher = Teacher::findById($Group->id_teacher);
					foreach (Student::$_phone_fields as $phone_field) {
						$teacher_number = $Teacher->{$phone_field};
						if (!empty($teacher_number)) {
							$messages[] = [
								"type"      => "Учителю #" . $Teacher->id,
								"number" 	=> $teacher_number,
								"message"	=> self::_generateMessage($Teacher),
							];
						}
					}
				}
				foreach ($Group->students as $id_student) {
					if (!in_array($id_student, $add_students)) {
						continue;
					}
					$Student = Student::findById($id_student);
									
					foreach (Student::$_phone_fields as $phone_field) {
						$student_number = $Student->{$phone_field};
						if (!empty($student_number)) {
							$messages[] = [
								"type"      => "Ученику #" . $Student->id,
								"number" 	=> $student_number,
								"message"	=> self::_generateMessage($Student),
							];
						}
						
						if ($Student->Representative) {
							$representative_number = $Student->Representative->{$phone_field};
							if (!empty($representative_number)) {
								$messages[] = [
									"type"      => "Представителю #" . $Student->Representative->id,
									"number" 	=> $representative_number,
									"message"	=> self::_generateMessage($Student),
								];
							}
						}
					}
				}
			}

			$sent_to = [];
			$final = [];
			foreach ($messages as $message) {
				if (!in_array($message['number'], $sent_to)) {
// 					SMS::send($message['number'], $message['message'], ["additional" => 3]);
					$sent_to[] = $message['number'];
					$final[] = $message;
					// debug
					$body .= "<h3>" . $message["type"] . "</h3>";
					$body .= "<b>Номер: </b>" . $message['number']."<br><br>";
					$body .= "<b>Сообщение: </b>" . $message['message']."<hr>";
				}
			}
			
//			Email::send("makcyxa-k@yandex.ru", "Уведомление о личном кабинете, Калужская", $body);
			preType($final);			
		}
		
		private function _generateMessage($Entity)
		{
			return "ЕГЭ-Центр информирует: доступ в личный кабинет (на сайте ЕГЭ-Центра ссылка вверху справа) логин – {$Entity->login}, пароль {$Entity->password}";
		}
		
		public function actionSetTeacherLogin()
		{
			ini_set('display_errors', 1);
			error_reporting(E_ALL);
			$Teachers = Teacher::findAll();
				
			foreach ($Teachers as $Teacher) {
				$Teacher->login 	= $Teacher->_generateLogin();
				$Teacher->password	= $Teacher->_generatePassword();
				
				User::add([
					"login" 		=> $Teacher->login,
					"password"		=> $Teacher->password,
					"first_name"	=> $Teacher->first_name,
					"last_name"		=> $Teacher->last_name,
					"middle_name"	=> $Teacher->middle_name,
					"type"			=> Teacher::USER_TYPE,
					"id_entity"		=> $Teacher->id
				]);
				
				$Teacher->save();
				
				preType($Teacher);
			}
		}
		
		public function actionSetStudentLogin()
		{
			$Students = Student::getWithContract();
/*
			$Students = Student::findAll([
				"condition" => "id IN (1851, 2111, 1910, 2051)"
			]);
*/
									
			foreach ($Students as $Student) {
				if (!$Student->login) {
					echo $Student->id . "<br>";	
				}
/*
				$Student->Contract 	= $Student->getLastContract();
				$Student->login 	= $Student->Contract->id;
				$Student->password	= mt_rand(10000000, 99999999);
				$Student->save();
				
				User::add([
					"login" 	=> $Student->login,
					"password"	=> $Student->password,
					"first_name"	=> $Student->first_name,
					"last_name"		=> $Student->last_name,
					"middle_name"	=> $Student->middle_name,
					"type"			=> Student::USER_TYPE,
					"id_entity"		=> $Student->id
				]);
*/
			}
		}
		
/*
		public function action()
		{	
			$Students = Student::getWithContract(true);
			
			$nc = new NCLNameCaseRu(); 
			
			$messages = [];
			foreach ($Students as $Student) {
				$student_gender = $nc->genderDetect($Student->last_name . " " 
							. $Student->first_name . " " . $Student->middle_name);
				foreach (Student::$_phone_fields as $phone_field) {
					$student_number = $Student->{$phone_field};
					if (!empty($student_number)) {
						$messages[] = [
							"number" 	=> $student_number,
							"message"	=> ($student_gender == 1 ? "Уважаемый" : "Уважаемая") . " {$Student->first_name} {$Student->middle_name}, ЕГЭ-Центр активно формирует группы и расписание. Ежегодно, как и в этом году, занятия начинаются с 15 по 30 сентября. Перед началом занятий мы обязательно с Вами свяжемся. Спасибо за понимание. Администрация ЕГЭ-Центра."
						];
					}
					
					if ($Student->Representative) {
						$representative_gender = $nc->genderDetect($Student->Representative->last_name . " " 
							. $Student->Representative->first_name . " " . $Student->Representative->middle_name);
						$representative_number = $Student->Representative->{$phone_field};
						if (!empty($representative_number)) {
							$messages[] = [
								"number" 	=> $representative_number,
								"message"	=> ($representative_gender == 1 ? "Уважаемый" : "Уважаемая") . " {$Student->Representative->first_name} {$Student->Representative->middle_name}, ЕГЭ-Центр активно формирует группы и расписание. Ежегодно, как и в этом году, занятия начинаются с 15 по 30 сентября. Перед началом занятий мы обязательно с Вами свяжемся. Спасибо за понимание. Администрация ЕГЭ-Центра."
							];
						}
					}
				}
			}
			
			$sent_to = ['79670270752', '79031231801', '79037457698', '79251285692', '79163301472', '79164306272', '79654492601', '79096451438', '79175883100', '79153460947', '74953142024', '79055555825', '79099526366', '79853550349', '79037755318', '79852699043', '79653994501', '79152446686', '79857808032', '79030155035', '79169291117', '79257318384', '79175630479', '79166259015', '79166976092', '79166059905', '79169901330', '79652064827', '79032828225', '79152191898', '79104049172', '79152690638', '79165339308', '79852792608', '79055907327', '79060568266', '79166705602', '79150075824', '79164521239', '79859780281', '79152381922', '79680619395', '79629271004', '79165480965', '79161710291', '79152330527', '79150072764', '79636724119', '79057799915', '79859659477', '79162310335', '79160305385', '79166161406', '79035071873', '79166390009', '79175605578', '79030101149', '79037805451', '79151887521', '79166878687', '79998262848', '79263414810', '79169558280', '79168509009', '79168153574', '79175658506', '79197789113', '79653132515', '79032874994', '79032767814', '79167437499', '79163102484', '79175776664', '79163947757', '79637896620', '79165035329', '79096478884', '79629238497', '79060663807', '79055039481', '79067895132', '79037842820', '79168793353', '79859228141', '79269127329', '79684492848', '79060535047', '79036157176', '79037335976', '79264542105', '79853899036', '79153272754', '79169536667', '79683364322', '79859952254', '79164741454', '79163533037', '79168790332', '79160703690', '79037533242', '79037413040', '79672915048', '79035242404', '79647834373', '79036119530', '79251908463', '79636488591', '79639245523', '79267080163', '79268279111', '79299623626', '79295184080', '79262489217', '79264291913', '79031778317', '79685148381', '79168238741', '79853047100', '79160446718', '79164911512', '79154670326', '79168086738', '79689657517', '79851988946', '79175548554', '79629381537', '79031218805', '79672582855', '79161572919', '79096595455', '79636058802', '79250298355', '79263374369', '79035716447', '79265381197', '79035716467', '79036819268', '79647993371', '79194100124', '79265728638', '79036710491', '79036710489', '79162612220', '79166661129', '79153866202', '79163349589', '79160412379', '79175404446', '79150367931', '79104281257', '79851408180', '79165050858', '79250236096', '79262720500', '79055347193', '79037779277', '79150774972', '79153978881', '79636661438', '79166233805', '79652195014', '79647058120', '79685286554', '79037759411', '79266989106', '79261936833', '79851786672', '79168468245', '79168334676', '79161354015', '79099566952', '79035400133', '79253709094', '79165054682', '79261542040', '79266965344', '79150190567', '79199675935', '79854580882', '79104880192', '79670763053', '79096831152', '79035260414', '79167469551', '79260140057', '79295798008', '79035158899', '79037757657', '79165366325', '79036175095', '79854310778', '79162005025', '79091569166', '79036856688', '79150318065', '79166202089', '79036816083', '79636444830'];
			
			$sent_to_new = [];
			foreach ($messages as $message) {
				if (!in_array($message['number'], $sent_to)) {
// 					SMS::send($message['number'], $message['message'], ["additional" => 3]);	
					$sent_to[] = $message['number'];
					$sent_to_new[] = $message['number'];
				}
			}
			
			preType($sent_to_new);
			echo "<hr>";
			echo count($sent_to_new);
		}
*/
		
		public function actionCabinetsCheck()
		{
			$r = Cabinet::getCabinetGroups(1);
			
			preType($r);
		}
		
		public function actionSetCabinet()
		{
			$Groups = Group::findAll();
			
			foreach ($Groups as $Group) {
				$Cabinets = Cabinet::findAll([
					"condition" => "id_branch=" . $Group->id_branch
				]);
				
				if ($Cabinets) {
					$Group->cabinet = $Cabinets[0]->id;
					$Group->save("cabinet");
				}
			}
		}
		
		public function actionStudentFreetime()
		{
			$Student = Student::findById(473);
			$ft = $Student->getGroupFreetime(208);
			preType($ft);
		}
		
		public function actionTeacherFreetime()
		{
			$Teachers = Teacher::findAll();
			
			foreach ($Teachers as $Teacher) {
				foreach ($Teacher->branches as $id_branch) {
					if (!$id_branch) {
						continue;
					}
					
					dbConnection()->query("
						INSERT INTO teacher_freetime 
							(id_teacher, id_branch, day, time) 
						VALUES
							({$Teacher->id}, $id_branch, 1, '16:15'),
							({$Teacher->id}, $id_branch, 1, '18:40'),
							
							({$Teacher->id}, $id_branch, 2, '16:15'),
							({$Teacher->id}, $id_branch, 2, '18:40'),
							
							({$Teacher->id}, $id_branch, 3, '16:15'),
							({$Teacher->id}, $id_branch, 3, '18:40'),
							
							({$Teacher->id}, $id_branch, 4, '16:15'),
							({$Teacher->id}, $id_branch, 4, '18:40'),
							
							({$Teacher->id}, $id_branch, 5, '16:15'),
							({$Teacher->id}, $id_branch, 5, '18:40'),
							
							({$Teacher->id}, $id_branch, 6, '11:00'),
							({$Teacher->id}, $id_branch, 6, '13:30'),
							({$Teacher->id}, $id_branch, 6, '16:00'),
							({$Teacher->id}, $id_branch, 6, '18:30'),
							
							({$Teacher->id}, $id_branch, 7, '11:00'),
							({$Teacher->id}, $id_branch, 7, '13:30'),
							({$Teacher->id}, $id_branch, 7, '16:00'),
							({$Teacher->id}, $id_branch, 7, '18:30')
					");
					
					echo ("
						INSERT INTO teacher_freetime 
							(id_teacher, id_branch, day, time) 
						VALUES
							({$Teacher->id}, $id_branch, 1, '16:15'),
							({$Teacher->id}, $id_branch, 1, '18:40'),
							
							({$Teacher->id}, $id_branch, 2, '16:15'),
							({$Teacher->id}, $id_branch, 2, '18:40'),
							
							({$Teacher->id}, $id_branch, 3, '16:15'),
							({$Teacher->id}, $id_branch, 3, '18:40'),
							
							({$Teacher->id}, $id_branch, 4, '16:15'),
							({$Teacher->id}, $id_branch, 4, '18:40'),
							
							({$Teacher->id}, $id_branch, 5, '16:15'),
							({$Teacher->id}, $id_branch, 5, '18:40'),
							
							({$Teacher->id}, $id_branch, 6, '11:00'),
							({$Teacher->id}, $id_branch, 6, '13:30'),
							({$Teacher->id}, $id_branch, 6, '16:00'),
							({$Teacher->id}, $id_branch, 6, '18:30'),
							
							({$Teacher->id}, $id_branch, 7, '11:00'),
							({$Teacher->id}, $id_branch, 7, '13:30'),
							({$Teacher->id}, $id_branch, 7, '16:00'),
							({$Teacher->id}, $id_branch, 7, '18:30')
					")."<br><br>";
					
					echo dbConnection()->error . "<hr>";
				}
			}
		}
		
		public function actionDeleteCache()
		{
			foreach (Branches::$all as $id_branch => $name) {
				memcached()->delete("Rating[$id_branch]");
				memcached()->delete("UniqueRating[$id_branch]");
				memcached()->delete("MaxRating[$id_branch]");
			}
//			memcached()->delete("Rating");
		//	memcached()->delete("SumRating");
		}
		
		
		public function actionTransferDayAndTime()
		{
			$Groups = Group::findAll([
				"condition" => "start!='' AND start IS NOT NULL"
			]);
			
			foreach ($Groups as $Group) {
				$GroupTime = new GroupTime([
					"id_group" 	=> $Group->id,
					"day"		=> $Group->day,
					"time"		=> $Group->start,
				]);
				
				$GroupTime->save();
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
		
		public function actionTesty()
		{
			$Students = Student::getWithContract(true);
			h1(count($Students));
			
			
			// Добавляем догавары к студентам
			foreach ($Students as $index => $Student) {
// 				echo $index . ") " . $Student->last_name .  "<br>";
				
				$Students[$index]->Contract 	= $Student->getLastContract();
				foreach ($Student->branches as $id_branch) {
					if (!$id_branch) {
						continue;
					}
					$Students[$index]->branch_short[$id_branch] = Branches::getShortColoredById($id_branch);
				}
			}
			
// 			preType($Students);
//			preType($Students[326]);
			
			echo "<hr>";
			
			// Формируем по классам, всех студентов, кто не принадлежит группам
			foreach ($Students as $index => $Student) {
//				echo $index . ") " . $Student->last_name .  "<br>";
				$GroupsGrade[$Student->Contract->grade][] = $Student;	
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
			foreach ($GroupStudents as $_grade => $_SubjectBranch) {
				foreach ($_SubjectBranch as $_subject => $_Branch) {
					foreach ($_Branch as $_branch => $BS) {
						foreach ($BS as $index => $S) {
							if ($S->inOtherGradeSubjectGroup($grade, $subject)) {
								unset($BS[$index]);
							}
						}
						// если есть ученики в группе
						if (count($BS)) {
							$GroupsFull[] = [
								"grade"		=> $_grade,
								"subject"	=> $_subject,
								"branch"	=> $_branch,
								"branch_svg"=> Branches::getName($_branch),
								"count"		=> count($BS),
								"Students"	=> $BS,
							];	
						}
					}
				}
			}
			
			preType($GroupsFull);
			
/*
			
			// Сортируем по количеству учеников
			usort($GroupsFull, function($a, $b) {
				return ($a['count'] > $b['count'] ? -1 : 1);
			});
			
*/
// 			preType($GroupsFull);
						
			h1(count($Students));
		}
		
		public function actionDeleteUsersCache()
		{
			memcached()->delete("Users");
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
		
		public function actionBranchesDelete()
		{
			error_reporting(E_ALL);
			ini_set("display_errors", 1);

			
			$branch_str = Branches::STR;
			$branch_prr = Branches::PRR;
			
			$RequestsStr = Request::findAll([
				"condition" => "id_branch=$branch_str"	
			]);
			
			$RequestsPrr = Request::findAll([
				"condition" => "id_branch=$branch_prr"	
			]);
			
			$StudentsStr = Student::findAll([
				"condition" => "CONCAT(',', CONCAT(branches, ',')) LIKE '%,{$branch_str},%'"
			]);
			
			$StudentsPrr = Student::findAll([
				"condition" => "CONCAT(',', CONCAT(branches, ',')) LIKE '%,{$branch_prr},%'"
			]);
			
			preType($RequestsStr);
			
/*
			foreach ($RequestsStr as $Request) {
				$Request->id_branch = Branches::MLD;
				$Request->save("id_branch");
			}
			
			foreach ($RequestsPrr as $Request) {
				$Request->id_branch = Branches::VLD;
				$Request->save("id_branch");
			}
*/
		}
		
		public function actionMap()
		{
			$this->setTabTitle("Тестирование алгоритма метро");
			
			$this->addJs("//maps.google.ru/maps/api/js?libraries=places", true);
			$this->addJs("maps.controller, ng-test-app");
			$this->render("map");
		}
		
		public function actionImap()
		{
			error_reporting(E_ALL);
			ini_set("display_errors", 1);
			
			$mailbox = new PhpImap\Mailbox('{imap.yandex.ru:993/imap/ssl}', 'info@ege-centr.ru', 'kochubey1981');
			
//			$mailbox->statusMailbox();
//			$mailbox->testy();
			
//			$t = $mailbox->statusMailbox();
											
			$mailsIds = $mailbox->searchMailBox('ANSWERED');
			
			foreach ($mailsIds as $id_mail) {
				$mail = $mailbox->getMail($id_mail);
				preType($mail);
			}
						
//			var_dump($mailsIds);
//			preType($mailbox);
		}
		
		public function actionRating()
		{
			$Students = Student::findAll([
				"condition" => "branches != ''"
			]);
			
			foreach ($Students as &$Student) {
				$Student->Contract = $Student->getLastContract();
			}

			
			foreach ($Students as $Student) {
				foreach ($Student->branches as $id_branch) {
					$rating[$id_branch]++;
					if ($Student->Contract) {
						$rating[$id_branch] += count($Student->Contract->subjects); 
					}
				}
			}
			
			asort($rating);
			$rating = array_reverse($rating, true);
			
			foreach ($rating as $id_branch => $score) {
				echo Branches::$all[$id_branch].": ".$score;
				echo "<br>";
			}
		}
		
		public function actionRatingCache()
		{
			$Rating = memcached()->get("Rating");
			
			preType($Rating);
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

		##################################################
		###################### AJAX ######################
		##################################################
	
	
	}
