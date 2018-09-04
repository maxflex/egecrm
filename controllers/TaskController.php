<?php

	// Контроллер
	class TaskController extends Controller
	{
		public $defaultAction = "list";

		public static $allowed_users = [Admin::USER_TYPE];

		// Папка вьюх
		protected $_viewsFolder	= "tasks";

		public function beforeAction()
		{
            # @rights-refactored
            $this->checkRights(Shared\Rights::SHOW_TASKS);
			$this->addJs("ng-task-app, dnd");
		}

		public function actionList()
		{
			$list = $_GET["list"];
			$id = $_GET["id"];

			if (allowed(Shared\Rights::IS_SUPERUSER)) {
				$id_user_responsible = @$_GET['user'];
			} else {
				$id_user_responsible = User::id();
			}

			// dev only
			$search = $_GET["search"];
			$limit = $_GET["limit"];

			// не надо панель рисовать
			$this->_custom_panel = true;

			if ($id) {
				$Tasks = [Task::findById($id)];
			} else {
				$user_responsible_condition = ($id_user_responsible !== null && $id_user_responsible !== '') ? " AND id_user_responsible={$id_user_responsible}" : '';
				if ($list) {
					$condition = "html IS NOT NULL AND id_status=" . $list . $user_responsible_condition;
					if (isset($search)) {
						$condition .= " AND html LIKE '%{$search}%'";
					}
					$Tasks = Task::findAll([
						"condition" => $condition,
						"order"		=> "id DESC",
						"limit"		=> $list == 8 ? 50 : 50,
					]);
				} else {
					$condition =  "html IS NOT NULL AND id_status!=" . TaskStatuses::CLOSED . $user_responsible_condition;
					if (isset($search)) {
						$condition .= " AND html LIKE '%{$search}%'";
					}
					$Tasks = Task::findAll([
						"condition" => $condition,
						"order"		=> "id DESC",
	                    "limit"     => $_GET["limit"] ? $_GET["limit"] : 100,
					]);
				}
			}

			$ang_init_data = angInit([
				"Tasks"         => $Tasks,
				"task_statuses" => TaskStatuses::$all,
				"user"          => User::fromSession()
			]);

			$this->render("list", [
				"Tasks" => $Tasks,
				"ang_init_data" => $ang_init_data,
				"id_user_responsible" => $id_user_responsible,
			]);
		}

		public function actionAjaxSave()
		{
			extract($_POST);

			if ($Task['delete'] == 1) {
				Task::deleteById($Task['id']);
			} else {
				echo Task::updateById($Task['id'], $Task) ? 1 : 0;
			}
		}

		public function actionAjaxAdd()
		{
			returnJSON(Task::add()->id);
		}

	}
