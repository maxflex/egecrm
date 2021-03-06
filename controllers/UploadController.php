<?php

	// Контроллер загрузок
	// https://github.com/verot/class.upload.php

	class UploadController extends Controller
	{
		public $defaultAction = "AjaxUpload";

		// Папка вьюх
		protected $_viewsFolder	= "";

		// Строка с сообщением об ошибке
		const ERROR = "ERROR";
		const OK	= "OK";

		// Временная директория электронных версий договоров
		const CONTRACTS_TMP_DIR = "files/contracts/tmp/";

		// Временная директория электронных версий договоров
		const EMAIL_TMP_DIR = "files/email/tmp/";

		public static $allowed_users = [Admin::USER_TYPE, Teacher::USER_TYPE, Student::USER_TYPE];

		##################################################
		###################### AJAX ######################
		##################################################

		/**
		 * Загрузка аватара преподавателя
		 *
		 */
		public function actionAjaxTeacher()
		{
			extract($_POST);
			extract($_FILES);

			$handle = new upload($teacher_photo);
			$handle->allowed = array('image/*');

			$handle->image_min_width = 240;
			$handle->image_min_height = 300;
			$handle->image_max_width = 240;
			$handle->image_max_height = 300;

			if ($handle->uploaded) {
				$handle->file_overwrite = true;

				// Даем временное уникальное имя файлу
				$handle->file_new_name_body = $id_teacher . "_2x";
				$handle->file_new_name_ext = 'jpg';

				$handle->process(Teacher::UPLOAD_DIR);

				if ($handle->processed) {
					// создаем сжатую копию изображения
					$handle = new upload($teacher_photo);
					$handle->image_resize = true;
					$handle->image_x = 120;
					$handle->image_y = 150;
					$handle->file_new_name_body = $id_teacher;
					$handle->file_new_name_ext = 'jpg';
					$handle->file_overwrite = true;
					$handle->process(Teacher::UPLOAD_DIR);
					returnJson([
						"status" => self::OK,
					]);
				} else {
					returnJson([
						"status" => self::ERROR,
						"error"	=> $handle->error,
					]);
				}
			} else {
				returnJson([
					"status" => self::ERROR,
					"error"	=> $handle->error,
				]);
			}
		}

		/**
         * Загрузка аватара пользователя
         *
         */
        public function actionAjaxUser()
        {
            extract($_POST);
            extract($_FILES);

            $handle = new upload($photo);
            $handle->allowed = array('image/*');

            $handle->image_min_width = 240;
            $handle->image_min_height = 300;

            if ($handle->uploaded) {

                $handle->file_overwrite = true;

                // Даем временное уникальное имя файлу
                $handle->file_new_name_body = $user_id . "_original";
                $handle->file_new_name_ext = $handle->file_src_name_ext;

                $handle->process(User::UPLOAD_DIR);

                if ($handle->processed) {
                    $User = User::findById($user_id);
                    $User->photo_extension = $handle->file_src_name_ext;
                    $User->save('photo_extension');

                    returnJson([
                        'extension' => $handle->file_src_name_ext,
                        'size'      => filesize('' . User::UPLOAD_DIR .  $user_id . '_original.' . $handle->file_src_name_ext)
                    ]);
                } else {
                    returnJson([
                        "status" => self::ERROR,
                        "error"	=> $handle->error,
                    ]);
                }
            } else {
                returnJson([
                    "status" => self::ERROR,
                    "error"	=> $handle->error,
                ]);
            }
        }

        /**
         * Загрузка аватара студента
         *
         */
        public function actionAjaxStudent()
        {
            extract($_POST);
            extract($_FILES);

            $handle = new upload($photo);
            $handle->allowed = array('image/*');

            $handle->image_min_width = 240;
            $handle->image_min_height = 300;

            if ($handle->uploaded) {

                $handle->file_overwrite = true;

                // Даем временное уникальное имя файлу
                $handle->file_new_name_body = $student_id . "_original";
                $handle->file_new_name_ext = $handle->file_src_name_ext;

                $handle->process(Student::UPLOAD_DIR);

                if ($handle->processed) {
                    $Student = Student::findById($student_id);
                    $Student->photo_extension = $handle->file_src_name_ext;
                    $Student->save('photo_extension');

                    returnJson([
                        'extension' => $handle->file_src_name_ext,
                        'size'      => filesize('' . Student::UPLOAD_DIR .  $student_id . '_original.' . $handle->file_src_name_ext)
                    ]);
                } else {
                    returnJson([
                        "status" => self::ERROR,
                        "error"	=> $handle->error,
                    ]);
                }
            } else {
                returnJson([
                    "status" => self::ERROR,
                    "error"	=> $handle->error,
                ]);
            }
        }

        /**
         * Обрезка аватара пользователя
         */
        public function actionAjaxCropped()
        {
            extract($_POST);
            extract($_FILES);

            $User = User::findById($user_id);

            $this->handleCropped($croppedImage, $user_id, $User->photo_extension, User::UPLOAD_DIR);

            $User = User::findById($user_id);
            $User->update(['has_photo_cropped' => 1]);

            if (User::id() == $user_id) {
                $User->toSession();
            }

            echo $User->photoCroppedSize();
            exit();
        }

        public function actionAjaxCroppedStudent()
        {
            extract($_POST);
            extract($_FILES);

            if ($student_id == User::id() || User::isAdmin()) {

                $Student = Student::findById($student_id);
                $this->handleCropped($croppedImage, $student_id, $Student->photo_extension, Student::UPLOAD_DIR);

				$Student->has_photo_cropped = 1;
                $Student->save('has_photo_cropped');
                echo $Student->photoCroppedSize();
                exit();
            }
        }

        private function handleCropped($croppedImage, $id, $extension, $dir)
        {
            // создаем сжатую копию изображения
            $handle = new upload($croppedImage);

            if ($handle->uploaded) {
                $handle->image_resize = true;
                $handle->image_x = 240;
                $handle->image_y = 300;
                $handle->file_new_name_body = $id;
                $handle->file_new_name_ext = $extension;
                $handle->file_overwrite = true;
                $handle->process($dir);
            }
        }

		/**
		 * Загрузка электронной версии договора.
		 *
		 */
		public function actionAjaxContract()
		{
			extract($_FILES);

			$handle = new upload($contract_file);

			if ($handle->uploaded) {
				$handle->file_overwrite = true;

				// Временный файл
//				$handle->file_new_name_ext = 'tmp';

				// Даем временное уникальное имя файлу
				$handle->file_new_name_body = uniqid("contract_", true);

//				$contract_file["tmp_name"] = BASE_ROOT.self::CONTRACTS_TMP_DIR.$handle->file_new_name_body.".".$handle->file_new_name_ext;


				$handle->process(self::CONTRACTS_TMP_DIR);

				// указываем размер
				$size = round($handle->file_src_size / 1000000, 3); // в мегабайтах, 1 цифра после запятой

				// если размер меньше мегабайта, отобразить в киллобайтах
				if ($size < 1) {
					$size = round($size * 1000) . " Кб";
				} else {
					$size = round($size, 1) . " Мб";
				}

				if ($handle->processed) {
					returnJson([
						"name"			=> "tmp/" . $handle->file_dst_name,
						"uploaded_name"	=> $contract_file["name"],
						"size"			=> $size,
						"coords"		=> User::fromSession()->login." ". dateFormat(now()),
					]);
				} else {
					returnJson(self::ERROR);
				}
			} else {
				returnJson(self::ERROR);
			}
		}

		/**
		 * Загрузка файла в email
		 *
		 */
		public function actionAjaxEmail()
		{
			extract($_FILES);

			$handle = new upload($email_file);

			if ($handle->uploaded) {
				$handle->file_overwrite = true;

				// Временный файл
//				$handle->file_new_name_ext = 'tmp';

				// Даем временное уникальное имя файлу
				$handle->file_new_name_body = uniqid("email_", true);

//				$contract_file["tmp_name"] = BASE_ROOT.self::CONTRACTS_TMP_DIR.$handle->file_new_name_body.".".$handle->file_new_name_ext;


				$handle->process(Email::UPLOAD_DIR);

				// указываем размер
				$size = round($handle->file_src_size / 1000000, 3); // в мегабайтах, 1 цифра после запятой

				// если размер меньше мегабайта, отобразить в киллобайтах
				if ($size < 1) {
					$size = round($size * 1000) . " Кб";
				} else {
					$size = round($size, 1) . " Мб";
				}

				if ($handle->processed) {
					returnJson([
						"name"			=> $handle->file_dst_name,
						"uploaded_name"	=> $email_file["name"],
						"size"			=> $size,
						"coords"		=> User::fromSession()->login." ". dateFormat(now()),
					]);
				} else {
					returnJson(self::ERROR);
				}
			} else {
				returnJson(self::ERROR);
			}

		}


		/**
		 * Загрузка файла в email
		 *
		 */
		public function actionAjaxTask()
		{
			extract($_FILES);

			$handle = new upload($task_file);

			// if ($handle->uploaded) {
				$handle->file_overwrite = true;

				// Временный файл
//				$handle->file_new_name_ext = 'tmp';

				// Даем временное уникальное имя файлу
				$handle->file_new_name_body = uniqid("task_", true);

				$handle->process(Task::UPLOAD_DIR);

				// указываем размер
				$size = round($handle->file_src_size / 1000000, 3); // в мегабайтах, 1 цифра после запятой

				// если размер меньше мегабайта, отобразить в киллобайтах
				if ($size < 1) {
					$size = round($size * 1000) . " Кб";
				} else {
					$size = round($size, 1) . " Мб";
				}

				if ($handle->processed) {
					returnJson([
						"name"			=> $handle->file_dst_name,
						"uploaded_name"	=> $task_file["name"],
						"size"			=> $size,
						"coords"		=> User::fromSession()->login." ". dateFormat(now()),
					]);
				} else {
					returnJson(self::ERROR);
				}
			// } else {
			// 	returnJson(self::ERROR);
			// }
		}
	}
