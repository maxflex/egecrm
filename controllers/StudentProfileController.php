<?php

// Контроллер
class StudentsProfileController extends Controller
{
    public $defaultAction = "photo";

    public static $allowed_users = [User::USER_TYPE, Student::USER_TYPE];

    // Папка вьюх
    protected $_viewsFolder	= "students";

    public function beforeAction()
    {
        $this->addJs("ng-student-profile-app");
    }

    public function actionPhoto()
    {
        $id_student = User::fromSession()->id_entity;

        if ($Student = Student::findById($id_student)) {
            $StudentProfile = $Student->dbData(['id', 'first_name', 'last_name', 'middle_name']);
            foreach (['has_photo_original', 'photo_original_size', 'has_photo_cropped', 'photo_cropped_size', 'photo_url', 'photo_extension'] as $key) {
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


    ##################################################
    ###################### AJAX ######################
    #################################################

    public function actionAjaxDeletePhoto() {
        extract($_POST);
        if ($student_id == User::fromSession()->id_entity) {
            $Student = Student::findById($student_id);

            unlink($Student->photoPath());
            unlink($Student->photoPath('_original'));

            $User = User::find(['condition' => 'id_entity = '.$student_id]);
            $User->photo_extension = '';
            $User->save('photo_extension');
            $_SESSION['user']['photo_extension'] = '';
//            $User->toSession();
        }
    }
}