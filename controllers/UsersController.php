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

			$ActiveUsers = User::findAll([
				"condition" => "type='USER' AND NOT (FIND_IN_SET(" . Shared\Rights::EC_BANNED . ", rights) AND FIND_IN_SET(" . Shared\Rights::ER_BANNED . ", rights))",
				"order"     => "id ASC"
			]);

			$BannedUsers = User::findAll([
				"condition" => "type ='USER' AND (FIND_IN_SET(" . Shared\Rights::EC_BANNED . ", rights) AND FIND_IN_SET(" . Shared\Rights::ER_BANNED . ", rights))",
				"order"     => "id ASC"
			]);

			foreach ($ActiveUsers as &$User) {
				$User = $User->dbData(["id", "login", "rights"]);
			}
			foreach ($BannedUsers as &$User) {
				$User = $User->dbData(["id", "login", "rights"]);
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

            // если пытаемся отредактировать суперпользователя
            if ($User->allowed(Shared\Rights::IS_SUPERUSER) && ! allowed(Shared\Rights::IS_SUPERUSER)) {
                $this->renderRestricted();
            }

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
			foreach ($Users as $User) {
                # суперпользователя нельзя редактировать
                if (in_array(Shared\Rights::IS_SUPERUSER, $User['rights'])) {
                    exit('superuser');
                }
				if (! empty($User['new_password'])) {
					$User['password'] = User::password($User['new_password']);
				}
                unset($User['photo_extension']);
                unset($User['has_photo_cropped']);
                // если убрали все права
                if (! isset($User['rights'])) {
                    $User['rights'] = [];
                }
                $User['updated_at'] = now();
				User::updateById($User['id'], $User);
			}

            if (User::fromSession()->id == $User['id']) {
                User::findById($User['id'])->toSession();
            }

			# обновить кеш
			User::updateCache();

            exit('success');
		}

        public function actionAjaxCreate() {

            $User = new User($_POST["user"]);
            $User->password = User::password($User->password);
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
				$cnt = User::count(["condition" => "login = '{$login}'"]);
				echo $cnt;
			}
		}

		public function actionGet()
        {
            $id = isset($_GET['id']) ? intval($_GET['id']) : false;
            $data = $id ? User::findById($id)->dbData() : User::getCached();
            foreach ($data as &$user) {
                if ($user->id == User::fromSession()->id) {
                    $user->is_current = 1;
                }
            }

            returnJsonAng($data);
        }
	}
