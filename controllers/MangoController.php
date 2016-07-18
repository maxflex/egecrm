<?php
	// Контроллер
	class MangoController extends Controller
	{
		const TEST_NUMBER = '74955653170';
		const APERS_NUMBER = '74956461080';
		const EGECENTR_NUMBER = '74956468592';

		// Папка вьюх
		protected $_viewsFolder	= "mango";
		
		public function actionResultStats()
		{
			$data = json_decode($_POST['json']);
			Email::send("makcyxa-k@yandex.ru", "Mango Info", json_encode($data));
		}
		
		public function actionEventCall()
		{
			$data = json_decode($_POST['json']);
			if (static::isEgecentrNumber($data->to->line_number)) {
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
            $last_call_data = $this->getLastCallData($phone);

            // Ищем учителя с таким номером
            $Teacher = Teacher::find([
                "condition"	=> "phone='".$phone."' OR phone2='".$phone."' OR phone3='".$phone."'"
            ]);
            if ($Teacher) {
                returnJsonAng([
                    'user'	=> $answeredUserName,
                    'name'	=> static::nameOrEmpty($Teacher->getFullName()),
                    'type'	=> 'teacher',
                    'id'	=> $Teacher->id,
                ]);
            }

            # Ищем представителя с таким же номером телефона
            $represetative = dbConnection()->query("
				SELECT s.id, r.first_name, r.last_name, r.middle_name FROM ".Representative::$mysql_table." r
				LEFT JOIN ".Student::$mysql_table." s on r.id = s.id_representative
				WHERE r.phone='".$phone."' OR r.phone2='".$phone."' OR r.phone3='".$phone."'"
            );

            // Если заявка с таким номером телефона уже есть, подхватываем ученика оттуда
            if ($represetative->num_rows) {
                $data = $represetative->fetch_object();
                returnJsonAng([
                    'user'	=> $answeredUserName,
                    'name'	=> static::nameOrEmpty(getName($data->first_name, $data->last_name, $data->middle_name)),
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
                    'last_call_data' => $last_call_data,
					'name'	=> static::nameOrEmpty($Student->name()),
					'type'	=> 'student',
					'id'	=> $Student->id
                ]);
			}

			# Ищем заявку с таким же номером телефона
			$Request = Request::find([
				"condition"	=> "phone='".$phone."' OR phone2='".$phone."' OR phone3='".$phone."'"
			]);

			// Если заявка с таким номером телефона уже есть, подхватываем ученика оттуда
			if ($Request) {
				returnJsonAng([
                    'user'	=> $answeredUserName,
					'name'	=> static::nameOrEmpty($Request->name),
					'type'	=> 'request',
					'id'	=> $Request->id
                ]);
			}

			// возвращается, если номера нет в базе
			returnJsonAng([
                'user' => $answeredUserName,
                'type' => false
            ]);
		}
		
		private function getAnsweredUser($phone) {
            $result = dbConnection()->query(
                "SELECT user_id FROM last_call_data ".
                "WHERE phone = '{$phone}' LIMIT 1"
            );

            if ($result && $result->num_rows) {
                $data = $result->fetch_assoc();
                return User::findById($data['user_id']);
            }
            return false;
        }
		
        private function getLastCallData($phone) {
			$stats = MangoNew::getStats($phone);
			
			foreach(array_reverse($stats) as $s) {
				// если это входящий звонок и разговора не было, не анализировать
				if ($s['to_extension'] && $s['answer'] == 0) {
					continue;
				}
				if ($s['from_extension']) {
					$s['user'] = User::findById($s['from_extension']);
					return $s;
				} 
				if ($s['to_extension']) {
					$s['user'] = User::findById($s['to_extension']);
					return $s;
				}
			}
			
            return false;
        }
        
        private static function nameOrEmpty($name)
        {
	        if (empty(trim($name))) {
		        return 'имя неизвестно';
	        } else {
		        return $name;
	        }
        }

        public function actionSaveCallState() {
            extract($_POST);
            $user_id = intval($user_id);
            dbConnection()->query(
                "INSERT INTO last_call_data (phone, user_id) ".
                "VALUES ('{$phone}', {$user_id}) ".
                "ON DUPLICATE KEY UPDATE user_id = {$user_id}"
            );
        }

        public static function isTestNumber($number) {
            return $number == static::TEST_NUMBER;
        }

        public static function isApersNumber($number) {
            return $number == static::APERS_NUMBER;
        }

        public static function isEgecentrNumber($number) {
            return $number == static::EGECENTR_NUMBER;
        }
	}
