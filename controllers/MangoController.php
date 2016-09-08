<?php
	// Контроллер
	class MangoController extends Controller
	{
		const TEST_NUMBER		= '74955653170';
		const APERS_NUMBER 		= '74956461080';
		const EGECENTR_NUMBER 	= '74956468592';


		// Папка вьюх
		protected $_viewsFolder	= "mango";
		
		/**
		 * Поступил входящий звонок
		 */
		public function actionEventCall()
		{
			$data = json_decode($_POST['json']);
			
			// @todo: так же добавить User::setCallBusy на исходящий звонок
			// если исходящий звонок
			if ($data->from->extension) {
				switch ($data->call_state) {
					case Mango::STATE_APPEARED:
						User::setCallBusy($data->from->extension);
						break;
					case Mango::STATE_DISCONNECTED:
						User::setCallFree($data->from->extension);
						break;
				}
			}
			
			
			if (static::isEgecentrNumber($data->to->line_number)) {
				// @рассмотреть добавление определения в appeared
				switch ($data->call_state) {
					case Mango::STATE_APPEARED:
						// определить последнего говорившего
						$data->caller			= static::_getCaller($data->from->number);
						$data->last_call_data 	= static::_getLastCallData($data->from->number);
						break;
					case Mango::STATE_CONNECTED:
						User::setCallBusy($data->to->extension);
						static::_notifyAnswered($data->to->extension, $data->call_id);
						break;
					case Mango::STATE_DISCONNECTED:
						User::setCallFree($data->to->extension);
						break;
				}
				// передаем данные
				Socket::trigger('user_' . $data->to->extension, 'incoming', $data);
			}
		}
		
		/*
		 * Положили трубку (не используется)
		 */
		public function actionHangup()
		{
			extract($_POST);
			Mango::hangup($call_id);
		}
		
		
		# ============================ #
		# ==== CONTROLLER HELPERS ==== #
		# ============================ #
		
		/*
		 * Данные по последнему разговору
		 */
		private static function _getLastCallData($phone)
		{
			$result = dbEgerep()->query("
				SELECT * FROM mango 
				WHERE (from_number='{$phone}' OR to_number='{$phone}') AND answer!=0
				ORDER BY id DESC
				LIMIT 1
			");
			
			if ($result->num_rows) {
				$return = $result->fetch_object();
				$id_user = $return->from_extension ?: $return->to_extension;
				$return->user_login = User::getLogin($id_user);
				$return->user_busy	= User::isCallBusy($id_user);
				return $return;
			} else {
				return false;
			}
		}
		
		
		/*
		 * Определить звонящего
		 */
		private static function _getCaller($phone)
		{
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
                    'name'	=> static::_nameOrEmpty(getName($data->first_name, $data->last_name, $data->middle_name)),
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
                        'name'	=> static::_nameOrEmpty(getName($data->first_name, $data->last_name, $data->middle_name)),
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
                            'name'	=> static::_nameOrEmpty(getName($data->first_name, $data->last_name, $data->middle_name)),
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
                                'name'	=> static::_nameOrEmpty($data->name),
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
			
			// memcached()->set("Caller[$phone]", $return, time() + 15);
			return $return;
		}
		
		private static function _nameOrEmpty($name)
        {
	        if (empty(trim($name))) {
		        return 'имя неизвестно';
	        } else {
		        return $name;
	        }
        }
        
        private static function _notifyAnswered($id_user, $call_id)
        {
	        $user_ids = User::getIds([
		       'condition' => 'banned=0 AND show_phone_calls=1'
	        ]);
	        
	        foreach($user_ids as $id) {
		    	Socket::trigger('user_' . $id, 'answered', [
			    	'answered_user' => User::getLogin($id_user),
			    	'call_id'		=> $call_id,
		    	]);
	        }
        }
		
		# =============================== #
		# ==== 	determine numbers	 ==== #
		# =============================== #
		
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
