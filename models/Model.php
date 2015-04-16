<?php

	// Скелет модели
	class Model
	{
		// Таблица модели
		public static $mysql_table = NULL;
		
		// Переменные из таблицы (которые надо сохранять и тд)
		public $mysql_vars = array();
		
		// Переменные из таблицы MySQL, которые сохранять не надо
		protected $_exclude_vars = array("id");
		
		// Если есть сериализованные данные в БД, то указать здесь для авто сериализации/ансериализации
		protected $_serialized = array();
		
		// Переменные
		public $isNewRecord = true;			// Новая запись
		
		// Конструктор
		public function __construct($array = false)
		{
			// Запрос к текущей БД на показ столбцов
			$Query = static::dbConnection()->query("SHOW COLUMNS FROM ".static::$mysql_table);
						
			// Динамически создаем переменные на основе таблицы	
			while ($data = $Query->fetch_assoc())
			{
				$this->mysql_vars[] = $data["Field"];
			}
						
			// Если создаем по массиву
			if (is_array($array))
			{
				foreach ($array as $key => $val)
				{	
					$this->{$key} = $val;
				}
				
				// Если есть ID - он уже не новая запись
				if ($this->id)
				{
					$this->isNewRecord = false;	
				}
			}
			
			// Если есть сериализованные данные – делаем ансериалайз для доступности (потом перед сохранением назад в сериалайз)
			if (count($this->_serialized)) {
				foreach ($this->_serialized as $serialized_field) {
					// При создании нового объекта может передаваться уже нормальный unserialized массив,
					// если создавать объект класса вручную через new ClassName(array(social => array(...))
					// Поэтому если тип $this->{$serialized_field} уже массив, то ничего разселиализовывать не нужно
					if (!is_array($this->{$serialized_field})) {
						$this->{$serialized_field} = unserialize($this->{$serialized_field});
					}
				}
			}
		}
		
		/*
		 * Получаем все записи
		 * $params - дополнительные параметры (condition - дополнительное условие, order - параметры сортировки)
		 * $count - только подсчитываем кол-во найденных
		 */
		public static function findAll($params = array(), $count = false)
		{
			// Получаем все данные из таблицы + доп условие, если есть
			$result = static::dbConnection()->query("
				SELECT * FROM ".static::$mysql_table." 
				WHERE true ".(!empty($params["condition"]) ? " AND ".$params["condition"] : "") // Если есть дополнительное условие выборки
				.(!empty($params["order"]) ? " ORDER BY ".$params["order"] : "")				// Если есть условие сортировки
				.(!empty($params["limit"]) ? " LIMIT ".$params["limit"] : "")					// Если есть условие лимита
				);
	
			// Если успешно получили и (что-то есть или нужно просто подсчитать)
			if ($result && ($result->num_rows || $count))
			{
				// Если нужно только подсчитать
				if ($count)
				{
					return $result->num_rows;
				}
				
				// Получаем имя текущего класса
				$class_name = get_called_class();
				
				// Создаем массив объектов
				while ($array = $result->fetch_assoc())
				{
					$return[] = new $class_name($array);
				}
				
				// Возвращаем массив объектов
				return $return;
			}
			else
			{
				return false;
			}
		}
		
		/*
		 * Получаем одну запись
		 * $params - дополнительные параметры (condition - дополнительное условие, order - параметры сортировки)
		 */
		public static function find($params = array())
		{
			// Получаем все данные из таблицы + доп условие, если есть
			$result = static::dbConnection()->query("
				SELECT * FROM ".static::$mysql_table."
				WHERE true ".(!empty($params["condition"]) ? " AND ".$params["condition"] : "") // Если есть дополнительное условие выборки
				.(!empty($params["order"]) ? " ORDER BY ".$params["order"] : "")				// Если есть условие сортировки
				." LIMIT 1");
	
			// Если успешно получили
			if ($result->num_rows)
			{
				// Создаем объект
				$array = $result->fetch_assoc();
				
				// Получаем название класса
				$class_name = get_called_class();
				
				// Возвращаем объект
				return new $class_name($array);
			}
			else
			{
				return false;
			}
		}
		
		/* 
		 * Загрузка записи по ID
		 */
		public static function findById($id)
		{
			// Получаем все данные из таблицы
			$result = static::dbConnection()->query("SELECT * FROM ".static::$mysql_table." WHERE id=".$id);
					
			// Если успешно получили
			if ($result)
			{
				// Создаем объект
				$array = $result->fetch_assoc();
				
				// Получаем название класса
				$class_name = get_called_class();
				
				// Возвращаем объект
				return new $class_name($array);	
			}
			else
			{
				return false;
			}
		}
		
		/*
		 * Получаем ID последней записи
		 */
		public static function lastId()
		{
		 	return static::dbConnection()
		 		->query("SELECT * FROM ".static::$mysql_table." ORDER BY id DESC LIMIT 1")
		 		->fetch_assoc()["id"];
		}
		 
		/*
		 * Функция определяет соединение БД
		 */
		public static function dbConnection()
		{
			return dbSettings();	// По умолчанию возвращает подключение к бд Settings
		}
		
		/*
		 * Сохранение
		 * $single_field – если изменять надо только одно поле в БД (чтобы не напрягать БД)
		 */
		 public function save($single_field = false)
		 {
		 	// Перед сохранением
		 	if (method_exists($this, "beforeSave")) {
			 	$this->beforeSave();
		 	}
		 	
		 	
		 	// Проверяем есть ли в бд шидзе с таким ID
			if ($this->isNewRecord)
			{
				// Составляем запрос на добавление новой записи
			 	foreach($this->mysql_vars as $field)
			 	{
			 		if (in_array($field, $this->_exclude_vars)) // Пропускаем поля, которые не надо сохранять
			 			continue;
			 		
			 		// Если значение установлено, будем его сохранять
			 		if (isset($this->{$field}))
			 		{
				 		$into[]		= $field;
				 		
				 		// Если текущее поле в формате serialize
				 		if (in_array($field, $this->_serialized)) {
					 		$values[]	= "'".serialize($this->{$field})."'";		// Сериализуем значение обратно
				 		} else {
					 		$values[]	= "'".$this->{$field}."'";					// Оборачиваем значение в кавычки		
				 		}				 		
			 		}
			 	}

				$result = static::dbConnection()->query("INSERT INTO ".static::$mysql_table." (".implode(",", $into).") VALUES (".implode(",", $values).")");
				// echo "The query was: "."INSERT INTO ".static::$mysql_table." (".implode(",", $into).") VALUES (".implode(",", $values).")";
				if ($result) {
					$this->id = static::dbConnection()->insert_id; 	// Получаем ID
					$this->isNewRecord = false;						// Уже не новая запись
					
					// После сохранения 
					if (method_exists($this, "afterSave")) {
						$this->afterSave();								// После сохранения
					}
					
					return $this->id;
				} else {
					return false;
				}
			}	
			else
			{
				// Если изменять только одно поле в БД 
				if ($single_field) {
					$query[] = $single_field." = '".$this->{$single_field}."'";
				} else {
				// Иначе сохраняем все
					// Составляем запрос на сохранение всего
				 	foreach($this->mysql_vars as $field)
				 	{
				 		if (in_array($field, $this->_exclude_vars)) // Пропускаем поля, которые не надо сохранять
				 			continue;
				 		
				 		// Если текущее поле в формате serialize
					 	if (in_array($field, $this->_serialized)) {
					 		$query[] = $field." = '".serialize($this->{$field})."'";	// Сериализуем значение
					 	} else {
						 	$query[] = $field." = '".$this->{$field}."'";
					 	}
				 	}
				}
				
							 					
				$result = static::dbConnection()->query("UPDATE ".static::$mysql_table." SET ".implode(",", $query)." WHERE id=".$this->id);
				
				if ($result) {
					$this->afterSave();	// После сохранения
					return $this->id;
				} else {
					return false;
				}
			}	
		 }
		 
		 
		 /*
		  * Перед сохранением
		  */
		 public function beforeSave()
		 {
			 // Будет переопределяться в child-классах
		 }
		 
		 /*
		  * После сохранения
		  */
		 public function afterSave()
		 {
			 // Будет переопределяться в child-классах
		 }
		 
		 /*
		  * Полностью удалить модель
		  */
		 public function delete()
		 {
		 	// Удаляем из БД
			static::dbConnection()->query("DELETE FROM ".static::$mysql_table." WHERE id=".$this->id);
			
			// Удаляем объект
			unset($this);
		 }
		 
		 /**
		 * Удалить модель по ID.
		 * 
		 */
		public static function deleteById($id)
		{
			// Удаляем из БД
			static::dbConnection()->query("DELETE FROM ".static::$mysql_table." WHERE id=".$id);
		}
		 
		 /*
		 * Возвращает массив с сохраняемой в БД информацией 
		 * $select –  выбрать конкретные поля
		 */
		public function dbData($select = false)
		{
			// Возвращаем только те данные, которые берутся из БД
			foreach ($this->mysql_vars as $var) {
				if ($select && !in_array($var, $select)) { 	// Если нужно выбрать конкретные поля
					continue;								// то пропускаем ненужные
				}
				$return[$var] = $this->{$var};
			}
			
			// Если в БД еще есть сериализованные данные
			if (count($_serialized)) {
				foreach ($_serialized as $var) {
					if ($select && !in_array($var, $select)) { 	// Если нужно выбрать конкретные поля
						continue;								// то пропускаем ненужные
					}
					$return[$var] = $this->{$var};
				}
			}
			
			return $return;
		}
		
	}

?>