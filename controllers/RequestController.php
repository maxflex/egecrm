<?php	// Контроллер	class RequestController extends Controller	{		public $defaultAction = "List";				// Папка вьюх		protected $_viewsFolder	= "request";								/**		 * BEFORE ACTION.		 * 		 */		public function beforeAction()		{			// Скрипт контроллера			$this->addJs("ng-request-app");		}				/**		 * Информация по заявке.		 * 		 */		public function actionEdit()		{ 			// Находим заявку по ID			$Request = Request::findById($_GET["id"]);						// Добавляем заголовок, в него пишем дубликаты заявки, если есть			$this->setTabTitle("Редактирование заявки" .  $Request->generateDuplicatesHtml());						// Добавляем JS и CSS			$this->addJs("bootstrap-select, jquery.ui.widget, jquery.iframe-transport, jquery.fileupload, gmaps, gmaps.functions");			$this->addJs("//maps.google.ru/maps/api/js", true);			$this->addCss("bootstrap-select");							# Генерируем данные для ангуляра			$ang_init_data = angInit([				# Основные данные				"payment_statuses"	=> Payment::$all,				"user"				=> User::fromSession()->dbData(),				"request_duplicates"=> $Request->getDuplicates(),				# Данные ученика				"server_markers"=> $Request->Student->getMarkers(),		// Метки ученика				"payments"		=> $Request->Student->getPayments(),	// Платежи ученика				"freetime"		=> $Request->Student->getFreetime(),	// Свободное время ученика				"contracts"		=> $Request->Student->getContracts(),	// Договоры ученика				"student"		=> $Request->Student->dbData(["first_name", "last_name", "middle_name"])  // Данные по ученику для печати			]);						# Передача во view			$this->render("edit", [				"Request"		=> $Request,				"User"			=> User::fromSession(),				"ang_init_data"	=> $ang_init_data,			]);		}						/**		 * Список заявок.		 * 		 */		public function actionList()		{			$this->setTabTitle("Заявки");						// Получаем новые заявки			$Requests = Request::getByPage(1, RequestStatuses::NEWR);									// Получаем сколько новых, в отказе и тд.			$RequestStatusesCount = Request::getAllStatusesCount();						// Данные для ангуляра			$ang_init_data = angInit([				"requests" 				=> $Requests,				"request_statuses"		=> RequestStatuses::$all,				"request_statuses_count"=> $RequestStatusesCount			]);						$this->render("list", [				"ang_init_data"	=> $ang_init_data,			]);		}								##################################################		###################### AJAX ######################		##################################################				/**		 * Редактирование заявки – основное сохранение.		 * 		 */		public function actionAjaxSave()		{				preType($_POST); // debug info//			preType($_FILES, true);						# ЗАЯВКА			// Получаем ID заявки			$id_request = $_POST["id_request"];								// Находим заявку			$Request = Request::findById($id_request);						// Обновляем данные (без сохранения, сохраняем в конце)			$Request->update($_POST["Request"], false);						// Если это первое сохранение, запоминаем данные сохранившего			if (!$Request->id_first_save_user) {				$Request->id_first_save_user 	= User::fromSession()->id;				$Request->first_save_date 		= now();			}						# НАПОМИНАНИЕ			if ($Request->Notification) {				$Request->Notification->update($_POST["Notification"]); // обновить и сохранить данные			} else {				// инача создаем новое уведомление				if ($Notification = Notification::add($_POST["Notification"])) {					$Request->addRelation("Notification", $Notification);				}			}						# СТУДЕНТ			// Если студент найден			if ($Request->Student) {				$Request->Student->update($_POST["Student"]); // обновить и сохранить данные			} else {				// инача создаем нового студента				if ($Student = Student::add($_POST["Student"])) {					$Request->addRelation("Student", $Student);				}			}						// Если добавили студента (ИНАЧЕ ВООБЩЕ НИКАКИЕ ДАННЫЕ НЕ СОХРАНЯТСЯ)			if ($Request->Student) {				# МЕТКИ СТУДЕНТА				$Request->Student->addMarkers($_POST["marker_data"]);								# СВОБОДНОЕ ВРЕМЯ СТУДЕНТА				Freetime::addData($_POST["freetime_json"], $Request->Student->id);								# ПРЕДСТАВИТЕЛЬ				// Если есть представитель у ученика				if ($Request->Student->Representative) {					$Request->Student->Representative->update($_POST["Representative"]); // обновить и сохранить данные				} else {					// Иначе создаем нового представителя					if ($Representative = Representative::add($_POST["Representative"])) {						$Request->Student->addRelation("Representative", $Representative); // добавляем вместе с видимостью $Request->Representative					}				}								# ПАСПОРТ ПРЕДСТАВИТЕЛЯ				// Если паспорт найден				if ($Request->Student->Representative->Passport) {					$Request->Student->Representative->Passport->update($_POST["Passport"]);				} else {					// Если данные паспорта были введены					if (hasValues($_POST["Passport"])) {						$Passport = Passport::add($_POST["Passport"] + ["type" => Passport::TYPE_REPRESENTATIVE]); // добавляем тип по умолчанию						$Request->Student->Representative->addRelation("Passport", $Passport, true); // добавляем взаимосвязь и сохраняем id_passport					}				}								# ДОГОВОРЫ				// Добавляем новые договоры и обновляем измененные существующие				Contract::addAndUpdate($_POST["Contract"], $Request->Student->id);/*				// Если договор уже существует				if ($Request->Student->Contract) {					$Request->Student->Contract->update($_POST["Contract"]);				} else {					if ($Contract = Contract::add($_POST["Contract"])) {						$Request->Student->addRelation("Contract", $Contract); // Добавляем взаимосвязь с контрактом					}				}								# ПРЕДМЕТЫ ДОГОВОРА				ContractSubject::addData($_POST["subjects_json"], $Request->Contract->id);*/								# ПЛАТЕЖИ ЗАЯВКИ				Payment::addData($_POST["Payment"], $Request->Student->id);								// Сохраняем все изменения по студенту				$Request->Student->save();			}												// Сохраняем все изменения заявки			$Request->save();		}						/**		 * Получить по странице и списку.		 * 		 */		public function actionAjaxGetByPage()		{			extract($_GET);			//			preType([$_GET, $page, $id_status]);//			preType(Request::getByPage($page, $id_status), true);						returnJSON(Request::getByPage($page, $id_status));		}	}