<?php

	// Контроллер
	class TestController extends Controller
    {
        public $defaultAction = "test";
        // Папка вьюх
        protected $_viewsFolder = "test";

        public function actionTest()
        {
            $client = new Predis\Client();
            $count = $client->get('egerep-web:constants:TRANSPORT_DISTANCE');
            var_dump($count);
        }
	}
