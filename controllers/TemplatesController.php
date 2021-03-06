<?php

	// Контроллер
	class TemplatesController extends Controller
	{
		public $defaultAction = "list";

		// Папка вьюх
		protected $_viewsFolder	= "templates";

		public function beforeAction()
		{
			$this->addJs("ng-templates-app");
		}

		// Страница входа
		public function actionList()
		{
            # @rights-refactored
            $this->checkRights(Shared\Rights::SHOW_TEMPLATES);

			$this->setTabTitle("Редактирование шаблонов");

			$Templates = Template::findAll([
                'order' => 'type ASC'
            ]);

			$ang_init_data = angInit([
				"Templates" => $Templates,
			]);

			$this->render("list", [
				"ang_init_data" => $ang_init_data,
			]);
		}

		public function actionAjaxSave()
		{
			$Templates = $_POST['templates'];

			Template::deleteAll();

			foreach ($Templates as $Template)
			{
				unset($Template['id']);
				$Template['isNewRecord'] = 1;
				Template::add($Template);
			}
		}

		public function actionAjaxGet()
		{
			extract($_POST);

			echo Template::get($number, $params);
		}
	}
