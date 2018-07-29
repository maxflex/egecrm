<?php

	// Контроллер
	class UsersController extends Controller
	{
		public $defaultAction = "list";

		// Папка вьюх
		protected $_viewsFolder	= "users";

        public function beforeAction()
		{
			$this->addJs("ng-users-app");
		}

		public function actionList()
		{
            # @rights-refactored
            $this->checkRights(Shared\Rights::SHOW_USERS);

			$this->setTabTitle("Пользователи");
			$this->setRightTabTitle('<a href="users/create" class="link-reverse link-white">добавить  нового пользователя</a>');

			$Users = User::findAll([
				"condition" => "type = '" . Admin::USER_TYPE . "'",
				"order"     => "id ASC"
			]);

			$ActiveUsers = [];
			$BannedUsers = [];
			foreach($Users as $User) {
				if ($User->allowed(Shared\Rights::EC_BANNED)) {
					$BannedUsers[] = $User->only('id', 'login', 'rights', 'id_entity');
				} else {
					$ActiveUsers[] = $User->only('id', 'login', 'rights', 'id_entity');
				}
			}

			$ang_init_data = angInit([
				"ActiveUsers" => $ActiveUsers,
				"BannedUsers" => $BannedUsers,
                "Rights" => Shared\Rights::$all,
                "Groups" => Shared\Rights::$groups,
			]);

			$this->render("list", [
				"ang_init_data" => $ang_init_data,
			]);
		}

        public function actionEdit()
		{
            # @rights-refactored
            $this->checkRights(Shared\Rights::SHOW_USERS);

            if (!($id = intval($_GET['id']))) {
                $this->redirect("users");
            }

			$User = User::findById($id);
			$User->ips = AdminIp::getAll($User->id_entity) ?: [];

            // если пытаемся отредактировать суперпользователя
            // if ($User->allowed(Shared\Rights::IS_SUPERUSER) && ! allowed(Shared\Rights::IS_SUPERUSER)) {
            //     $this->renderRestricted();
            // }

            // не надо панель рисовать
			$this->_custom_panel = true;

			$ang_init_data = angInit([
				"User" => $User,
			]);

			$this->render("edit", [
				"ang_init_data" => $ang_init_data,
			]);
		}

		public function actionCreate()
		{
            // не надо панель рисовать
			$this->_custom_panel = true;

			$User = new User;

			$ang_init_data = angInit([
				"User" => $User,
			]);

			$this->render("create", [
				"ang_init_data" => $ang_init_data,
			]);
		}

		public function actionAjaxSave()
		{
            # @rights-refactored
            $this->checkRights(Shared\Rights::SHOW_USERS);

			$Users = $_POST["Users"];

			foreach($Users as $User) {
				# суперпользователя нельзя редактировать
	            // if (in_array(Shared\Rights::IS_SUPERUSER, $User['rights'])) {
	            //     exit('superuser');
	            // }

				Admin::edit($User);

	            if (User::id() == $User['id_entity']) {
	                User::findById($User['id'])->toSession();
	            }
			}

			# обновить кеш
			User::updateCache();

            exit('success');
		}

        public function actionAjaxCreate()
		{
			unset($_POST["user"]['mysql_vars']); // иначе админу присваивается mysql_vars от передаваемого new User
			$Admin = Admin::add($_POST["user"]);
            $User = new User($_POST["user"]);
			$User->id_entity = $Admin->id;
			$User->type = Admin::USER_TYPE;
            echo $User->save();

            # обновить кеш
            User::updateCache();
        }

        public function actionAjaxDeletePhoto() {
            extract($_POST);
            $User = User::findById($user_id);

            unlink($User->photoPath());
            unlink($User->photoPath('_original'));

            $User->photo_extension = '';
            $User->has_photo_cropped = 0;
            $User->save();
        }

		public function actionAjaxExists() {
			extract($_POST);
			if ($login) {
				$cnt = Admin::count(["condition" => "login = '{$login}'"]);
				echo $cnt;
			}
		}

		public function actionGet()
        {
            $id = isset($_GET['id']) ? intval($_GET['id']) : false;
            $data = $id ? User::findById($id) : User::getCached();
            foreach ($data as &$user) {
                if ($user->id == User::id()) {
                    $user->is_current = 1;
                }
            }

            returnJsonAng($data);
        }
	}
