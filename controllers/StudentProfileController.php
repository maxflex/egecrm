<?php

// Контроллер
class StudentsProfileController extends Controller
{
    public $defaultAction = "photo";

    public static $allowed_users = [Admin::USER_TYPE, Student::USER_TYPE, Teacher::USER_TYPE];

    // Папка вьюх
    protected $_viewsFolder	= "students";

    public function beforeAction()
    {
        $this->addJs("ng-student-profile-app");
    }

	public function actionBalance()
	{
		$this->setTabTitle('Баланс счета');

		$ang_init_data = angInit([
            "id_student" => User::id(),
        ]);

		$this->render("balance", [
            "ang_init_data" => $ang_init_data
        ]);
	}

    public function actionPhoto()
    {
        $id_student = User::id();

        if ($Student = Student::findById($id_student)) {
            $StudentProfile = [];

            foreach ([
                'id', 'first_name', 'last_name', 'middle_name',
                'has_photo_cropped', 'has_photo_original',
                'photo_original_size', 'photo_cropped_size', 'photo_url', 'photo_extension'
                     ] as $key) {
                $StudentProfile[$key] = $Student->$key;
            }
        }

        $this->setTabTitle($Student->last_name . ' ' . $Student->first_name. ' | Фото ученика');
        $ang_init_data = angInit([
            "Student" => $StudentProfile,
        ]);

        $this->render("photo", [
            "ang_init_data" => $ang_init_data
        ]);
    }

	// public function actionTeacherLk()
	// {
	// 	$this->setRights([Teacher::USER_TYPE]);
	//
	// 	$id_student = $_GET["id_student"];
	// 	$this->hasAccess('students', $id_student, 'id_head_teacher');
	//
	// 	$Student = Student::getLight($id_student);
	//
	// 	$this->setTabTitle("Профиль ученика – {$Student->last_name} {$Student->first_name}");
	//
	// 	$ang_init_data = angInit(compact('id_student'));
	//
	// 	$this->render("teacher_lk", compact('ang_init_data'));
	// }


    ##################################################
    ###################### AJAX ######################
    #################################################

    public function actionAjaxDeletePhoto() {
        extract($_POST);
        if ($student_id == User::id() || User::isAdmin()) {
            $Student = Student::findById($student_id);

            unlink($Student->photoPath());
            unlink($Student->photoPath('_original'));

            $User = User::find(['condition' => 'id_entity = '.$student_id]);
            $User->update(['photo_extension'=>'','has_photo_cropped'=>0]);

            if (!User::isAdmin()) {
                $User->toSession();
            }
        }
    }
}
