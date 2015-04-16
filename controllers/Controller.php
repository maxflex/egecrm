<?php
	class Controller
	{
		// Экшн по умолчанию
		public $defaultAction = "Main";
		
		// Заголовок по умолчанию
		protected $_html_title	= "Ratie | Анонимные мнения о друзьях, комментарии и оценки";
		protected $_add_title	= " | Анонимные мнения о друзьях, комментарии и оценки"; // Будет добавляться к TITLE текущей страницы
		
		// Папка VIEWS
		protected $_viewsFolder	= "";
		
		// Дополнительный JS
		protected $_js_additional = "";
		
		// Дополнительный CSS
		protected $_css_additional = "";
		
		/*// Проверка на аякс запрос
		private function _isAjaxRequest()
		{
			// Проверка на аякс-запрос
			if (strtolower(mb_strimwidth($_action, 0, 4)) == "ajax") {
				
				$_ajax_request = true;
				
				// Это аякс-запрос, к скрипту можно обращаться только через AJAX
				if (strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
					die("SECURITY RESTRICTION: THIS PAGE ACCEPTS AJAX REQUESTS ONLY (poshel nahuj)");	// Выводим мега-сообщение
				}
			} else {
				$_ajax_request = false;
			}
		} */
		
		/*
		 * Отобразить view
		 */
		protected function render($view, $vars = array())
		{
			// Рендер лэйаута
			include_once(BASE_ROOT."/layouts/header.php");
			include_once(BASE_ROOT."/layouts/menu.php");	

			// Если передаем переменные в рендер, то объявляем их здесь (иначе будут недоступны)
			if (!empty($vars)) {
				// Объявляем переменные, соответсвующие элементам массива
				foreach ($vars as $key => $value) {
					$$key = $value;
				}
			}
			
			include_once(BASE_ROOT."/views/".(!empty($this->_viewsFolder) ? $this->_viewsFolder."/" : "")."{$view}.php");
			
			// Рендер лэйаута
			include_once(BASE_ROOT."/layouts/footer.php");
		}
		
		
		/*
		 * Редирект
		 */
		protected function redirect($location)
		{
			header("Location: {$location}");
		}
		
		/*
		 * Указываем заголовк HTML
		 * $add_website_name – добавлять $_add_title к указанному $title 
		 */
		protected function htmlTitle($title, $add_website_name = false)
		{
			$this->_html_title = $title;
			
			if ($add_website_name) {
				$this->_html_title .= $this->_add_title;
			}
		}
		
		/*
		 * Добавляет JavaScript
		 * Добавление скриптов через запятую ( addJs('script_1, script_2') )
		 * $side – подключается сторонний JS (не размещенний на сайте в папке /js) (тогда скрипт передается строкой)
		 */
		protected function addJs($js, $side = false)
		{			
			// если подключается сторонний JS
			if ($side) {
				$this->_js_additional .= "<script src='$js' type='text/javascript'></script>";
			} else {
			// подключаем внутренний JS
				$js = explode(", ", $js);
				
				foreach ($js as $script_name) {
					$this->_js_additional .= "<script src='js/{$script_name}.js?ver=".settings()->version."' 
												type='text/javascript'></script>";
				}
			}
		}
		
		/*
		 * Добавляет CSS
		 */
		protected function addCss($css)
		{
			$css = explode(", ", $css);
			
			foreach ($css as $css_name) {
				$this->_css_additional .= "<link href='css/{$css_name}.css?ver=".settings()->version."' rel='stylesheet'>";
			}
		}
		
	}
?>