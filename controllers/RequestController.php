<?php	// Контроллер	class RequestController extends Controller	{		public $defaultAction = "List";		// Папка вьюх		protected $_viewsFolder	= "request";				/**		 * BEFORE ACTION.		 *		 */		public function beforeAction()		{			// Скрипт контроллера			$this->addJs("ng-request-app");		}				/**		 * Информация по заявке.		 *		 */		public function actionEdit()		{			// Находим заявку по ID			$Request = Request::findById($_GET["id"]);						// Если заявка или ученик не установлены			if (!$Request || !$Request->Student) {				$this->redirect("requests", true);			}			if ($Request->adding) {			} else {				// Добавляем заголовок, в него пишем дубликаты заявки, если есть//				$this->setTabTitle("Редактирование профиля ученика №" . $Request->Student->id);//				$this->setRightTabTitle("<span class='link-reverse pointer' id='delete-student' onclick='deleteStudent({$Request->Student->id})'>удалить профиль</span>");			}						// не надо панель рисовать			$this->_custom_panel = true;						// Добавляем JS и CSS			$this->addJs("//maps.google.ru/maps/api/js?libraries=places", true);			$this->addJs("bootstrap-select, maps.controller");			$this->addCss("bootstrap-select");						# Генерируем данные для ангуляра			$ang_init_data = angInit([				# Основные данные				"request_comments"	=> $Request->Comments,				"payment_statuses"	=> Payment::$all,				"payment_types"		=> PaymentTypes::$all,				"Subjects"			=> Subjects::$three_letters,				"Grades"			=> Grades::$all,				"id_request"		=> $Request->id,					// ID текущей заявки				"user"				=> User::fromSession()->dbData(),				"users"				=> User::getCached(),				"request_duplicates"=> $Request->getDuplicates(true),	// получить дубликаты, включая свой ID				"request_duplicate_comments"=> $Request->getDuplicateComments(),	// получить дубликаты, включая свой ID								"request_phone_level"	=> $Request->phoneLevel(),				# Данные ученика				"student_comments" => $Request->Student->getComments(),				"server_markers"=> $Request->Student->getMarkers(),		// Метки ученика				"payments"		=> $Request->Student->getPayments(),	// Платежи ученика				"freetime"		=> $Request->Student->getFreetime(),				"contracts"		=> $Request->Student->getContracts(),	// Договоры ученика				"student"		=> $Request->Student,  // Данные по ученику для печати				"Groups"		=> $Request->Student->getGroups(),				"student_phone_level"	=> $Request->Student->phoneLevel(),				"branches_brick"		=> Branches::getShortColored(),				"GroupLevels"			=> GroupLevels::$all,				# Данные представителя				"representative_phone_level"	=> ($Request->Student->Representative ? $Request->Student->Representative->phoneLevel() : 1), // уровень телефона 1, если нет представителя				"representative"				=> ($Request->Student->Representative ? $Request->Student->Representative : new Representative()),  // для печати			]);//			preType($Request->Student->getContracts(), false);//			var_dump($Request->Student->getContracts()[0]->files);			# Передача во view			$this->render("edit", [				"Request"		=> $Request,				"User"			=> User::fromSession(),				"ang_init_data"	=> $ang_init_data,			]);		}		/**		 * Список заявок.		 *		 */		public function actionList()		{							$this->addJs("dnd");						$Users = User::getCached();						// Генерируем HTML для выбора пользователей 				$html = '<select class="user-list top" onchange="setRequestListUser(this)" style="background-color: '. $Users[$_COOKIE["id_user_list"]]['color'] .'">					<option selected="" value="">все</option>					<option disabled="" value="">──────────────</option>';								foreach ($Users as $User) {					if (!$User['worktime']) {						continue;					}					$html .= '						<option style="background-color: '. $User['color'] .'" 							'. ($User['id'] == $_COOKIE["id_user_list"] ? "selected" : "") .'							value="'. $User['id'] .'">							'. $User['login'] .'						</option>';				}				$html .= '</select>';			// \конец						$this->setTabTitle("Заявки " . $html);			$this->setRightTabTitle('<a href="requests/add" class="link-reverse link-white">создать заявку</a>');			// Получаем выбранный список заявок			$id_status	= Request::getIdStatus();			$page		= isset($_GET['page']) ? $_GET['page'] : 1;			// Получаем новые заявки			$Requests = Request::getByPage($page, $id_status);						// Получаем сколько новых, в отказе и тд.			$RequestStatusesCount = Request::getAllStatusesCount();			// Данные для ангуляра			$ang_init_data = angInit([				"requests" 				=> $Requests,				"users"					=> $Users,				"subjects"				=> Subjects::$all,				"branches"				=> Branches::$all,				"notification_types"	=> NotificationTypes::$all,				"sources"				=> Sources::$all,				"request_statuses"		=> RequestStatuses::get(),				"request_statuses_count"=> $RequestStatusesCount,				"chosen_list"			=> $id_status,				"currentPage"			=> $page,			]);						$this->render("list", [				"ang_init_data"	=> $ang_init_data,			]);		}						/**		 * Список заявок.		 *		 */		public function actionRelevant()		{				extract($_GET);						$this->addCss("bootstrap-select");			$this->addJs("bootstrap-select");						$Users = User::getCached();						// Генерируем HTML для выбора пользователей 				$html = '<select class="user-list top" onchange="setRequestListUser(this); ang_scope.pageChangedRelevant()" 					style="background-color: '. $Users[$_COOKIE["id_user_list"]]['color'] .'">					<option selected="" value="">все</option>					<option disabled="" value="">──────────────</option>';								foreach ($Users as $User) {					if (!$User['worktime']) {						continue;					}					$html .= '						<option style="background-color: '. $User['color'] .'" 							'. ($User['id'] == $_COOKIE["id_user_list"] ? "selected" : "") .'							value="'. $User['id'] .'">							'. $User['login'] .'						</option>';				}				$html .= '</select>';			// \конец						$this->setTabTitle("Релевантные заявки " . $html);			$this->setRightTabTitle('<a href="requests/add" class="link-reverse link-white">создать заявку</a>');			// Получаем новые заявки			$Requests = Request::getByPageRelevant(1, $grade, $branch, $subject);						// Данные для ангуляра			$ang_init_data = angInit([				"requests" 				=> $Requests,				"users"					=> $Users,				"subjects"				=> Subjects::$all,				"branches"				=> Branches::$all,				"notification_types"	=> NotificationTypes::$all,				"sources"				=> Sources::$all,				"request_statuses"		=> RequestStatuses::get(),				"requests_count"		=> Request::countByPageRelevant($grade, $branch, $subject),				"search"				=> [					"grade" 		=> $grade,					"id_subject"	=> $subject,					"id_branch"		=> $branch,				],			]);						$this->render("list_relevant", [				"ang_init_data"	=> $ang_init_data,			]);		}		/**		 * Добавление заявки.		 *		 */		public function actionAdd()		{			$default_params = [				"id_user_created"	=> User::fromSession()->id,			//	"id_status"			=> RequestStatuses::CUSTOM,				"adding" 			=> 1,				"id_source"			=> 0,			];			// Добавляем заявку			$Request = new Request($_GET + $default_params);			if (!$Request->id_student) {				// Создаем нового ученика по заявке, либо привязываем к уже существующему				$this->setTabTitle("Добавление профиля ученика");				$Request->createStudent();			} else {				$this->setTabTitle("Добавление заявки к профилю ученика №" . $Request->id_student);			}			// Сохраняем заявку			$Request->save();			// Редиректим на редактирование заявки			// $this->redirect("requests/edit/" . $Request->id, true);			$_GET["id"] = $Request->id;			$this->actionEdit();		}		##################################################		###################### AJAX ######################		##################################################		/**		 * Редактирование заявки – основное сохранение.		 *		 */		public function actionAjaxSave()		{			preType($_POST, false); // debug info//			preType($_FILES, true);			# ЗАЯВКА			// Получаем ID заявки			$id_request = $_POST["id_request"];			// Находим заявку			$Request = Request::findById($id_request);			// форсируем ID студента/*			if ($_POST["id_student_force"]) {				$Request->id_student = $_POST["id_student_force"];				$Request->save("id_student");				$Request = Request::findById($id_request);			}*/			// Обновляем данные (без сохранения, сохраняем в конце)						if (!isset($_POST["Request"]["branches"])) {				$_POST["Request"]["branches"] = "";			}						$Request->update($_POST["Request"], false);//			preType($Request);			# НАПОМИНАНИЕ			if ($Request->Notification) {				// если не установлен тип нотификации, то удалить ее				if (!$_POST["Notification"]["id_type"]) {					$Request->Notification->delete();				} else {					$Request->Notification->update($_POST["Notification"]); // обновить и сохранить данные				}			} else {				// инача создаем новое уведомление				if ($Notification = Notification::add($_POST["Notification"])) {					$Request->addRelation("Notification", $Notification);				}			}			# СТУДЕНТ			// Если студент найден			if ($Request->Student) {				$Request->Student->update($_POST["Student"]); // обновить и сохранить данные			} else {				// инача создаем нового студента				if ($Student = Student::add($_POST["Student"])) {					$Request->addRelation("Student", $Student);				}			}			// Если добавили студента (ИНАЧЕ ВООБЩЕ НИКАКИЕ ДАННЫЕ НЕ СОХРАНЯТСЯ)			if ($Request->Student) {				# МЕТКИ СТУДЕНТА				// $Request->Student->addMarkers($_POST["marker_data"]);				# СВОБОДНОЕ ВРЕМЯ СТУДЕНТА				Freetime::addData($_POST["Student"]["freetime"], $Request->Student->id);				# Если удобные для ученика филиалы не установлены				if (!$_POST["Student"]["branches"]) {					$Request->Student->branches = null;				}				# ПАСПОРТ УЧЕНИКА				// Если паспорт найден				if ($Request->Student->Passport) {					$Request->Student->Passport->update($_POST["StudentPassport"]);				} else {					// Если данные паспорта были введены					if (hasValues($_POST["StudentPassport"])) {						$StudentPassport = Passport::add($_POST["StudentPassport"] + ["type" => Passport::TYPE_STUDENT]); // добавляем тип по умолчанию						$Request->Student->addRelation("Passport", $StudentPassport, true); // добавляем взаимосвязь и сохраняем id_passport					}				}				# ПРЕДСТАВИТЕЛЬ				// Если есть представитель у ученика				if ($Request->Student->Representative) {					$Request->Student->Representative->update($_POST["Representative"]); // обновить и сохранить данные				} else {					// Иначе создаем нового представителя					if ($Representative = Representative::add($_POST["Representative"])) {						$Request->Student->addRelation("Representative", $Representative); // добавляем вместе с видимостью $Request->Representative					}				}				# ПАСПОРТ ПРЕДСТАВИТЕЛЯ				// Если паспорт найден				if ($Request->Student->Representative->Passport) {					$Request->Student->Representative->Passport->update($_POST["Passport"]);				} else {					// Если данные паспорта были введены					if (hasValues($_POST["Passport"])) {						$Passport = Passport::add($_POST["Passport"] + ["type" => Passport::TYPE_REPRESENTATIVE]); // добавляем тип по умолчанию						$Request->Student->Representative->addRelation("Passport", $Passport, true); // добавляем взаимосвязь и сохраняем id_passport					}				}				// Сохраняем все изменения по студенту				$Request->Student->save();			}			// Сохраняем все изменения заявки			$Request->save();		}		/**		 * Получить по странице и списку.		 *		 */		public function actionAjaxGetByPage()		{			extract($_GET);			returnJSON(Request::getByPage($page, $id_status));		}				/**		 * Получить по странице и списку.		 *		 */		public function actionAjaxGetByPageRelevant()		{			extract($_GET);			returnJSON([				"requests" => Request::getByPageRelevant($page, $grade, $id_branch, $id_subject),				"requests_count" => Request::countByPageRelevant($grade, $id_branch, $id_subject)			]);		}		/**		 * Найти студента по ID.		 *		 */		public function actionAjaxGetStudent()		{			extract($_GET);			returnJSON(Student::findById($id));		}		/**		 * Присвоить заявку ученику (склейка клиентов).		 *		 */		public function actionAjaxGlueRequest()		{			extract($_POST);						$Request = Request::findById($id_request);			returnJSON($Request->bindToStudent($id_student, $delete_student));		}				public function actionAjaxChangeStatus()		{			extract($_POST);						Request::updateById($id_request, [				"id_status" => $id_request_status			]);		}				public function actionAjaxSaveMarkers()		{			extract($_POST);						preType($_POST);						Student::addMarkersStatic($markers, $id_student);		}	}