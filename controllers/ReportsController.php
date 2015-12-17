<?php	// Контроллер отчетов	class ReportsController extends Controller	{		public $defaultAction = "add";				public static $allowed_users = [User::USER_TYPE, Teacher::USER_TYPE, Student::USER_TYPE];				// Папка вьюх		protected $_viewsFolder	= "report";				public function beforeAction()		{						$this->addJs("ng-reports-app");		}				public function actionView()		{			$this->_custom_panel = true;			$Report				= Report::findById($_GET['id']);			$Report->Student	= Student::findById($Report->id_student);			$Report->Teacher	= Teacher::findById($Report->id_teacher);						$ang_init_data = angInit([				"Report" 		=> $Report,			]);						$this->render("view", [				'ang_init_data' => $ang_init_data,			]);		}				public function actionList()		{				if (User::fromSession()->type == Teacher::USER_TYPE) {				$this->_custom_panel = true;				$VisitJournal = VisitJournal::findAll([					"condition" => "id_teacher=" . User::fromSession()->id_entity,					"group"	=> "id_entity",				]);								foreach ($VisitJournal as $Data) {					$Student = Student::findById($Data->id_entity);					$Student->Reports = Report::findAll([						"condition" => "id_teacher=" . User::fromSession()->id_entity . " AND id_student=" . $Student->id					]);										$Student->visit_count = VisitJournal::count([						"condition" => "id_teacher=" . User::fromSession()->id_entity . " AND id_entity=" . $Student->id					]);										$Students[] = $Student;				}								$ang_init_data = angInit([					'Students'	=> $Students,				]);								$this->render("teacher_list", [					'ang_init_data' => $ang_init_data,				]);			}			if (User::fromSession()->type == Student::USER_TYPE) {				$this->_studentList();			}		}				private function _studentList()		{			$Student = Student::findById(User::fromSession()->id_entity);			$Student->AllVisits = $Student->getVisits();							// Group visits by subject			foreach ($Student->AllVisits as $Visit) {// 					$Visit->Teacher = Teacher::findById($Visit->id_teacher);				$Visits[$Visit->id_teacher][$Visit->id_subject][] = $Visit;								if (!$Teachers[$Visit->id_teacher]) {					$Teachers[$Visit->id_teacher] = Teacher::findById($Visit->id_teacher);				}			}						unset($Student->AllVisits);						// Get reports			foreach ($Visits as $id_teacher => $data) {				foreach($data as $id_subject => $Visit) {										$Group = Group::find([						"condition" => "CONCAT(',', CONCAT(students, ',')) LIKE '%,{$Student->id},%' AND id_subject=$id_subject AND id_teacher=$id_teacher"					]);										if ($Group) {						$PlannedLessons[$id_teacher][$id_subject] = $Group->countFutureSchedule();					} else {						$PlannedLessons[$id_teacher][$id_subject] = false;					}													$Reports = Report::findAll([						"condition" => "id_student=" . $Student->id . " AND id_subject=" . $id_subject ." AND id_teacher=" . $id_teacher					]);										// ВНИМАНИЕ: ДОБАВЛЯЕМ ОТЧЕТЫ В МАССИВ visits!					foreach ($Reports as $Report) {						// внимание!						$Report->lesson_date = date("Y-m-d", strtotime($Report->date));						$Visits[$id_teacher][$id_subject][] = $Report;					}															// Sort visits by SO CALLED lesson_date					usort($Visits[$id_teacher][$id_subject], function($a, $b) {						return $a->lesson_date > $b->lesson_date;					});					}							}						$ang_init_data = angInit([				'Visits' => $Visits,				'Teachers' => $Teachers,				'PlannedLessons' => $PlannedLessons,				'Subjects' => Subjects::$all,			]);						$this->setTabTitle('Отчёты');											$this->render("student_list", [				'ang_init_data' => $ang_init_data,			]);		}				public function actionAddStudent()		{			$id_student = $_GET["id_student"];						$Student = Student::findById($id_student);			$Student->AllVisits = $Student->getVisits();						// Group visits by subject			foreach ($Student->AllVisits as $Visit) {				if ($Visit->id_teacher == User::fromSession()->id_entity) {					$Student->Visits[$Visit->id_subject][] = $Visit;				}			}						unset($Student->AllVisits);						// Get reports			foreach(array_keys($Student->Visits) as $id_subject) {				$Reports = Report::findAll([					"condition" => "id_teacher=" . User::fromSession()->id_entity . " AND id_student=" . $Student->id . " AND id_subject=" . $id_subject				]);								// ВНИМАНИЕ: ДОБАВЛЯЕМ ОТЧЕТЫ В МАССИВ visits!				foreach ($Reports as $Report) {					// внимание!					$Report->lesson_date = date("Y-m-d", strtotime($Report->date));					$Student->Visits[$id_subject][] = $Report;				}												// Sort visits by SO CALLED lesson_date				usort($Student->Visits[$id_subject], function($a, $b) {					return $a->lesson_date > $b->lesson_date;				});				}									$ang_init_data = angInit([				'Student' => $Student,				'Subjects' => Subjects::$all,				'SubjectsDative' => Subjects::$dative,			]);						$this->setTabTitle('Добавление отчета');						$this->render('add_student', [				'ang_init_data' => $ang_init_data,			]);		}				public function actionEdit()		{			$this->setRights([User::USER_TYPE, Teacher::USER_TYPE]);						$Report = Report::findById($_GET['id']);						$this->actionAdd($Report);		}				public function actionAdd($Report = false)		{			$this->_custom_panel = true;						if ($Report) {				$Report->Student = Student::findById($Report->id_student);				$Report->Teacher = Teacher::findById($Report->id_teacher);			} else {				$id_student = $_GET["id_student"];				$id_subject = $_GET["id_subject"];								$Report = new Report([					"id_student" => $id_student,					"id_subject" => $id_subject,					"id_teacher" => User::fromSession()->id_entity,					]);									$Report->Student = Student::findById($id_student);				$Report->Teacher = Teacher::findById(User::fromSession()->id_entity);			}						$ang_init_data = angInit([				"Report" 	=> $Report,				"Subjects"	=> Subjects::$dative,				]);						$this->render("add", [				'ang_init_data' => $ang_init_data,			]);		}				public function actionStudents()		{			$this->setTabTitle('Записаться на тестирование');						$id_student = User::fromSession()->id_entity;			$Student = Student::findById($id_student);			$Contract = $Student->getLastContract();						$ang_init_data = angInit([			//	"TestingData" 	=> Testing::getAvailable(User::fromSession()->id_entity),				"Testings"		=> Testing::findAll(),				"Subjects"		=> Subjects::$dative,				"id_student"	=> $id_student,				"grade"			=> $Contract->grade,				'minutes_9'		=> Subjects::$minutes_9,				'minutes_11'	=> Subjects::$minutes_11,			]);						$this->render('students', [				"ang_init_data" => $ang_init_data,			]);		}				public function actionAjaxAdd()		{			extract($_POST);						$NewReport = Report::add($Report);						if ($with_email) {				$_POST["Report"] = $NewReport;				$this->actionAjaxSendEmail();					}		}				public function actionAjaxEdit()		{			extract($_POST);						Report::updateById($Report['id'], $Report);						preType($Report);		}						public function actionAjaxSendEmail()		{// 			error_reporting(E_ALL);			extract($_POST);						$Report  = (object)$Report;			$Student = Student::findById($Report->id_student);			$Teacher = Teacher::findById($Report->id_teacher);			/*			$message = "				<div>Преподаватель: {$Teacher->last_name} {$Teacher->first_name} {$Teacher->middle_name}</div>				<b>Выполнение домашнего задания</b><br>				Оценка: {$Report['homework_grade']}<br>				Комментарий: {$Report['homework_comment']}<br><br>								<b>Работоспособность и активность на уроках</b><br>				Оценка: {$Report['activity_grade']}<br>				Комментарий: {$Report['activity_comment']}<br><br>								<b>Способность усваивать материал</b><br>				Оценка: {$Report['behavior_grade']}</div><br>				Комментарий: {$Report['behavior_comment']}<br><br>								<b>Прогнозируемое количество баллов на экзамене</b><br>				Оценка: {$Report['material_grade']}<br>				Комментарий: {$Report['material_comment']}<br><br>			";	*/			/*			$message = "				Преподаватель: <b>{$Teacher->last_name} {$Teacher->first_name} {$Teacher->middle_name}</b>				<table style='width: 100%; border-collapse:collapse' cellspacing='0'>					<tr>						<td></td>						<td style='color: #A3A3A3'>оценка</td>						<td style='color: #A3A3A3'>комментарий</td>					</tr>					<tr>						<td style='border-top: 1px solid #A3A3A3'>выполнение домашнего задания</td>						<td style='border-top: 1px solid #A3A3A3'>{$Report['homework_grade']}</td>						<td style='border-top: 1px solid #A3A3A3'>{$Report['homework_comment']}</td>					</tr>					<tr>						<td style='border-top: 1px solid #A3A3A3'>работоспособность и активность на уроках</td>						<td style='border-top: 1px solid #A3A3A3'>{$Report['activity_grade']}</td>						<td style='border-top: 1px solid #A3A3A3'>{$Report['activity_comment']}</td>					</tr>					<tr>						<td style='border-top: 1px solid #A3A3A3'>поведение на уроках</td>						<td style='border-top: 1px solid #A3A3A3'>{$Report['behavior_grade']}</td>						<td style='border-top: 1px solid #A3A3A3'>{$Report['behavior_comment']}</td>					</tr>					<tr>						<td style='border-top: 1px solid #A3A3A3'>способность усваивать новый материал</td>						<td style='border-top: 1px solid #A3A3A3'>{$Report['material_grade']}</td>						<td style='border-top: 1px solid #A3A3A3'>{$Report['material_comment']}</td>					</tr>					<tr>						<td style='border-top: 1px solid #A3A3A3'>выполнение контрольных работ, текущий уровень знаний</td>						<td style='border-top: 1px solid #A3A3A3'>{$Report['tests_grade']}</td>						<td style='border-top: 1px solid #A3A3A3'>{$Report['tests_comment']}</td>					</tr>					<tr>						<td style='border-top: 1px solid #A3A3A3'>рекомендации родителям</td>						<td style='border-top: 1px solid #A3A3A3'></td>						<td style='border-top: 1px solid #A3A3A3'>{$Report['recommendation']}</td>					</tr>				</table>			";*/						$Student->AllVisits = $Student->getVisits();						$Group = Group::find([				"condition" => "id_subject={$Report->id_subject} AND id_teacher={$Teacher->id}"			]);			$Group->future_schedule_count = $Group->countFutureSchedule();						// Group visits by subject			foreach ($Student->AllVisits as $Visit) {				if ($Visit->id_teacher == $Teacher->id && $Visit->id_subject == $Report->id_subject) {					if ($Visit->presence == 1 && !$Visit->late) {						$visits_text .= "<li>" . date("d.m.y", strtotime($Visit->date)) . " – был</li>";									}					if ($Visit->presence == 1 && $Visit->late) {						$visits_text .= "<li>" . date("d.m.y", strtotime($Visit->date)) . " – опоздал на " . $Visit->late 							. " " . pluralize('минута', 'минуты', 'минут', $Visit->late) . "</li>";									}					if ($Visit->presence == 2) {						$visits_text .= "<li>" . date("d.m.y", strtotime($Visit->date)) . " – не был</li>";									}				}			}			if ($Group->future_schedule_count) {				$visits_text .= "<li>Планируется еще " . $Group->future_schedule_count . " " . pluralize('занятие', 'занятия', 'занятий', $Group->future_schedule_count) . "</li>";				}						$subject = "Отчет преподавателя по " . Subjects::$dative[$Report->id_subject] . " " . $Teacher->getInitials() . " (ЕГЭ-Центр)";						$sbj = Subjects::$all[$Report->id_subject];						$message = "				<div><b>Предмет:</b> {$sbj}</div>				<div><b>Дата формирования отчета:</b> {$Report->date}</div>				<div><b>Выполнение домашнего задания</b> (заполняется преподавателем): оценка {$Report->homework_grade} ({$Report->homework_comment})</div>				<div><b>Работоспособность и активность на уроках</b> (заполняется преподавателем): оценка {$Report->activity_grade} ({$Report->activity_comment})</div>				<div><b>Поведение на уроках</b> (заполняется преподавателем): оценка {$Report->behavior_grade} ({$Report->behavior_comment})</div>				<div><b>Способность усваивать новый материал</b> (заполняется преподавателем): оценка {$Report->material_grade} ({$Report->material_comment})</div>				<div><b>Выполнение контрольных работ, текущий уровень знаний</b> (заполняется преподавателем): оценка {$Report->tests_grade} ({$Report->tests_comment})</div>				<div><b>Рекомендации родителям</b> (заполняется преподавателем): ({$Report->recommendation})</div>				<div><b>Посещаемость в группе преподавателя {$Teacher->getFullName()}</b> (заполняется автоматически):				<ul>					{$visits_text}				</ul>				</div>				<div>В личном кабинете (на сайте ege-centr.ru ссылка вверху справа) Вы можете прочитать этот же отчет, а также увидеть посещаемость вашего ребенка в течение года во всех группах. Подобные отчеты формируются каждым преподавателем примерно каждые 2 месяца. Если у Вас есть вопросы, звоните по единому номеру ЕГЭ-Центра (495) 646-85-92. Это сообщение создано автоматически, отвечать на него не обязательно.</div>				<div>С уважением, ЕГЭ-Центр.</div>			";												if ($Student->Representative && $Student->Representative->email) {				Email::send($Student->Representative->email, $subject, $message);								$Report = Report::findById($Report->id);				$Report->email_sent = true;				$Report->date_sent = now();				$Report->save("email_sent");				$Report->save("date_sent");												$sms_message = Template::get(11, [					'representative_name'	=> $Student->Representative->first_name . " " . $Student->Representative->middle_name,					'subject'				=> Subjects::$dative[$Report->id_subject],					'email'					=> $Student->Representative->email,				]);										foreach (Student::$_phone_fields as $phone_field) {					$representative_number = $Student->Representative->{$phone_field};					if (!empty($representative_number)) {						SMS::send($representative_number, $sms_message, ["additional" => 3]);					}				}								returnJsonAng($Report->date_sent);			}		}				public function actionAjaxDelete()		{			extract($_POST);						Report::deleteById($id_report);		}				public function actionAjaxChangeDate()		{			extract($_POST);						$Cabinets = Cabinet::getByBranch(Branches::TRG);						foreach ($Cabinets as $Cabinet) {				$cabinet_ids[] = $Cabinet->id;			}			$cabinet_ids = implode(',', $cabinet_ids);						// lesson time			$time_data_schedule = GroupSchedule::findAll([				"condition" => "date='$date' AND cabinet IN ($cabinet_ids)",				"order"		=> "time ASC"			]);						foreach ($time_data_schedule as $data) {				$return[$data->cabinet][] = [					'start_time' => $data->time,					'end_time'	 => self::_plusHours($data->time),				];			}						// testing time			$time_data_testing = Testing::findAll([				"condition" => "date='$date' AND cabinet IN ($cabinet_ids)". ($id > 0 ? " AND id!=$id" : ""),				"order"	=> "start_time ASC"			]);						foreach ($time_data_testing as $data) {				$return[$data->cabinet][] = [					'start_time' => $data->start_time,					'end_time'	 => $data->end_time,				];			}						// sort by time			foreach ($return as &$cabinet_time) {				usort($cabinet_time, function($a, $b) {					return $a['start_time'] > $b['start_time'];				});			}						returnJsonAng($return);		}				private static function _plusHours($time, $hours = 2, $minutes = 15)		{			$timestamp = strtotime($time) + 60*60*$hours + (60 * $minutes);			return date('H:i', $timestamp);		}				private static function _generateFutureDates($days = 14) 		{			foreach(range(0, $days) as $day) {				$dates[] = date("Y-m-d", strtotime("+$day days"));			}						return $dates;		}			}