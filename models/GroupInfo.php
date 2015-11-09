<?php
	class GroupTeacherLike extends Model
	{
		/*====================================== ПЕРЕМЕННЫЕ И КОНСТАНТЫ ======================================*/

		public static $mysql_table	= "group_teacher_likes";
		
		
		public static function addData($data)
		{
			$StudentTeacherLike = self::find([
				"condition" => "id_teacher={$data['id_teacher']} AND id_student={$data['id_student']}"
			]);
			
			# если мнение о преподе уже существует
			if ($StudentTeacherLike) {
				# если мнение изменилось
				if ($StudentTeacherLike->id_status != $data['id_status']) {
					$StudentTeacherLike->id_status = $data['id_status'];
					$StudentTeacherLike->save("id_status");
				}	
			} else {
				self::add($data);
			}
		}
		
		public static function getStatus($id_student, $id_teacher)
		{
			$StudentTeacherLike = self::find([
				"condition" => "id_teacher={$id_teacher} AND id_student={$id_student}"
			]);
			
			if ($StudentTeacherLike) {
				return $StudentTeacherLike->id_status;
			} else {
				return 0;
			}
		}
	}
	
	class GroupSms extends Model
	{
		/*====================================== ПЕРЕМЕННЫЕ И КОНСТАНТЫ ======================================*/

		public static $mysql_table	= "group_sms";
		
		
		public static function notify($data)
		{
			$data['notified'] = 1;
			self::add($data);
		}
		
		public static function getStatus($id_student, $id_branch, $id_subject, $first_schedule, $cabinet)
		{
			// preType([$id_student, $id_branch, $id_subject, $first_schedule, $cabinet]);
			return self::count([
				"condition" => "id_student={$id_student} 
								 AND id_branch={$id_branch} 
								 AND id_subject={$id_subject}
								 AND first_schedule='{$first_schedule}' 
								 AND cabinet={$cabinet}"
			]);
		}
	}
	
	class GroupAgreement extends Model 
	{
		/*====================================== ПЕРЕМЕННЫЕ И КОНСТАНТЫ ======================================*/
		
		# Список предметов
		const NBT 		= 1;
		const AWAITING	= 2;
		const AGREED	= 3;
		
		# Все предметы
		static $all = [
			self::NBT 		=> "нбт",
			self::AWAITING	=> "ожидает",
			self::AGREED	=> "с расписанием согласен",
		];

		public static $mysql_table	= "group_agreement";
				
		
		public static function addData($data)
		{
			# clean up empty date_and_time
			$data['day_and_time'] = self::_cleanDateAndTime($data['day_and_time']);
			
			$GroupAgreement = self::find([
				"condition" => "type_entity='" . $data['type_entity'] ."' AND id_entity={$data['id_entity']} 
								 AND id_group={$data['id_group']} AND day_and_time='" . $data['day_and_time'] . "'"
			]);
			
			# если мнение о преподе уже существует
			if ($GroupAgreement) {
				# если мнение изменилось
				if ($GroupAgreement->id_status != $data['id_status']) {
					$GroupAgreement->id_status = $data['id_status'];
					$GroupAgreement->save("id_status");
				}	
			} else {
				self::add($data);
			}
		}
		
		public static function getStatus($data)
		{
			# clean up empty date_and_time
			$data['day_and_time'] = self::_cleanDateAndTime($data['day_and_time']);

			$GroupAgreement = self::find([
				"condition" => "type_entity='" . $data['type_entity'] ."' AND id_entity={$data['id_entity']} 
								 AND id_group={$data['id_group']} AND day_and_time='" . $data['day_and_time'] . "'"
			]);
			
			if ($GroupAgreement) {
				return $GroupAgreement->id_status;
			} else {
				return 0;
			}
		}
		
		private static function _cleanDateAndTime($date_and_time)
		{
			$date_and_time = array_filter($date_and_time);
			foreach ($date_and_time as &$dt) {
				$dt = array_filter($dt);
			}
			return json_encode($date_and_time);
		}
	}