<?php
	class Controller
	{
		// Экшн по умолчанию
		public $defaultAction = "Main";
		
		// Заголовок по умолчанию
		protected $_html_title	= "ЕГЭ Центр";
		protected $_add_title	= " | "; // Будет добавляться к TITLE текущей страницы
		
		// Заголовок таба
		private $_tab_title = "";
		
		// Папка VIEWS
		protected $_viewsFolder	= "";
		
		// Дополнительный JS
		protected $_js_additional = "";
		
		// Дополнительный CSS
		protected $_css_additional = "";
		
		public static $allowed_users = [User::USER_TYPE];
		
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
		 * $layout – кастомный лэйаут, по умолчанию меню
		 * @todo: изменить футер под кастомный лэйаут DONE
		 */
		protected function render($view, $vars = array(), $layout = false)
		{
			if (!$layout) {
				$layout = strtolower(User::fromSession()->type);
			}
			
			// Рендер лэйаута
			include_once(BASE_ROOT."/layouts/header.php");
			include_once(BASE_ROOT."/layouts/{$layout}.php");	

			// Если передаем переменные в рендер, то объявляем их здесь (иначе будут недоступны)
			if (!empty($vars)) {
				// Объявляем переменные, соответсвующие элементам массива
				foreach ($vars as $key => $value) {
					$$key = $value;
				}
			}
			
			include_once(BASE_ROOT."/views/".(!empty($this->_viewsFolder) ? $this->_viewsFolder."/" : "")."{$view}.php");
			
			// Рендер лэйаута
			include_once(BASE_ROOT."/layouts/{$layout}_footer.php");
		}
		
		/*
		 * Отобразить "Недостаточно прав"
		 */
		public function renderRestricted($layout = false)
		{
			if (!$layout) {
				$layout = strtolower(User::fromSession()->type);
			}
			
			$this->setTabTitle("Ошибка");
				
			// Рендер лэйаута
			include_once(BASE_ROOT."/layouts/header.php");
			include_once(BASE_ROOT."/layouts/{$layout}.php");	

			include_once(BASE_ROOT."/views/_partials/_access_restricted.php");
						
			// Рендер лэйаута
			include_once(BASE_ROOT."/layouts/{$layout}_footer.php");
			
			exit();
		}
		
		
		/**
		 * Установить права доступа к контроллеру.
		 * 
		 * @access protected
		 * @param mixed $allowed_users (default: [User::USER_TYPE]) Только пользователям по умолчанию
		 * @return void
		 */
		protected function setRights($allowed_users = [User::USER_TYPE])
		{
			if (!in_array(User::fromSession()->type, $allowed_users)) {
				$this->renderRestricted();
			}
		}
		
		/*
		 * Редирект
		 *
		 * $from_base – редирект от базового URL
		 */
		protected function redirect($location, $from_base = false)
		{
			header("Location: ".($from_base ? BASE_ADDON : "")."{$location}");
		}
		
		/*
		 * Редирект на предыдущую страницу
		 */
		protected function refererRedirect()
		{
			header("Location: {$_SERVER['HTTP_REFERER']}");
		}
		
		/*
		 * Обновить текущую страницу
		 */
		protected function refresh()
		{
			header('Location: '.$_SERVER['REQUEST_URI']);
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
		
		
		/**
		 * Установить заголовок таба.
		 * 
		 */
		protected function setTabTitle($title)
		{
			$this->_tab_title = $title;
		}
		
		/**
		 * Установить заголовок таба.
		 * 
		 */
		protected function setRightTabTitle($title)
		{
			$this->_tab_title .= '<span class="pull-right">'. $title . '</span>';
		}
		
		public function tabTitle()
		{
			return $this->_tab_title;
		}
	}
?>