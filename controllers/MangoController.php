<?php
	// Контроллер
	class MangoController extends Controller
	{
		// Папка вьюх
		protected $_viewsFolder	= "mango";
		
		public function actionCall()
		{
// 			h1("here");
// 			$this->addJs("https://cdn.socket.io/socket.io-1.4.5.js", true);
			$this->addJs("//js.pusher.com/3.0/pusher.min.js", true);
			$this->setTabTitle('Mango');
			$this->render("call");
// 			Email::send("makcyxa-k@yandex.ru", "Mango Info", json_encode($_POST));
		}
		
		public function actionSocket()
		{
			Socket::trigger('my_channel', 'new_message', ['testy' => 'yeah']);
		}
	}