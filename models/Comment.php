<?php
	class Comment extends Model
	{
	
		/*====================================== ПЕРЕМЕННЫЕ И КОНСТАНТЫ ======================================*/

		public static $mysql_table	= "comments";
		
		# Места, где отображаются комментарии
		const PLACE_REQUEST_EDIT = 'REQUEST_EDIT';
		
		/*====================================== СИСТЕМНЫЕ ФУНКЦИИ ======================================*/
		
		
		
		/*====================================== СТАТИЧЕСКИЕ ФУНКЦИИ ======================================*/
		
		
		/**
		 * Типа виджет (хотя я ненавижу это слово) комментариев.
		 * $place - где комментарии? редактирование заявки и тд.
		 * $id_place - где конкретно? если в редактировании заявки, то это ID заявки
		 */
		public static function display($place, $id_place)
		{
			// Получаем все комментарии для заданного места
			$Comments = self::findAll([
				"condition" => "place='$place' AND id_place=$id_place"
			]);
			
			//  Отображаем комментарии
			self::buildHtml($Comments, $place, $id_place);
		}
		
		
		/**
		 * Построить функциональный HTML из комментариев.
		 * 
		 */
		public static function buildHtml($Comments, $place, $id_place)
		{
			// Подключаем скрипт-контроллер
			echo "<script src='js/comments-app.js' type='text/javascript'></script>"; 
			
			echo "<div class='comment-block'><div class='existing-comments'>";
			
			// Отображение уже имеющихся комментариев
			foreach ($Comments as $Comment) {
				echo "
					<div>
						<span class='glyphicon glyphicon-stop'></span>{$Comment->comment}
						 <span class='save-coordinates'>(". $Comment->getCoordinates() .")</span>
					</div>
				";
			}
			// Отображение добавления комментариев
			echo "
				</div>
				<div style='height: 25px'>
					<span class='glyphicon glyphicon-forward opacity-pointer no-margin-right' 
						id='comment-add' place='$place' id_place='$id_place'></span>
					<input id='comment-add-field' type='text'>
				</div>
			";
			
			echo "</div>"; // закрываем comment-block
		}
				
		/*====================================== ФУНКЦИИ КЛАССА ======================================*/
		
		public function beforeSave()
		{
			$this->date 	= now();
			$this->id_user 	= User::fromSession()->id;
		}
		
		
		/**
		 * Получить отформатированные данные сохранившего.
		 * 
		 */
		public function getCoordinates()
		{
			return User::findById($this->id_user)->login . " " . date("d.m.y в H:i", strtotime($this->date));
		}
		
	}