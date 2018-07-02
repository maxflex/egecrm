<?php

// Контроллер
class TeacherProfileController extends Controller
{
    public $defaultAction = "list";

    public static $allowed_users = [Teacher::USER_TYPE];

    // Папка вьюх
    protected $_viewsFolder	= "teacher-profile";

    public function beforeAction()
    {
        $this->addJs("ng-teacher-profile-app");
    }

	public function actionList()
	{
		$this->setTabTitle('Преподаватели');

		$ang_init_data = angInit([
            "Teachers" => Teacher::getHead(User::id()),
        ]);

		$this->render("list", [
            "ang_init_data" => $ang_init_data
        ]);
	}

	public function actionStudents()
	{
		$this->setTabTitle('Ученики');

		$ang_init_data = angInit([
            "Students" => Teacher::getHeadStudents(User::id()),
        ]);

		$this->render("students", [
            "ang_init_data" => $ang_init_data
        ]);
	}
}
