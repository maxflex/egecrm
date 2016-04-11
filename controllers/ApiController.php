<?php

	// Контроллер
	class ApiController extends Controller
	{


		############################################################################################
		##################################### ИНИЦИАЛИЗАЦИЯ ########################################
		############################################################################################


		// Ключ API, проверяется при каждом запросе к API
		const API_KEY = "44327d40af8a93c23497047c08688a50"; // MD5 от «А то ругать будут!»

		// Перед выполнением любого действия, устанавливаем заголовок для JSON данных API
    //		public function beforeAction()
    //		{
    //			// Тип данных - JSON
    //			header('Content-Type: application/json');
    //
    //			// Первым делом проверяем API_KEY
    //			if (trim($_POST["API_KEY"]) != self::API_KEY) {
    //				self::errorMessage("Invalid API_KEY");
    //			}
    //		}

		/*
		 * JSON-сообщение с ошибкой
		 * $error_message – сообщение ошибки
		 */
		public static function errorMessage($error_message)
		{
			exit(json_encode(array(
					"error_message"	=> $error_message,
					"post_data"		=> $_POST,
				)));
		}

		/*
		 * Возвратить JSON
		 */
		public static function returnJSON($Object)
		{
			echo json_encode($Object);
		}


		public static function getFromServer($method, $postData = array())
		{
			define("API_KEY", md5("Hg)9nv71Vgssdf0")); // Ключ АПИ

			$url = 'http://192.168.0.32:8080/api/?api_key='. API_KEY . '&action='. $method;

			echo $url;

			$ch = curl_init($url);
			//curl_setopt($ch, CURLOPT_POST, 1);
			//curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);

			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

			$response = curl_exec($ch);

			return $response;
		}

		############################################################################################
		##################################### ПОЛУЧЕНИЕ ДАННЫХ #####################################
		############################################################################################

		// Расписание
		public function actionGetSchedule()
		{
			extract($_POST);

			$Groups = Group::findAll([
				"condition" => ($id_branch > 0 ? "id_branch=$id_branch AND " : "" ) . "id_subject=$id_subject AND grade=$id_grade"
			]);

			returnJSON($Groups);
		}

		// Получить ученика по коду
		// Возвращает имя ученика в родительном падеже
		public function actionGetStudentByCode()
		{
			extract($_POST);

			$Student = Student::find([
				"condition" => "code='$code'"
			]);

			if ($Student) {
				$nc = new NCLNameCaseRu();

				$name = $nc->setFirstName($Student->first_name)->setSecondName($Student->last_name)->getFormatted(NCL::$RODITLN, "N S");

				returnJSON($name);
			} else {
				returnJSON(false);
			}
		}

		public function actionGetTeachers()
		{
			$Teachers = Teacher::getPublished();
			
			$return = [];
			
			foreach ($Teachers as &$Teacher) {
				$object = [];
				foreach (Teacher::$api_fields as $field) {
					$object[$field] = $Teacher->{$field};
				}
				$return[] = $object;
			}
			
			returnJSON($return);
		}

        /**
         * $id_subject постом должно быть передано
         */
        public function actionGetTeachersBySubjectAndGrade()
        {
            extract($_POST);
            $return = [];
            if (($id_subject = intval($id_subject)) && ($grade = intval($grade) )) {
                $Teachers = Teacher::findAll([
                    "condition" => "published = 1 ".
                        "AND CONCAT(',', CONCAT(subjects, ',')) LIKE '%,{$id_subject},%' ".
                        "AND CONCAT(',', CONCAT(grades, ',')) LIKE '%,{$grade},%' "
                ]);

                foreach ($Teachers as &$Teacher) {
                    $object = [];
                    foreach (Teacher::$api_fields as $field) {
                        $object[$field] = $Teacher->{$field};
                    }
                    $return[] = $object;
                }
            }
            returnJSON($return);
        }
		
		public function actionMetro()
		{
			extract($_POST);
			
			$Metors = Metro::calculate($lat, $lng);

			returnJSON($Metors);
		}

		############################################################################################
		#################################### ДОБАВЛЕНИЕ ДАННЫХ #####################################
		############################################################################################

		// Добавляем заявку
		public function actionAddRequest()
		{
			// Добавляем заявку
			$Request = new Request($_POST);

			// Обработка входящей заявки
			$Request->processIncoming();

			// Сохраняем заявку
			$Request->save();
		}

	}
