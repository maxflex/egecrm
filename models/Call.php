<?php
	class Call
	{
		const TEST_NUMBERS		= ['74955653170'];
		const EGEREP_NUMBERS 	= ['74956461080'];
		const EGECENTR_NUMBERS 	= ['74956468592', '74954886885', '74954886882'];

		/*
			Алгоритм: сначала получаем все сегодняшние пропущенные, затем исключаем по условиям
				* WHERE mango.start > missed.start									дата звонка > даты пропущенного звонка
				* mango.to_number = missed.from_number								мы перезвонили (неважно ответил ли клиент)
				* mango.from_number = missed.from_number and mango.answer != 0		клиент сам перезвонил и мы ответили
				*
				*
				* upd: анализируем за последние 24 часа
		*/

        private static function getMissedCallsSql()
        {
            $excluded_sql = " and 1 ";
            if (($excluded_entries = memcached()->get("excluded_missed")) && is_array($excluded_entries) && !empty($excluded_entries)) {
                // in (string, string) => in ('string', 'string')
                $excluded_entries = array_map(function($item){return "'".$item."'"; }, $excluded_entries);
                $excluded_sql = " and entry_id not in (".implode(",", $excluded_entries).") ";
            }

            return "
                    FROM (
                        SELECT entry_id, from_number, start
                        FROM `mango`
                        WHERE `start` > UNIX_TIMESTAMP(now() - interval 24 hour) and from_extension=0 and line_number IN (" . implode(',', self::EGECENTR_NUMBERS) . ") {$excluded_sql}
                        GROUP BY entry_id
                        HAVING sum(answer) = 0
                    ) missed
                    WHERE NOT EXISTS (SELECT 1 FROM mango WHERE mango.start > missed.start and
                        (mango.to_number = missed.from_number or (mango.from_number = missed.from_number and mango.answer != 0))
                    )
                    GROUP BY from_number
                    ORDER BY start DESC";
        }

        /**
		 * Выбираем пропущенные за сегодня звонки, на которые потом не перезвонили
		 */
		public static function missed($get_caller = true, $line = self::EGECENTR_NUMBERS)
        {
            $result = dbEgerep()->query("SELECT *" . self::getMissedCallsSql());
			$missed = [];
			while ($row = $result->fetch_object()) {
				if ($get_caller) {
					$row->caller = self::getCaller($row->from_number, $line);
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
			return dbEgerep()->query("SELECT 1" . self::getMissedCallsSql())->num_rows;
		}

		public static function excludeFromMissed($entry_id)
        {
            if ($excluded = memcached()->get('excluded_missed')) {
                $excluded[] = $entry_id;
            } else {
                $excluded = [$entry_id];
            }
            memcached()->set('excluded_missed', $excluded, tillNextDay());
        }

		/*
		 * Номер ЕГЭ-Центра
		 */
		public static function isEgecentr($number) {
            return in_array($number, self::EGECENTR_NUMBERS);
        }

        /*
		 * Номер ЕГЭ-Репетитора
		 */
		public static function isEgerep($number) {
            return in_array($number, self::EGEREP_NUMBERS);
        }

        /*
		 * Данные по последнему разговору
		 */
		public static function lastData($phone)
		{
			$result = dbEgerep()->query("
				SELECT * FROM mango
				WHERE (from_number='{$phone}' AND answer!=0) OR to_number='{$phone}'
				ORDER BY start DESC
				LIMIT 1
			");

			if ($result->num_rows) {
				$return = $result->fetch_object();
				$id_user = $return->from_extension ?: $return->to_extension;
				$return->user_login = Admin::getLogin($id_user);
				$return->user_busy	= User::isCallBusy($id_user);
				return $return;
			} else {
				return false;
			}
		}


		/*
		 * Определить звонящего
		 */
		public static function getCaller($phone, $to_number)
		{
			if (static::isEgecentr($to_number)) {
				$return = static::determineEgecrm($phone);
            }

			if (static::isEgerep($to_number)) {
				$return = static::determineEgerep($phone);
			}

            // возвращается, если номера нет в базе
			if (! is_array($return)) {
				$return = ['type' => false];
			}

			return $return;
		}


		/**
		 * Определить номер для ЕГЭ-Центра
		 */
		public static function determineEgecrm($phone)
		{
			# Ищем ученика с таким же номером телефона
            $student = dbConnection()->query("
                select id, first_name, last_name, middle_name from students
                WHERE phone='{$phone}' OR phone2='{$phone}' OR phone3='{$phone}'
            ");
            // Если заявка с таким номером телефона уже есть, подхватываем ученика оттуда
            if ($student->num_rows) {
                $data = $student->fetch_object();
				return [
                    'name'	=> static::_nameOrEmpty(getName($data->last_name, $data->first_name, $data->middle_name)),
                    'type'	=> 'student',
                    'id'	=> $data->id
                ];
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
				return [
                    'name'	=> static::_nameOrEmpty(getName($data->last_name, $data->first_name, $data->middle_name)),
                    'type'	=> 'representative',
                    'id'	=> $data->id,
                ];
            }

			# Ищем учителя с таким номером
            $teacher = dbEgerep()->query("
            	select id, first_name, last_name, middle_name from tutors
            	WHERE phone='{$phone}' OR phone2='{$phone}' OR phone3='{$phone}' OR phone4='{$phone}'
            ");
            if ($teacher->num_rows) {
	            $data = $teacher->fetch_object();
				return [
                    'name'	=> static::_nameOrEmpty(getName($data->last_name, $data->first_name, $data->middle_name)),
                    'type'	=> 'teacher',
                    'id'	=> $data->id,
                ];
            }


            # Ищем заявку с таким же номером телефона
            $request = dbConnection()->query("
                select id, name from requests
                WHERE phone='{$phone}' OR phone2='{$phone}' OR phone3='{$phone}'
            ");
            // Если заявка с таким номером телефона уже есть, подхватываем ученика оттуда
            if ($request->num_rows) {
                $data = $request->fetch_object();
				return [
                    'name'	=> static::_nameOrEmpty($data->name),
                    'type'	=> 'request',
                    'id'	=> $data->id
                ];
            }
		}

		/**
		 * Определить номер для ЕГЭ-Репетитора
		 */
		private static function determineEgerep($phone)
		{
			# ищем клиента в ЕГЭ-РЕПЕТИТОРЕ с таким номером
            $client = dbEgerep()->query("
                select id, phone, phone2, phone3, phone4, phone_comment, phone2_comment, phone3_comment, phone4_comment from clients
                WHERE phone='{$phone}' OR phone2='{$phone}' OR phone3='{$phone}' OR phone4='{$phone}'
            ");
            # если заявка с таким номером телефона уже есть, подхватываем ученика оттуда
            if ($client->num_rows) {
                $data = $client->fetch_object();
                // берем имя из комментария к телефону
                foreach(['phone', 'phone2', 'phone3', 'phone4'] as $phone_field) {
                    if ($data->{$phone_field} == $phone) {
                        $name = $data->{$phone_field . '_comment'};
                    }
                }
				return [
                    'name'	=> static::_nameOrEmpty(getName($name)),
                    'type'	=> 'client',
                    'id'	=> $data->id
                ];
            }

			# ищем учителя с таким номером
            $teacher = dbEgerep()->query("
            	select id, first_name, last_name, middle_name from tutors
            	WHERE phone='{$phone}' OR phone2='{$phone}' OR phone3='{$phone}'  OR phone4='{$phone}'
            ");
            if ($teacher->num_rows) {
	            $data = $teacher->fetch_object();
				return [
                    'name'	=> static::_nameOrEmpty(getName($data->last_name, $data->first_name, $data->middle_name)),
                    'type'	=> 'tutor',
                    'id'	=> $data->id,
                ];
            }
		}

		/**
		 * Уведомить всех пользователей об ответе на звонок
		 */
		public static function notifyAnswered($id_user, $call_id, $number)
        {
            // @rights-refactored
	        $user_ids = User::getIds([
		       'condition' => 'NOT FIND_IN_SET(' . Shared\Rights::EC_BANNED . ', rights) AND FIND_IN_SET(' . Shared\Rights::PHONE_NOTIFICATIONS . ', rights)'
	        ]);

	        foreach($user_ids as $id) {
		    	Socket::trigger('user_' . $id, 'answered', [
			    	'answered_user' => Admin::getLogin($id_user),
			    	'call_id'		=> $call_id,
		    	], static::_getCrmByNumber($number));
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
