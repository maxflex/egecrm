<?php

	// Контроллер
	class AjaxController extends Controller
	{
		// 
		public $defaultAction = "default";
		
		// Папка вьюх
		protected $_viewsFolder	= "";
				
		
		
		##################################################
		###################### AJAX ######################
		##################################################
		
		
		/**
		 * Добавление комментария.
		 * 
		 */
		public function actionAjaxAddComment()
		{
			$Comment = Comment::add($_POST);
			
			// Возвращаем форматированную дату и ник пользователя
			toJson([
				"date" => date("d.m.y в H:i", time()),
				"user" => User::fromSession()->login,
			]);
		}
		
	}