<?php
	class PromoController extends Controller
	{
		public $defaultAction = "main";
		
		public static $allowed_users = [Student::USER_TYPE];
		
		// Папка вьюх
		protected $_viewsFolder	= "promo";
		
		public function beforeAction()
		{
			$this->addJs("ng-promo-app, flipclock.min");
			$this->addCss("flipclock");
			
			User::fromSession()->promoVisit();
		}
		
		public function actionMain()
		{
			$this->setTabTitle("Промо");
			
			$ang_init_data = angInit([
				"Student" => Student::findById(User::fromSession()->id_entity),
			]);
			
			$this->render("main", [
				"ang_init_data" => $ang_init_data,
			]);
		}
	}