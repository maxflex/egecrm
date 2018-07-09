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
			extract($_POST);

			if (isset($subject) && $subject != 'all') {
				$id_subject = array_search($subject, Subjects::$short_eng);
			}

			$condition = "description!='' " . (isset($id_subject) ? " AND FIND_IN_SET($id_subject, subjects_ec)" : "") ;
/*
			returnJSON($condition);
			exit();
*/
			$Teachers = Teacher::findAll([
				'condition' => $condition,
				'limit' => $limit,
			]);


			returnJSON(Teacher::forApi($Teachers));
		}

		public function actionCountTeachers()
		{
			echo Teacher::count([
				'condition' => "description!=''"
			]);
		}

        /**
         * @param int $id       Teacher id.
         *
         * @return string       Teacher data in JSON format.
         */
        public function actionGetTeacherById()
        {
            extract($_POST);
            if ($id = intval($id) && $Teacher = Teacher::findById($id)) {
                returnJSON(Teacher::forApi($Teacher));
            }
        }

        /**
         * $id_subject постом должно быть передано
         */
        public function actionGetTeachersBySubjectAndGrade()
        {
            extract($_POST);
            if (($id_subject = intval($id_subject)) && ($grade = intval($grade) )) {
                $Teachers = Teacher::findAll([
                    "condition" => "description!='' ".
                        "AND CONCAT(',', CONCAT(subjects_ec, ',')) LIKE '%,{$id_subject},%' ".
                        "AND CONCAT(',', CONCAT(grades, ',')) LIKE '%,{$grade},%' "
                ]);

            }
            returnJSON($Teachers ? Teacher::forApi($Teachers) : []);
        }

		public function actionMetro()
		{
			extract($_POST);

			$Metors = Metro::calculate($lat, $lng);

			returnJSON($Metors);
		}

		public function actionTeacherStatistics()
		{
			extract($_POST);
			returnJSON(Teacher::stats($tutor_id));
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
			if ($Request->processIncoming()) {
                // Сохраняем заявку
                $Request->save();
            }
        }

		// Обновление статуса SMS
		public function actionSmsStatus()
		{
			extract($_POST);

			if (isset($mes)) {
	            // message
	        } else {
	            // status
				$SMS = SMS::find([
					"condition" => "external_id={$id}"
				]);

				if ($SMS) {
					$SMS->id_status = $status;
					$SMS->save("id_status");
					SMS::notifyStatus($SMS);
				}
	        }
		}
	}
