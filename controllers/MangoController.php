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
			
			if ($memcached_return = memcached()->get("Caller[$phone]")) {
				returnJsonAng($memcached_return);
			}
			
            // Ищем учителя с таким номером
            $teacher = dbConnection()->query("
            	select id, first_name, last_name, middle_name from teachers
            	WHERE phone='{$phone}' OR phone2='{$phone}' OR phone3='{$phone}'
            ");
            if ($teacher->num_rows) {
	            $data = $teacher->fetch_object();
				$return = [
                    'name'	=> static::nameOrEmpty(getName($data->first_name, $data->last_name, $data->middle_name)),
                    'type'	=> 'teacher',
                    'id'	=> $data->id,
                ];
            } else {
                # Ищем представителя с таким же номером телефона
                $represetative = dbConnection()->query("
				SELECT s.id, r.first_name, r.last_name, r.middle_name FROM ".Representative::$mysql_table." r
				LEFT JOIN ".Student::$mysql_table." s on r.id = s.id_representative
				WHERE r.phone='".$phone."' OR r.phone2='".$phone."' OR r.phone3='".$phone."'"
                );
                // Если заявка с таким номером телефона уже есть, подхватываем ученика оттуда
                if ($represetative->num_rows) {
                    $data = $represetative->fetch_object();
                    $return = [
                        'name'	=> static::nameOrEmpty(getName($data->first_name, $data->last_name, $data->middle_name)),
                        'type'	=> 'representative',
                        'id'	=> $data->id,
                    ];
                } else {
                    # Ищем ученика с таким же номером телефона
                    $student = dbConnection()->query("
                        select id, first_name, last_name, middle_name from students
                        WHERE phone='{$phone}' OR phone2='{$phone}' OR phone3='{$phone}'
                    ");
                    // Если заявка с таким номером телефона уже есть, подхватываем ученика оттуда
                    if ($student->num_rows) {
                        $data = $student->fetch_object();
                        $return = [
                            'name'	=> static::nameOrEmpty(getName($data->first_name, $data->last_name, $data->middle_name)),
                            'type'	=> 'student',
                            'id'	=> $data->id
                        ];
                    } else {
                        # Ищем заявку с таким же номером телефона
                        $request = dbConnection()->query("
                            select id, name from requests
                            WHERE phone='{$phone}' OR phone2='{$phone}' OR phone3='{$phone}'
                        ");
                        // Если заявка с таким номером телефона уже есть, подхватываем ученика оттуда
                        if ($request->num_rows) {
                            $data = $request->fetch_object();
                            $return = [
                                'name'	=> static::nameOrEmpty($data->name),
                                'type'	=> 'request',
                                'id'	=> $data->id
                            ];
                        }
                    }
                }
            }

            // возвращается, если номера нет в базе
			if (! isset($return)) {
				$return = ['type' => false];
			}
			
			memcached()->set("Caller[$phone]", $return, time() + 15);
			returnJsonAng($return);
		}
		
		public function actionGetLastCallData()
		{
			extract($_POST);
			
			$return = memcached()->get("LastCallData[$phone]");
			if (! $return) {
				$return = $this->getLastCallData($phone);
				memcached()->set("LastCallData[$phone]", $return, time() + 15);
			}
			
			returnJsonAng($return);
		}
		
		public function actionGetAnsweredUser($phone) {
            return MangoNew::getAnswered($phone);
        }

        private function getLastCallData($phone) {
			$stats = MangoNew::getStats($phone);
			
			foreach(array_reverse($stats) as $s) {
				// если это входящий звонок и разговора не было, не анализировать
				if ($s['to_extension'] && $s['answer'] == 0) {
					continue;
				}
				if ($s['from_extension']) {
					$s['user'] = User::findById($s['from_extension'], true);
					return $s;
				} 
				if ($s['to_extension']) {
					$s['user'] = User::findById($s['to_extension'], true);
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
            memcached()->set("Answered[$phone]", $user_id, time() + 15);
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
