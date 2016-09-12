<?php
	class Call
	{
		const TEST_NUMBER		= '74955653170';
		const EGEREP_NUMBER 	= '74956461080';
		const EGECENTR_NUMBER 	= '74956468592';
		
		/*
			Алгоритм: сначала получаем все сегодняшние пропущенные, затем исключаем по условиям
				* WHERE mango.start > missed.start									дата звонка > даты пропущенного звонка
				* mango.to_number = missed.from_number								мы перезвонили (неважно ответил ли клиент)
				* mango.from_number = missed.from_number and mango.answer != 0		клиент сам перезвонил и мы ответили
		*/
		const MISSED_CALLS_SQL = "
				FROM (
					SELECT from_number, start
					FROM `mango`
					WHERE DATE(NOW()) = DATE(FROM_UNIXTIME(start)) and from_extension=0
					GROUP BY entry_id
					HAVING sum(answer) = 0
				) missed 
				WHERE NOT EXISTS (SELECT 1 FROM mango WHERE mango.start > missed.start and 
					(mango.to_number = missed.from_number or (mango.from_number = missed.from_number and mango.answer != 0))
				)
				GROUP BY from_number 
				ORDER BY start DESC";
		
		/**
		 * Выбираем пропущенные за сегодня звонки, на которые потом не перезвонили
		 */
		public static function missed($get_caller = true)
		{
			$result = dbEgerep()->query("SELECT *" . self::MISSED_CALLS_SQL);
			$missed = [];
			while ($row = $result->fetch_object()) {
				if ($get_caller) {
					$row->caller = self::getCaller($row->from_number);
				}
				$row->phone_formatted = formatNumber($row->from_number);
				$missed[] = $row;
			}
			return $missed;
		}
		
		/*
		 * Кол-во пропущенных сегдоня звонков, на которые не ответили
		 */
		public static function missedCount()
		{
			return dbEgerep()->query("SELECT 1" . self::MISSED_CALLS_SQL)->num_rows;
		}
		
		
		/*
		 * Номер ЕГЭ-Центра
		 */
		public static function isEgecentr($number) {
            return $number == self::EGECENTR_NUMBER;
        }
        
        /*
		 * Номер ЕГЭ-Репетитора
		 */
		public static function isEgerep($number) {
            return $number == self::EGEREP_NUMBER;
        }
        
        /*
		 * Данные по последнему разговору
		 */
		public static function lastData($phone)
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
		public static function getCaller($phone)
		{
            // Ищем учителя с таким номером
            $teacher = dbEgerep()->query("
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
			
			return $return;
		}
		
		/**
		 * Уведомить всех пользователей об ответе на звонок
		 */
		public static function notifyAnswered($id_user, $call_id)
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
        
        /**
	     * Уведомить pusher о входящем звонке
	     * $number – входящий номер нужен для определения crm
	     */
        public static function notifyIncoming($id_user, $data, $number)
        {
	        Socket::trigger('user_' . $id_user, 'incoming', $data, static::_getCrmByNumber($number));
        }
		
		private static function _getCrmByNumber($number)
		{
			if (static::isEgerep($number)) {
				return 'egerep';
			} else {
				return 'egecrm';
			}
		}
		
		private static function _nameOrEmpty($name)
        {
	        if (empty(trim($name))) {
		        return 'имя неизвестно';
	        } else {
		        return $name;
	        }
        }
	}