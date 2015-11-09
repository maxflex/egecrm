<?php
	class Comment extends Model
	{
	
		/*====================================== ПЕРЕМЕННЫЕ И КОНСТАНТЫ ======================================*/

		public static $mysql_table	= "comments";
		
		# Места, где отображаются комментарии
		const PLACE_STUDENT = 'STUDENT';
		const PLACE_REQUEST = 'REQUEST';
		const PLACE_GROUP 	= 'GROUP';

		
		/*====================================== СИСТЕМНЫЕ ФУНКЦИИ ======================================*/
		
		public function __construct($array)
		{
			parent::__construct($array);
			
			$this->coordinates = $this->getCoordinates();
		}
		
		
		/*====================================== СТАТИЧЕСКИЕ ФУНКЦИИ ======================================*/
		
		
		/**
		 * Типа виджет (хотя я ненавижу это слово) комментариев.
		 * $place - где комментарии? редактирование заявки и тд.
		 * $id_place - где конкретно? если в редактировании заявки, то это ID ученика заявки (id_student)
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
			echo "<div class='comment-block'><div class='existing-comments'>";
			echo "<script src='js/comments-app.js' type='text/javascript'></script>"; 
			
			// Отображение уже имеющихся комментариев
			foreach ($Comments as $Comment) {
				echo "
					<div id='comment-block-{$Comment->id}'>
						<span class='glyphicon glyphicon-stop' style='float: left'></span>
						<div style='display: initial' data-id='{$Comment->id}' id='comment-{$Comment->id}'>{$Comment->comment}</div>
						<span class='save-coordinates'>(". $Comment->getCoordinates() .")</span>
						<span class='glyphicon opacity-pointer glyphicon-pencil no-margin-right' onclick='editComment({$Comment->id})'></span>
						<span class='glyphicon opacity-pointer text-danger glyphicon-remove' onclick='deleteComment({$Comment->id})'></span>
					</div>
				";
			}
			// Отображение добавления комментариев
			echo "
				</div>
				<div style='height: 25px'>
					<span class='glyphicon glyphicon-forward pointer no-margin-right comment-add' 
						id='comment-add' place='$place' id_place='$id_place'></span>
					<input id='comment-add-field' class='comment-add-field' type='text' placeholder='Введите комментарий...'>
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