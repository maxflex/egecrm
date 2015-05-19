<?php	// Контроллер	class RequestController extends Controller	{		public $defaultAction = "List";				// Папка вьюх		protected $_viewsFolder	= "request";								/**		 * Включаем Джей-ЭС.		 * 		 */		public function beforeAction()		{			// Если пользователь уже вошел, либо была галка "запомнить",			// то редиректим на профиль			if (User::loggedIn() || User::rememberMeLogin(false)) {				$this->addJs("ng-request-app");			} else {				$this->redirect(BASE_ADDON . "login"); // Если не удалось войти по кукам			}		}				/**		 * Информация по заявке.		 * 		 */		public function actionEdit()		{			$this->setTabTitle("Редактирование заявки");			$this->addJs("bootstrap-select");			$this->addCss("bootstrap-select");						// Находим заявку по ID			$Request = Request::findById($_GET["id"]);						// Добавляем JS			$this->addJs("jquery.ui.widget, jquery.iframe-transport, jquery.fileupload");									$this->render("edit", [				"Request"	=> $Request,				"User"		=> User::fromSession()			]);		}				public function actionList()		{			$this->setTabTitle("Заявки");						// Получаем все заявки			$Requests = Request::findAll();						$this->render("list", [				"Requests"	=> $Requests			]);		}				##################################################		###################### AJAX ######################		##################################################								/**		 * Редактирование заявки – основное сохранение.		 * 		 */		public function actionAjaxSave()		{				preType($_FILES);//			preType($_POST);			# ЗАЯВКА			// Получаем ID заявки			$id_request = $_POST["id_request"];								// Находим заявку			$Request = Request::findById($id_request);						// Обновляем данные (без сохранения, сохраняем в конце)			$Request->update($_POST["Request"], false);						// Если это первое сохранение, запоминаем данные сохранившего			if (!$Request->id_first_save_user) {				$Request->id_first_save_user 	= User::fromSession()->id;				$Request->first_save_date 		= now();			}						# СТУДЕНТ			// Если студент найден			if ($Request->Student) {				$Request->Student->update($_POST["Student"]); // обновить и сохранить данные			} else {				// инача создаем нового студента				if ($Student = Student::add($_POST["Student"])) {					$Request->addRelation("Student", $Student);				}			}						# СВОБОДНОЕ ВРЕМЯ СТУДЕНТА			Freetime::addData($_POST["freetime_json"], $Request->Student->id);						# ПРЕДСТАВИТЕЛЬ			// Если есть представитель			if ($Request->Representative) {				$Request->Representative->update($_POST["Representative"]); // обновить и сохранить данные			} else {				// Иначе создаем нового представителя				if ($Representative = Representative::add($_POST["Representative"])) {					$Request->addRelation("Representative", $Representative); // добавляем вместе с видимостью $Request->Representative				}			}										# ПАСПОРТ ПРЕДСТАВИТЕЛЯ			// Если паспорт найден			if ($Request->Representative->Passport) {				$Request->Representative->Passport->update($_POST["Passport"]);			} else {				// Если данные паспорта были введены				if (hasValues($_POST["Passport"])) {					$Passport = Passport::add($_POST["Passport"] + ["type" => Passport::TYPE_REPRESENTATIVE]); // добавляем тип по умолчанию					$Request->Representative->addRelation("Passport", $Passport, true); // добавляем взаимосвязь и сохраняем id_passport				}			}							# ДОГОВОРЫ			// Если договор уже существует			if ($Request->Contract) {				$Request->Contract->update($_POST["Contract"]);			} else {				if ($Contract = Contract::add($_POST["Contract"])) {					$Request->addRelation("Contract", $Contract); // Добавляем взаимосвязь с контрактом				}			}						# ПРЕДМЕТЫ ДОГОВОРА			ContractSubject::addData($_POST["subjects_json"], $Request->Contract->id);						# ПЛАТЕЖИ ЗАЯВКИ			Payment::addData($_POST["Payment"], $Request->id);						// Сохраняем все изменения заявки			$Request->save();		}					}