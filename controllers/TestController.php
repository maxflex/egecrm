<?php

	// Контроллер
	class TestController extends Controller
    {
        public $defaultAction = "test";
        // Папка вьюх
        protected $_viewsFolder = "test";

        public function actionTest()
        {
			echo Teacher::getEfficency(111);
            echo '<hr />';
        }

		/**
		 * Обновление кеша полей таблиц.
		 */
		public function actionClearColumnCache()
		{
			$Tables = dbConnection()->query("SHOW TABLES");
			while ($Table = $Tables->fetch_assoc())
			{
				$table_name = $Table["Tables_in_".DB_PREFIX."egecrm"];
				memcached()->delete($table_name."Columns");
				$Query = dbConnection()->query("SHOW COLUMNS FROM `".$table_name."`");
				$mysql_vars = [];
				while ($data = $Query->fetch_assoc()) {
					$mysql_vars[] = $data["Field"];
				}
				memcached()->set($table_name."Columns", $mysql_vars, 3600 * 24);
			}
		}
	}
