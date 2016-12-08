<?php	// Контроллер	class UsersController extends Controller	{		public $defaultAction = "list";		// Папка вьюх		protected $_viewsFolder	= "users";        public function beforeAction()		{			$this->addJs("ng-users-app");		}		public function actionList()		{            # @rights-refactored            $this->checkRights(Shared\Rights::SHOW_USERS);			$this->setTabTitle("Пользователи");			$this->setRightTabTitle('<a href="users/create" class="link-reverse link-white">добавить  нового пользователя</a>');			$Users = User::findAll([				"condition" => "type='USER' OR type='SEO'",				"order" => "type DESC"			]);			foreach ($Users as &$User) {				$User = $User->dbData(["id", "login", "banned", "banned_egerep", "agreement", "color"]);			}			$ang_init_data = angInit([				"Users" => $Users,			]);			$this->render("list", [				"ang_init_data" => $ang_init_data,			]);		}        public function actionEdit()		{            if (!($id = intval($_GET['id']))) {                $this->redirect("users");            }            $this->setTabTitle("Пользователь #{$id}");			$User = User::findById($id);			$ang_init_data = angInit([				"User" => $User,			]);			$this->render("edit", [				"ang_init_data" => $ang_init_data,			]);		}		public function actionCreate()		{			$this->setTabTitle("Добавление нового пользователя");			$this->setRightTabTitle('<a href="users" class="link-reverse link-white">к списку пользователей</a>');			$User = new User;			$ang_init_data = angInit([				"User" => $User,			]);			$this->render("create", [				"ang_init_data" => $ang_init_data,			]);		}		public function actionContract()        {            // @rights-refactored            $this->checkRights(Shared\Rights::SHOW_CONTRACT);            $this->_custom_panel = true;            $ang_init_data = angInit([                'contract_html' => dbEgerep()->query("select `value` from settings where `key` = 'contract_html'")->fetch_object()->value,                'contract_date' => dbEgerep()->query("select `value` from settings where `key` = 'contract_date'")->fetch_object()->value,            ]);            $this->render("contract", [                "ang_init_data" => $ang_init_data,            ]);        }		public function actionAjaxSave()		{			$Users = $_POST["Users"];			foreach ($Users as $User) {				if (!empty($User['new_password'])) {					$User['password'] = User::password($User['new_password']);				}                unset($User['photo_extension']);                unset($User['has_photo_cropped']);				User::updateById($User['id'], $User);			}            if (User::fromSession()->id == $User['id']) {                User::findById($User['id'])->toSession();            }			# обновить кеш			User::updateCache();		}        public function actionAjaxCreate() {            $User = new User($_POST["user"]);            $User->password = User::password($User->password);            echo $User->save();            # обновить кеш            User::updateCache();        }        public function actionAjaxDeletePhoto() {            extract($_POST);            $User = User::findById($user_id);            unlink($User->photoPath());            unlink($User->photoPath('_original'));            $User->photo_extension = '';            $User->has_photo_cropped = 0;            $User->save();        }		public function actionAjaxExists() {			extract($_POST);			if ($login) {				$cnt = User::count(["condition" => "login = '{$login}'"]);				echo $cnt;			}		}		public function actionGet()        {            $id = isset($_GET['id']) ? intval($_GET['id']) : false;            $data = $id ? User::findById($id)->dbData() : User::getCached();            foreach ($data as &$user) {                if ($user->id == User::fromSession()->id) {                    $user->is_current = 1;                }            }            returnJsonAng($data);        }	}