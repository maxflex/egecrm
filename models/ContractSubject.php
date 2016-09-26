<?php
	class ContractSubject  extends Model
	{
	
		/*====================================== ПЕРЕМЕННЫЕ И КОНСТАНТЫ ======================================*/

		public static $mysql_table	= "contract_subjects";
		
		/*====================================== СИСТЕМНЫЕ ФУНКЦИИ ======================================*/
		
		
		
		/*====================================== СТАТИЧЕСКИЕ ФУНКЦИИ ======================================*/
		
		
		/**
		 * Получить предметы договора.
		 * 
		 */
		public static function getContractSubjects($id_contract)
		{
			// Находим предметы договора
			$ContractSubjects = self::findAll([
				"condition" => "id_contract=$id_contract",
			]);
			
			// Если предметы нашлись, сопостовляем ID предметов названиям
			// и сразу же убираем ненужные данные, только dbData
			if ($ContractSubjects) {
				foreach ($ContractSubjects as $id => &$ContractSubject) {
					$ContractSubject = $ContractSubject->dbData();
					$ContractSubject['name']	= Subjects::$all[$ContractSubject['id_subject']];
					$ContractSubject['short']	= Subjects::$short[$ContractSubject['id_subject']];
					
					// Вместе 0, 1 в массиве ключами идут ID предметов. Нужно обязательно
					$return[$ContractSubject["id_subject"]] = $ContractSubject;
				}
				// Возвращаем с названиями предметов
				return $return;
			} else {
				return false;
			}
		}
		
		/**
		 * Добавить предметы в договор.
		 * 
		 */
		public static function addData($subjects_data, $id_contract) 
		{
			$subjects_data = array_filter($subjects_data);

			// Если никаких данных нет
			if (!count($subjects_data)) {
				return false;
			}
			
			// Удаляем предыдущие данные 
			// @todo: проверять есть ли данные. если их не существует – добавлять по отдельности
			self::deleteAll([
				"condition"	=> "id_contract=$id_contract",
			]);
			
			// Сохраняем данные
			foreach ($subjects_data as $subject_data) {
				// обнуляем ID и ID контракта, это обязательно,
				// иначе из функции Contract::versionControl() не смогут создаться копии предметов договора
				// потому что у них уже установлены ID. 
				unset($subject_data["id"]);
				unset($subject_data["id_contract"]);
				self::add($subject_data + ["id_contract" => $id_contract]);
			}
		}
				
		/*====================================== ФУНКЦИИ КЛАССА ======================================*/

		
	}