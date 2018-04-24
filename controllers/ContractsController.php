<?php

// Контроллер
class ContractsController extends Controller
{
    public $defaultAction = "list";

    // Папка вьюх
    protected $_viewsFolder	= "contracts";

    public function beforeAction()
    {
        $this->addJs("ng-contracts-app");
    }

    public function actionList()
    {
        // не надо панель рисовать
        $this->_custom_panel = true;

        $ang_init_data = angInit([
            'currentPage'	=> $_GET['page'] ? $_GET['page'] : 1,
        ]);

        $this->render("list", [
            "ang_init_data" => $ang_init_data,
        ]);
    }

	public function actionPayments()
	{
		$this->setTabTitle('График платежей');

		$ang_init_data = angInit([
            'currentPage'	=> $_GET['page'] ? $_GET['page'] : 1,
        ]);

		$this->render("payments", [
            "ang_init_data" => $ang_init_data,
        ]);
	}

    public function actionAjaxGetContracts()
    {
        extract($_POST);

        returnJsonAng(
            Contract::getData($page)
        );
    }

	public function actionAjaxGetPayments()
	{
		extract($_POST);

		returnJsonAng(
			ContractPayment::getData($page)
		);
	}
}
