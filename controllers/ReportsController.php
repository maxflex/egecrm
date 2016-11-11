<?php	// Контроллер отчетов	class ReportsController extends Controller	{		public $defaultAction = "add";		public static $allowed_users = [User::USER_TYPE, Teacher::USER_TYPE, Student::USER_TYPE];		// Папка вьюх		protected $_viewsFolder	= "report";		public function beforeAction()		{			$this->addJs("ng-reports-app");		}		public function actionView()		{			$this->_custom_panel = true;			$Report				= Report::findById($_GET['id']);			$Report->Student	= Student::findById($Report->id_student);			$Report->Teacher	= Teacher::findById($Report->id_teacher);			$ang_init_data = angInit([				"Report" 		=> $Report,				"Subjects"		=> Subjects::$dative,			]);			$this->render("view", [				'ang_init_data' => $ang_init_data,			]);		}		public function actionList()		{			if (User::fromSession()->type == Teacher::USER_TYPE) {				$this->_teacherList();			}			if (User::fromSession()->type == Student::USER_TYPE) {				$this->_studentList();			}			if (User::fromSession()->type == User::USER_TYPE) {				$this->_userList();			}		}		private function _teacherList()		{			$this->_custom_panel = true;			$VisitJournal = VisitJournal::findAll([				"condition" => "id_teacher=" . User::fromSession()->id_entity,				"group"	=> "id_entity",			]);			foreach ($VisitJournal as $Data) {				$Student = Student::findById($Data->id_entity);				$Student->Reports = Report::findAll([					"condition" => "id_teacher=" . User::fromSession()->id_entity . " AND id_student=" . $Student->id				]);				foreach (Subjects::$all as $id_subject => $name) {					$count = VisitJournal::count([						"condition" => "id_teacher=" . User::fromSession()->id_entity . " AND id_entity=" . $Student->id ." AND id_subject=$id_subject AND year={$Data->year}"					]);					if ($count) {						$Student->visit_count[$id_subject] = $count;						if (!$Student->ReportRequired) {							// если отчет по конфигурации не требуется							if (ReportForce::check($Student->id, User::fromSession()->id_entity, $id_subject, $Data->year)) {								continue;							}							// получаем кол-во занятий с последнего отчета по предмету							$LatestReport = Report::find([								"condition" => "id_student=" . $Student->id . " AND id_subject=" . $id_subject ." AND id_teacher=" . User::fromSession()->id_entity . " AND year={$Data->year}",                                "order" => " STR_TO_DATE(date,'%d.%m.%Y') desc "							]);							if ($LatestReport) {								$latest_report_date = date("Y-m-d", strtotime($LatestReport->date));							} else {								$latest_report_date = "0000-00-00";							}							$lessons_count = VisitJournal::count([								"condition" => "id_subject=$id_subject AND id_entity={$Student->id} AND id_teacher=" . User::fromSession()->id_entity . "									AND lesson_date > '$latest_report_date' AND year={$Data->year}"							]);							if ($lessons_count >= Report::LESSON_COUNT) {								$Student->ReportRequired = true;							}						}					}				}				# Находим группу пользователя				$group_ids = Group::getIds([					"condition" => "id_teacher=" . User::fromSession()->id_entity . " AND CONCAT(',', CONCAT(students, ',')) LIKE '%,{$Student->id},%'"				]);				if ($group_ids !== false && count($group_ids) < 2) {					$Student->id_group = $group_ids[0];				} else {					$Student->id_group = false;				}				// $Student->in_group = $group_count == 1 ? true : false;				$Students[] = $Student;			}			$Groups = Teacher::getGroups(User::fromSession()->id_entity);			usort($Students, function($a, $b) {				return $a->name() > $b->name();			});			$ang_init_data = angInit([				'Students'	=> $Students,				'Groups' 	=> $Groups,				'Subjects' 	=> Subjects::$dative,			]);			$this->render("teacher_list", [				'ang_init_data' => $ang_init_data,			]);		}		private function _userList()		{			$this->_custom_panel = true;			$this->addJs("bootstrap-select");			$this->addCss("bootstrap-select");			$ang_init_data = angInit([				'Subjects' 		=> Subjects::$all,				'Teachers'		=> Teacher::getJournalTeachers(),				'three_letters' => Subjects::$three_letters,				'reports_updated' => Settings::get('reports_updated'),				'currentPage'	=> $_GET['page'] ? $_GET['page'] : 1,			]);			$this->render("user_list", [				"ang_init_data" => $ang_init_data,			]);		}		private function _studentList()		{			$Student = Student::findById(User::fromSession()->id_entity);			$Student->AllVisits = $Student->getVisits();			// Group visits by subject			foreach ($Student->AllVisits as $Visit) {// 					$Visit->Teacher = Teacher::findById($Visit->id_teacher);				$Visits[$Visit->id_teacher][$Visit->id_subject][] = $Visit;				if (!$Teachers[$Visit->id_teacher]) {					$Teachers[$Visit->id_teacher] = Teacher::findById($Visit->id_teacher);				}			}			unset($Student->AllVisits);			// Get reports			foreach ($Visits as $id_teacher => $data) {				foreach($data as $id_subject => $Visit) {					$Group = Group::find([						"condition" => "CONCAT(',', CONCAT(students, ',')) LIKE '%,{$Student->id},%' AND id_subject=$id_subject AND id_teacher=$id_teacher"					]);					if ($Group) {						$PlannedLessons[$id_teacher][$id_subject] = $Group->countFutureSchedule();					} else {						$PlannedLessons[$id_teacher][$id_subject] = false;					}					$Reports = Report::findAll([						"condition" => "available_for_parents=1 AND id_student=" . $Student->id . "							AND id_subject=" . $id_subject ." AND id_teacher=" . $id_teacher					]);					// ВНИМАНИЕ: ДОБАВЛЯЕМ ОТЧЕТЫ В МАССИВ visits!					foreach ($Reports as $Report) {						// внимание!						$Report->lesson_date = date("Y-m-d", strtotime($Report->date));						$Visits[$id_teacher][$id_subject][] = $Report;					}					// Sort visits by SO CALLED lesson_date					usort($Visits[$id_teacher][$id_subject], function($a, $b) {						return $a->lesson_date > $b->lesson_date;					});				}			}			$ang_init_data = angInit([				'Visits' => $Visits,				'Teachers' => $Teachers,				'PlannedLessons' => $PlannedLessons,				'ReportRequired' => $ReportRequired,				'Subjects' => Subjects::$all,				'SubjectsDative' => Subjects::$dative,			]);			$this->setTabTitle('Отчёты');			$this->render("student_list", [				'ang_init_data' => $ang_init_data,			]);		}		public function actionAddStudent()		{			$id_student = $_GET["id_student"];			$Student = Student::findById($id_student, true);			$Student->AllVisits = $Student->getVisits(['id_teacher' => User::fromSession()->id_entity]);			// Group visits by subject			foreach ($Student->AllVisits as $Visit) {				$Student->Visits[$Visit->id_subject][] = $Visit;			}            unset($Student->AllVisits);			foreach ($Student->Visits as $id_subject => $Visits) {                // если отчет по конфигурации не требуется                if (ReportForce::check($Student->id, User::fromSession()->id_entity, $id_subject, $Visits[0]->year)) {                    continue;                }                // получаем кол-во занятий с последнего отчета по предмету                $LatestReport = Report::find([                    "condition" => "id_student=" . $Student->id . " AND id_subject=" . $id_subject ." AND id_teacher=" . User::fromSession()->id_entity . "AND year={$Visits[0]->year}",                    "order" => " STR_TO_DATE(date,'%d.%m.%Y') desc "                ]);                if ($LatestReport) {                    $latest_report_date = date("Y-m-d", strtotime($LatestReport->date));                } else {                    $latest_report_date = "0000-00-00";                }                $lessons_count = VisitJournal::count([                    "condition" => "id_subject=$id_subject AND id_entity={$Student->id} AND id_teacher=" . User::fromSession()->id_entity . "                        AND lesson_date > '$latest_report_date' AND year={$Visits[0]->year}"                ]);                $ReportRequired[$id_subject] = $lessons_count >= Report::LESSON_COUNT ? true : false;            }			// Get reports			foreach ($Student->Visits as $id_subject => $Visits) {                $Reports = Report::findAll([                    "condition" => "id_teacher=" . User::fromSession()->id_entity . " AND id_student=" . $Student->id . " AND id_subject=" . $id_subject . " AND year={$Visits[0]->year}"                ]);                // Находим группу                $Group = Group::find([                    "condition" => "CONCAT(',', CONCAT(students, ',')) LIKE '%,{$Student->id},%' AND id_subject=$id_subject"                ]);                if ($Group) {                    if ($Group->id_teacher == User::fromSession()->id_entity) {                        $Messages[$id_subject] = "в данный момент ученик присутствует в <a href='teachers/groups/edit/{$Group->id}/schedule'>группе №" . $Group->id . "</a>";                    } else {                        $Messages[$id_subject] = "ученик перешел в группу к другому преподавателю";                    }                } else {                    $Messages[$id_subject] = "ученик прекратил подготовку по этому предмету в ЕГЭ-Центре";                }                // ВНИМАНИЕ: ДОБАВЛЯЕМ ОТЧЕТЫ В МАССИВ visits!                foreach ($Reports as $Report) {                    // внимание!                    $Report->lesson_date = date("Y-m-d", strtotime($Report->date));                    $Student->Visits[$id_subject][] = $Report;                }    		    // Sort visits by SO CALLED lesson_date                usort($Student->Visits[$id_subject], function($a, $b) {                    return $a->lesson_date > $b->lesson_date;                });            }			$ang_init_data = angInit([				'Student' => $Student,				'Subjects' => Subjects::$all,				'Messages' => $Messages,				'SubjectsDative' => Subjects::$dative,				'ReportRequired' => $ReportRequired,			]);			$this->setTabTitle('Добавление отчета');			$this->render('add_student', [				'ang_init_data' => $ang_init_data,			]);		}		public function actionEdit()		{			$this->setRights([User::USER_TYPE, Teacher::USER_TYPE]);			$Report = Report::findById($_GET['id']);			$Report->email = $Report->getEmail();			$this->actionAdd($Report);		}		public function actionAdd($Report = false)		{			$this->_custom_panel = true;			if ($Report) {				$Report->Student = Student::findById($Report->id_student);				$Report->Teacher = Teacher::findById($Report->id_teacher);			} else {				$id_student = $_GET["id_student"];				$id_subject = $_GET["id_subject"];				$Report = new Report([					"id_student" => $id_student,					"id_subject" => $id_subject,					"id_teacher" => User::fromSession()->id_entity,				]);				$Report->Student = Student::findById($id_student);				$Report->Teacher = Teacher::findById(User::fromSession()->id_entity);			}			$ang_init_data = angInit([				"Report" 	=> $Report,				"Subjects"	=> Subjects::$dative,				"SubjectsFull" => Subjects::$full,			]);			$this->render("add", [				'ang_init_data' => $ang_init_data,			]);		}		public function actionAjaxAdd()		{			extract($_POST);			$NewReport = Report::add($Report);/*			if ($with_email) {				$_POST["Report"] = $NewReport;				$this->actionAjaxSendEmail();			}*/		}		public function actionAjaxEdit()		{			extract($_POST);			Report::updateById($Report['id'], $Report);			preType($Report);		}		public function actionAjaxSendEmail()		{			extract($_POST);			$Report  = (object)$Report;			$Student = Student::findById($Report->id_student);			$Teacher = Teacher::findById($Report->id_teacher);			$Student->AllVisits = $Student->getVisits();			$Group = Group::find([				"condition" => "id_subject={$Report->id_subject} AND id_teacher={$Teacher->id} AND FIND_IN_SET($Report->id_student, students)"			]);            if ($Group) {                $Group->future_schedule_count = $Group->countFutureSchedule();            }			// Group visits by subject			foreach ($Student->AllVisits as $Visit) {				if ($Visit->id_teacher == $Teacher->id && $Visit->id_subject == $Report->id_subject) {					if ($Visit->presence == 1 && !$Visit->late) {						$visits_text .= "<li>" . date("d.m.y", strtotime($Visit->date)) . " – был</li>";					}					if ($Visit->presence == 1 && $Visit->late) {						$visits_text .= "<li>" . date("d.m.y", strtotime($Visit->date)) . " – опоздал на " . $Visit->late							. " " . pluralize('минута', 'минуты', 'минут', $Visit->late) . "</li>";					}					if ($Visit->presence == 2) {						$visits_text .= "<li>" . date("d.m.y", strtotime($Visit->date)) . " – не был</li>";					}				}			}            $future_schedule_text = 'занятий с этим преподавателем больше не планируется';			if ($Group && $Group->future_schedule_count) {                $future_schedule_text = "<li>Планируется еще " . $Group->future_schedule_count . " " . pluralize('занятие', 'занятия', 'занятий', $Group->future_schedule_count) . "</li>";            }            $visits_text .= $future_schedule_text;			$subject = "Отчет преподавателя по " . Subjects::$dative[$Report->id_subject] . " " . $Teacher->getInitials() . " (ЕГЭ-Центр)";			$sbj = Subjects::$full[$Report->id_subject];			$sbj = mb_strtolower($sbj, "UTF-8");			$message = "				<div><b>Предмет:</b> {$sbj}</div>				<div><b>Дата формирования отчета:</b> {$Report->date}</div>				<div><b>Выполнение домашнего задания</b> (заполняется преподавателем): оценка {$Report->homework_grade}. {$Report->homework_comment}</div>				<div><b>Работоспособность и активность на уроках</b> (заполняется преподавателем): оценка {$Report->activity_grade}. {$Report->activity_comment}</div>				<div><b>Поведение на уроках</b> (заполняется преподавателем): оценка {$Report->behavior_grade}. {$Report->behavior_comment}</div>				<div><b>Способность усваивать новый материал</b> (заполняется преподавателем): оценка {$Report->material_grade}. {$Report->material_comment}</div>				<div><b>Выполнение контрольных работ, текущий уровень знаний</b> (заполняется преподавателем): оценка {$Report->tests_grade}. {$Report->tests_comment}</div>				<div><b>Рекомендации родителям</b> (заполняется преподавателем): {$Report->recommendation}</div>				<div><b>Посещаемость в группе преподавателя {$Teacher->getFullName()}</b> (заполняется автоматически):				<ul>{$visits_text}</ul></div><div>В личном кабинете (на сайте ege-centr.ru ссылка вверху справа) Вы можете прочитать этот же отчет, а также увидеть посещаемость вашего ребенка в течение года во всех группах. Подобные отчеты формируются каждым преподавателем примерно каждые 2 месяца. Если у Вас есть вопросы, звоните по единому номеру ЕГЭ-Центра (495) 646-85-92. Это сообщение создано автоматически, отвечать на него не обязательно.</div>				<div>С уважением, ЕГЭ-Центр.</div>			";			if ($Student->Representative && $Student->Representative->email) {				Email::send($Student->Representative->email, $subject, $message);				$Report = Report::findById($Report->id);				$Report->email_sent = true;				$Report->date_sent = now();				$Report->save("email_sent");				$Report->save("date_sent");				$sms_message = Template::get(11, [					'representative_name'	=> $Student->Representative->first_name . " " . $Student->Representative->middle_name,					'subject'				=> Subjects::$dative[$Report->id_subject],					'email'					=> $Student->Representative->email,				]);				foreach (Student::$_phone_fields as $phone_field) {					$representative_number = $Student->Representative->{$phone_field};					if (!empty($representative_number)) {						SMS::send($representative_number, $sms_message, ["additional" => 3]);					}				}				returnJsonAng($Report->date_sent);			}		}		public function actionAjaxDelete()		{			extract($_POST);			Report::deleteById($id_report);		}		public function actionAjaxGetReports()		{			extract($_POST);			$data = Teacher::getReportData($page, $teachers);			returnJsonAng($data);		}		public function actionAjaxForceNoreport()		{			extract($_POST);			ReportForce::toggle($id_student, $id_teacher, $id_subject, $year);		}		public function actionAjaxRecalcHelper()		{			returnJsonAng([				'date' 		=> ReportHelper::recalc(),				'red_count'	=> Teacher::redReportCountAll()			]);		}	}