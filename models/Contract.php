<?php
	class Contract extends Model
	{
	
		/*====================================== ПЕРЕМЕННЫЕ И КОНСТАНТЫ ======================================*/

		public static $mysql_table	= "contracts";
		
		// путь хранения электронных версий договоров
		const CONTRACTS_DIR = "files/contracts/";
		
		// Временная директория электронных версий договоров
		const CONTRACTS_TMP_DIR = "files/contracts/tmp/";
		
		/*====================================== СИСТЕМНЫЕ ФУНКЦИИ ======================================*/
		
		public function __construct($array) 
		{
			parent::__construct($array);
			
			// Добавляем предметы в контракт
			$this->subjects = ContractSubject::getContractSubjects($this->id);
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
					// Иначе обновляем старый договор
					self::updateById($id, $Contract);
					// Добавляем предметы договора
					ContractSubject::addData($Contract["subjects"], $id);	
				}
			}
		}
				
		/*====================================== ФУНКЦИИ КЛАССА ======================================*/
		
		public function beforeSave()
		{
			// Если сумма договора равна нулю, то ставим, будто не указана
			// иначе если в новый договор не подставить сумму, автоматом ставится ноль
			if (!$this->sum) {
				$this->sum = NULL;
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
		}
		
		public function afterSave()
		{
			// Загружаем файл после сохранения, т.к. нужен ID  договора
			$this->uploadFile();
		}
		
		
		/**
		 * Загружаем электронную версию договора.
		 * 
		 */
		public function uploadFile()
		{
			// Путь к файлу
			$file_path = self::CONTRACTS_TMP_DIR . $this->file;
			
			// Если временный файл есть, переносим его
			if (file_exists($file_path)) {
				$handle = new upload($file_path);
				if ($handle->uploaded) {
					// Разрешаем перезапись файла
					$handle->file_overwrite = true;
					
					// Переименовываем файл в ID контракта
					$handle->file_new_name_body = $this->id;
					
					// Грузим
					$handle->process(self::CONTRACTS_DIR);
					
					if ($handle->processed) {
						// Сохраняем имя файла
						$this->file = $handle->file_dst_name;
						$this->save("file");
						return true;
					} else {
						return false;
					}
				} else {
					return false;
				}	
			}
		}
	}