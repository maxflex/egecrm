<?php
	// Контроллер
	class MangoController extends Controller
	{
		// Папка вьюх
		protected $_viewsFolder	= "mango";

		public function actionEventCall()
		{
			$data = json_decode($_POST['json']);
			if ($data->to->line_number == '74955653170') {
				// Email::send("makcyxa-k@yandex.ru", "Mango Info", json_encode($data));
				Socket::trigger('user_' . $data->to->extension, 'incoming', $data);
			}
		}

		public function actionHangup()
		{
			extract($_POST);
			Mango::hangup($call_id);
		}

		public function actionSocket()
		{
			$json = '{"vpbx_api_key":"goea67jyo7i63nf4xdtjn59npnfcee5l","sign":"683a6a5a558a73c0fe11407bc3e918210344240990c70f23d3e8df7b56415dfe","json":"{\"entry_id\":\"MTg3OTAxOTIyMjozNjI=\",\"call_id\":\"MTo2MTQ1MTozNjI6ODIxNjUwOTg=\",\"timestamp\":1454323275,\"seq\":1,\"call_state\":\"Appeared\",\"from\":{\"number\":\"79686120551\"},\"to\":{\"extension\":\"25\",\"number\":\"sip:danila@kapralovka.mangosip.ru\",\"line_number\":\"74956461080\"}}"}';
			$post = json_decode($json);
			$data = json_decode($post->json);
			Socket::trigger('user_69', 'incoming', $data);
		}

		public function actionSockett()
		{
			$json = '{"vpbx_api_key":"goea67jyo7i63nf4xdtjn59npnfcee5l","sign":"683a6a5a558a73c0fe11407bc3e918210344240990c70f23d3e8df7b56415dfe","json":"{\"entry_id\":\"MTg3OTAxOTIyMjozNjI=\",\"call_id\":\"MTo2MTQ1MTozNjI6ODIxNjUwOTg=\",\"timestamp\":1454323275,\"seq\":1,\"call_state\":\"Connected\",\"from\":{\"number\":\"79686120551\"},\"to\":{\"extension\":\"25\",\"number\":\"sip:danila@kapralovka.mangosip.ru\",\"line_number\":\"74956461080\"}}"}';
			$post = json_decode($json);
			$data = json_decode($post->json);
			Socket::trigger('user_69', 'incoming', $data);
		}


		public function actionGetCaller()
		{
			extract($_POST);

            // Ищем учителя с таким номером
            $Teacher = Teacher::find([
                "condition"	=> "phone='".$phone."' OR phone2='".$phone."' OR phone3='".$phone."'"
            ]);
            if ($Teacher) {
                returnJsonAng([
                    'name'	=> $Teacher->getInitials(),
                    'type'	=> 'teacher',
                    'id'	=> $Teacher->id,
                ]);
            }

            # Ищем представителя с таким же номером телефона
            $represetative = dbConnection()->query("
				SELECT s.id, r.first_name, r.last_name FROM ".Representative::$mysql_table." r
				LEFT JOIN ".Student::$mysql_table." s on r.id = s.id_representative
				WHERE r.phone='".$phone."' OR r.phone2='".$phone."' OR r.phone3='".$phone."'"
            );

            // Если заявка с таким номером телефона уже есть, подхватываем ученика оттуда
            if ($represetative->num_rows) {
                $data = $represetative->fetch_object();
                returnJsonAng([
                    'name'	=> $data->last_name . ' ' . $data->first_name,
                    'type'	=> 'representative',
                    'id'	=> $data->id,
                ]);
            }

            # Ищем ученика с таким же номером телефона
			$Student = Student::find([
				"condition"	=> "phone='".$phone."' OR phone2='".$phone."' OR phone3='".$phone."'"
			]);

			// Если заявка с таким номером телефона уже есть, подхватываем ученика оттуда
			if ($Student) {
				returnJsonAng([
					'name'	=> $Student->name('fi'),
					'type'	=> 'client',
					'id'	=> $Student->id,
				]);
			}

			# Ищем заявку с таким же номером телефона
			$Request = Request::find([
				"condition"	=> "phone='".$phone."' OR phone2='".$phone."' OR phone3='".$phone."'"
			]);

			// Если заявка с таким номером телефона уже есть, подхватываем ученика оттуда
			if ($Request) {
				returnJsonAng([
					'name'	=> $Request->name,
					'type'	=> 'request',
					'id'	=> $Request->id,
				]);
			}

			// возвращается, если номера нет в базе
			returnJsonAng(false);
		}
	}
