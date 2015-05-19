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
			if ($ContractSubjects) {
				foreach ($ContractSubjects as &$ContractSubject) {
					$ContractSubject = $ContractSubject->dbData();
					$ContractSubject['name'] = Subjects::$all[$ContractSubject['id_subject']];
				//	$ContractSubject->name = Subjects::$all[$ContractSubject->id_subject];
				}
				// Возвращаем с названиями предметов
				return $ContractSubjects;
			} else {
				return false;
			}
		}
		
		/**
		 * Добавить предметы из angular-json-данных.
		 * 
		 */
		public static function addData($json_array, $id_contract) 
		{
			// Сначала декодируем данные
			$subjects_data = json_decode($json_array, true);
			
			// Если никаких данных нет
			if ($subjects_data === false) {
				return false;
			}
			
			// Удаляем предыдущие данные 
			// @todo: проверять есть ли данные. если их не существует – добавлять по отдельности
			self::deleteAll([
				"condition"	=> "id_contract=$id_contract",
			]);
			
			// Сохраняем данные
			foreach ($subjects_data as $subject_data) {
				unset($subject_data["id"]); //  удаляем ID, потому что заново передобавляем 
				
				$SubjectData = new self($subject_data);
				$SubjectData->id_contract = $id_contract;
				$SubjectData->save();
			}
		}
				
		/*====================================== ФУНКЦИИ КЛАССА ======================================*/

		
	}