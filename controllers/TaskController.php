<?php

	// Контроллер
	class TaskController extends Controller
	{
		public $defaultAction = "list";

		public static $allowed_users = [User::USER_TYPE];

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

			// dev only
			$search = $_GET["search"];
			$limit = $_GET["limit"];

			// не надо панель рисовать
			$this->_custom_panel = true;

			if ($id) {
				$Tasks = [Task::findById($id)];
			} else {
				if ($list) {
					$condition = "html IS NOT NULL AND id_status=" . $list;
					if (isset($search)) {
						$condition .= " AND html LIKE '%{$search}%'";
					}
					$Tasks = Task::findAll([
						"condition" => $condition,
						"order"		=> "id DESC",
						"limit"		=> $list == 8 ? 50 : 50,
					]);
				} else {
					$condition =  "html IS NOT NULL AND id_status!=" . TaskStatuses::CLOSED;
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
				"user"          => User::fromSession()->dbData()
			]);

			$this->render("list", [
				"Tasks" => $Tasks,
				"ang_init_data" => $ang_init_data
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
