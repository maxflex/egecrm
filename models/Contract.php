<?php
	class Contract extends Model
	{
	
		/*====================================== ПЕРЕМЕННЫЕ И КОНСТАНТЫ ======================================*/

		public static $mysql_table	= "contracts";
		
		// какие поля не сравнивать при сравнении объектов
		// в функции self::changed()
		private static $dont_compare = ["id", "id_student", "id_contract", "id_user", "date_changed", "cancelled_date", "cancelled_by", "files"];	
		
		public $_serialized = ["files"];
		
		// путь хранения электронных версий договоров
		const CONTRACTS_DIR = "files/contracts/";
		
		// Временная директория электронных версий договоров
		//const CONTRACTS_TMP_DIR = "files/contracts/tmp/";
		
		/*====================================== СИСТЕМНЫЕ ФУНКЦИИ ======================================*/
		
		public function __construct($array) 
		{
			parent::__construct($array);
			
			// Добавляем предметы в контракт
			$this->subjects = ContractSubject::getContractSubjects($this->id);
			
			// история изменений
			if ($this->id_student) {
				$this->History = self::findAll([
					"condition" => "id_contract=" . $this->id
				]);
			}
			
			// логин пользователя
			if ($this->id_user) {
				$this->user_login = User::findById($this->id_user)->login;
			}	
		}
		
		
		/*====================================== СТАТИЧЕСКИЕ ФУНКЦИИ ======================================*/
		
		
		/**
		 * Добавить новые контракты и обновить старые.
		 *
		 * договоры с (id < 0) – это новые договоры. у новых договоров отрицательный ID, их нужно добавить
		 * договоры с положительным ID – уже существующие, их нужно обновить
		 */
		public static function addAndUpdate($Contracts, $id_student)
		{
			foreach ($Contracts as $id => $Contract) {
				// Добавляем ID Ученика в данные по контракту
				$Contract["id_student"] = $id_student;
				
				// Если добавляем новый договор
				if ($id < 0) {
					// Создаем договор
					$NewContract = self::add($Contract);
					// Добавляем предметы договора
					ContractSubject::addData($Contract["subjects"], $NewContract->id);
				} else {
					// Контроль версий
					$changed = self::versionControl($id, $Contract);
					
					// если были изменения, обновляем
					if ($changed) {
						// Обновляем старый договор
						$OriginalContract = self::updateById($id, $Contract);
						
						// Загружаем туда файлы
						$OriginalContract->uploadFile();
						
						// Добавляем предметы договора
						ContractSubject::addData($Contract["subjects"], $id);	
					}
				}
			}
		}
		
		
		/**
		 * Договор был изменен?
		 * 
		 * @return boolean true – изменен, false – не изменен
		 */
		public static function changed($id, $Contract)
		{	
			# Находим оригинальный доГАВАр
			$OriginalContract = Contract::findById($id);
			
			//preType([$Contract["subjects"], $OriginalContract->subjects]);
			
			# ФАЙЛЫ
			
			// проверяем изменилось ли количество файлов
			$original_contract_files_count = $OriginalContract->files ? count($OriginalContract->files) : 0;
			
			if ($original_contract_files_count != count((array)$Contract["files"])) {
				return true;
			}
			
			// проверяем изменились ли загруженные файлы
			foreach ($OriginalContract->files as $id => $file) {
				if (serialize($file) != $Contract["files"][$id]) {
					h1("FILES NOT EQUAL");
					preType(erialize($file));
					preType($Contract["files"][$id]);
					return true;
				} else {
					h1("FILES ARE EQUAL!");
					preType(erialize($file));
					preType($Contract["files"][$id]);
				}
			}
			
/*
			if (serialize(arrayLevelUp($OriginalContract->files)) != serialize($Contract["files"])) {
				h1("FILES NOT EQUAL");
				preType($OriginalContract->files);
				preType($Contract["files"]);
				return true;
			}
*/
			# Проверяем изменились ли предметы договора
			
			// проверяем изменилось ли количество предметов в договорах
			if (count($Contract["subjects"]) != $OriginalContract->getSubjectCount()) {
				var_dump($OriginalContract->subjects);
//				echo "SUBJECTS COUNT CHANGED: ".count($Contract["subjects"])." - ".($OriginalContract->getSubjectCount())."<br>";
//				exit();
				return true;
			}
			
			// проверяем изменились ли предметы договора
			foreach ($Contract["subjects"] as $id => $ContractSubject) {
//				preType([$ContractSubject, $OriginalContract->subjects[$id]]);
				foreach ($ContractSubject as $key => $value) {
//					preType($OriginalContract->subjects[$id]);
					if ($value != $OriginalContract->subjects[$id][$key]) {
//						echo "<b>$key</b>:" . $OriginalContract->subjects[$id][$key] . " => ". $value . "<br>";
						return true;
					}
				}
			}
			
			// Проверяем изменились ли основные поля договора
			foreach ($OriginalContract->mysql_vars as $field) {
				// если поле не надо сравнивать
				if (in_array($field, static::$dont_compare)) {
					continue;
				}
				
//				echo "<b>$field</b>:" . $OriginalContract->{$field}. " => ". $Contract[$field]. "<br>";
				
				if (array_key_exists($field, $Contract)) {
					if ($Contract[$field] != $OriginalContract->{$field}) {
//						echo "'$field' has been changed! ";
						return true;
					}
				}
			}
			
			return false;
		}
		
		
		/**
		 * Создать версию договора (храним все изменения договора)
		 * 
		 */
		public static function versionControl($id, $Contract)
		{
			// если договор был изменен, то создать версию изменения
			if (self::changed($id, $Contract)) {
				// находим оригинальный контракт
				$OriginalContract = Contract::findById($id);
				
				// создаем копию оригинального контракта для истории
				$ContractCopy = clone $OriginalContract;
				
				unset($ContractCopy->id);
				$ContractCopy->isNewRecord = true;
				$ContractCopy->id_student = null;
				$ContractCopy->id_contract = $id;
				
				$ContractCopy->save();
				
				// Клонируем файлы. Обязательно после сохранения, когда уже есть ID
				self::cloneFiles($OriginalContract, $ContractCopy);
				
				// Добавляем предметы нового договора
				ContractSubject::addData($OriginalContract->subjects, $ContractCopy->id);
				return true;	
			} else {
				return false;
			}	
		}
		
		/**
		 * Скопировать файлы.
		 * 
		 * Скопировать из $OriginalContract в $NewContract
		 */
		public static function cloneFiles($OriginalContract, &$NewContract)
		{
			h1("HERE CLONE FILES");
			preType($OriginalContract);
			preType($NewContract);
			foreach ($OriginalContract->files as $id => $file) {
				// Имя файла
				$file_name = $file["name"];
				
				// Путь к файлу
				$original_file_path = self::CONTRACTS_DIR . $file_name;
				
				// Имя нового файла 
				// Имя = [ID Договора]_[Порядковый номер электронной версии договора].[Расширение файла]
				$new_file_name 		= $NewContract->id . "_" . ($id + 1) . "." . pathinfo($file_name, PATHINFO_EXTENSION);
				
				// Путь к новому файлу
				$new_file_path		= self::CONTRACTS_DIR . $new_file_name;
				
				// Генерируем файлы для нового догавара
				$return[] = ["name" => $new_file_name];
				
				// Копируем файл
				copy($original_file_path, $new_file_path);
			}
			
			preType($return);
			
			// если есть что сохранять
			if (count($return)) {
				$NewContract->files = $return;
				$NewContract->save("files");
			}
		}
				
		/*====================================== ФУНКЦИИ КЛАССА ======================================*/
		
		public function beforeSave()
		{
/*
			h1("SAVING...");
			echo "<pre>";
			debug_print_backtrace();
			echo "</pre>";
			preType($this);
*/
			// Если сумма договора равна нулю, то ставим, будто не указана
			// иначе если в новый договор не подставить сумму, автоматом ставится ноль
			if (!$this->sum) {
				$this->sum = NULL;
			}
			
			if (!$this->id_contract) {
				$this->id_contract = NULL;
			}
			
			// Если расторгаем договор
			// (если расторгнут)
			if ($this->cancelled) {
				// если изменили статус (а был НЕ расторгнут)
				if (!self::findById($this->id)->cancelled) {
					// сохраняем данные пользователя, который сделал расторжение договора
					$this->cancelled_by 	= User::fromSession()->id;
					$this->cancelled_date	= now();
				}
			}
			
			// дата изменения
			$this->date_changed = now();
			// договор всегда создается новый, поэтому нет условия if ($this->isNewRecord) { ... }
			$this->id_user		= User::fromSession()->id;
		}
		
		public function afterFirstSave()
		{
			parent::afterFirstSave();
			
			// Загружаем файл после сохранения, т.к. нужен ID  договора
			$this->uploadFile();
		}
		
		
		
		/**
		 * Получить количество предметов у договора.
		 *
		 * Потому что при отсутствии предметов возвращает 1: 
		 * http://stackoverflow.com/questions/3776882/count-of-false-gives-1-and-if-of-an-empty-array-gives-false-why
		 */
		public function getSubjectCount()
		{
			if (is_array($this->subjects)) {
			    return count($this->subjects);
			} else {
				return 0;
			}
		}
		
		/**
		 * Загружаем электронную версию договора.
		 * 
		 */
		public function uploadFile()
		{
			foreach ($this->files as $id => $file) {
				// декодируйем ДЖЕЙ(тире)СОН
				$file = json_decode($file, true);
				
				// если файл уже загружен
				if (strpos($file["name"], "tmp") === false) {
					// Регенерируем имя
					//$FileInfo = new SplFileInfo($file_name);
					
					// имя [ID Договора]_[Порядковый номер электронной версии договора].[Расширение файла]
					$file["name"] = $this->id . "_" . ($id + 1) . "." . pathinfo($file["name"], PATHINFO_EXTENSION);
					$return[] = $file;
					continue;
				}
				
				// Путь к файлу
				$file_path = self::CONTRACTS_DIR . $file["name"];
				
				// Если временный файл есть, переносим его
				if (file_exists($file_path)) {
					$handle = new upload($file_path);
					if ($handle->uploaded) {
						// Разрешаем перезапись файла
						$handle->file_overwrite = true;
						
						// Переименовываем файл в ID контракта и ID файла
						$handle->file_new_name_body = $this->id . "_" . ($id + 1);
						
						// Грузим
						$handle->process(self::CONTRACTS_DIR);
						
						if ($handle->processed) {
							// Сохраняем имя файла
							$file["name"] = $handle->file_dst_name;
							$return[] = $file;
						} else {
							return false;
						}
					} else {
						return false;
					}	
				}	
			}
			$this->files = $return;
			
			$this->save("files");
			
			return true;
		}
	}