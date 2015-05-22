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
				foreach ($ContractSubjects as &$ContractSubject) {
					$ContractSubject = $ContractSubject->dbData();
					$ContractSubject['name'] = Subjects::$all[$ContractSubject['id_subject']];
				}
				// Возвращаем с названиями предметов
				return $ContractSubjects;
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
				self::add($subject_data + ["id_contract" => $id_contract]);
			}
		}
				
		/*====================================== ФУНКЦИИ КЛАССА ======================================*/

		
	}