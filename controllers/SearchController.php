<?php# Контроллерclass SearchController extends Controller{	public $defaultAction = "search";	private static $resultsInSearch = 30;	// Папка вьюх	protected $_viewsFolder	= "search";	private function microtime_float()	{		list($usec, $sec) = explode(" ", microtime());		return ((float)$usec + (float)$sec);	}	/**	 * Новый поиск	 * @param string $keyword поисковый запрос     */    private function search($query)    {        # замер скорости поиска, старт        $start = $this->microtime_float();        # удаляем все тире, чтобы искалось по номеру телефона и так, и так        $query = str_replace("-", "", $query);        # очистка и разбиение запроса на ключевые слова и формирование текста для FULLTEXT        $query = trim($query);        $queryArray = explode(' ', $query);        # результативные и промежуточные массивы        $Students = []; # массив для хранения учеников        $Tutors = []; # массив для храненния преподавателей        $Requests = []; # массив для хранения результатов поиска по заявкам        $Representatives = []; # массив для хранения преставителей        $Contracts = []; # массив для хранения контракты        # запрос по поиску студента        $sqlSearchStudents = "SELECT                                id, first_name, last_name, middle_name                              FROM                                students                              WHERE                                " . $this->sqlLikeGenerator($queryArray) . "                               LIMIT " . self::$resultsInSearch;        $studientsResult = dbConnection()->query($sqlSearchStudents);        if ($studientsResult->num_rows) {            while ($row = $studientsResult->fetch_object()) {                $Students[] = $row;            }        }        # запрос по поиску представителя        $sqlSearchRepresentatives = "SELECT                                id, first_name, last_name, middle_name                              FROM                                representatives                              WHERE                               " . $this->sqlLikeGenerator($queryArray) . "                               LIMIT " . self::$resultsInSearch;        #  обработка ответа по представителям        $representativesResult = dbConnection()->query($sqlSearchRepresentatives);        if ($representativesResult->num_rows > 0) {            while ($row = $representativesResult->fetch_object()) {                $Representatives[] = $row;            }        }        # запрос по поиску преподавателя        $sqlSearchTutors = "SELECT                                id, first_name, last_name, middle_name                              FROM                                tutors                              WHERE                                " . $this->sqlLikeGenerator($queryArray) . "                               LIMIT " . self::$resultsInSearch;        #  обработка ответа по преподователям        $tutorsResult = dbEgerep()->query($sqlSearchTutors);        if ($tutorsResult->num_rows > 0) {            while ($row = $tutorsResult->fetch_object()) {                $Tutors[] = $row;            }        }        # запрос по поиску заявок        $sqlSearchRequest = "SELECT                                id, name, phone, phone2, phone3                              FROM                                requests                              WHERE                                " . $this->sqlLikeGenerator($queryArray, ['name', 'phone', 'phone2', 'phone3']) . "                               LIMIT " . self::$resultsInSearch;        # обработка ответа по заявкам        $requestResult = dbConnection()->query($sqlSearchRequest);        if ($requestResult->num_rows > 0) {            while ($row = $requestResult->fetch_object()) {                $Requests[] = $row;            }        }        # запрос по поиску по номеру договора        $sqlSearchContacts = "SELECT                                id_contract, id_student                              FROM                                contract_info                              WHERE                              " . $this->sqlLikeGenerator($queryArray, ['id_contract']) . "                               LIMIT " . self::$resultsInSearch;        # обработка ответа по договорам        $contractsResult = dbConnection()->query($sqlSearchContacts);        if ($contractsResult->num_rows > 0) {            while ($row = $contractsResult->fetch_object()) {                $Contracts[] = $row;            }        }        # окончание работы таймера        $end = $this->microtime_float();        // header('Content-Type: application/json'); # все равно без заголовка не хочет отдавать в json корректно        if (!count($Students) && !count($Tutors) && !count($Requests) && !count($Representatives) && !count($Contracts)) {            // не найдено            return [                'result' => 0,                'search' => [                ]            ];        } else {            return returnJson([                'timing' => $end - $start,                'result' => count($Students) + count($Tutors) + count($Requests) + count($Representatives) + count($Contracts),                'search' => [                    "students" => $Students,                    "tutors" => $Tutors,                    'requests' => $Requests,                    'representatives' => $Representatives,                    'contracts' => $Contracts                ]            ]);        }	}    /**     * формирвоание части запроса     * @param array $words     * @param array $fields     */    private function sqlLikeGenerator($words = [], $fields = ['first_name', 'last_name', 'middle_name', 'email', 'phone', 'phone2', 'phone3'])    {        $query = '';        foreach ($words as $z => $word) {            $query .= '(';                foreach ($fields as $i => $field) {                    $query .= $field . " like '%" . $word . "%'";                    $query .= ($i != count($fields) - 1) ? ' OR ' : ' ';                }            $query .= ')';            $query .= ($z != count($words) - 1) ? ' AND ' : ''; # было AND, но тогда поиск не работал        }        return $query;    }	/**	 * Поиск.	 *	 */	private function search2($keyword){		$start = $this->microtime_float();		$keyword = str_replace("-", "", $keyword); // удаляем все тире, чтобы искалось по номеру телефона и так, и так		$Students = [];		$Teachers = [];		$Requests = []; //массив для хранения результатов поиска по заявкам		$Representatives = []; //массив для хранения преставителей		$Contracts = []; //поиск по контрактам		$SearchInOrders = []; // массив для поиска в заявках		$idsRepresentative = []; //связи студент -> представитель		/*		exit("			SELECT id_student FROM search_students			WHERE search_text LIKE '%$keyword%'		"); */		//поиск по контрактам //2137		$cntrcts = dbConnection()->query("				SELECT id_user FROM contracts				WHERE id_contract LIKE '%$keyword%'			");		if ($cntrcts->num_rows) {			while ($row = $cntrcts->fetch_object()) {				$student_ids[] = $row->id_user;			}		}		$result = dbConnection()->query("			SELECT id_student FROM search_students			WHERE search_text LIKE '%$keyword%'		");		if ($result->num_rows) {			while ($row = $result->fetch_object()) {				$student_ids[] = $row->id_student;			}		}		if(count($student_ids) > 0){			$query = dbConnection()->query("				SELECT id, first_name, last_name, middle_name,id_representative FROM students				WHERE id IN (". implode(",", $student_ids) .")			");			while ($row = $query->fetch_object()) {				// проверяе  если пустые данные у учеников, откладываем в отдельный массив для получение заявок				if(empty($row->first_name) and empty($row->last_name) and empty($row->middle_name)){					$SearchInOrders[] = $row->id;				}else{					$Students[] = $row;					$idsRepresentative[$row->id_representative] = $row->id;				}			}		}		//поиск по представителю		if(count($idsRepresentative) > 0){			/*exit("			SELECT id,first_name,last_name,middle_name FROM representatives				WHERE				(first_name LIKE '%$keyword%' OR			last_name LIKE '%$keyword%' OR			middle_name LIKE '%$keyword%' OR			phone LIKE '%$keyword%' OR			phone2 LIKE '%$keyword%' OR			phone3 LIKE '%$keyword%' OR			email LIKE '%$keyword%')				AND id IN (". implode(",", $idsRepresentative) .")			");*/			$queryForRepresentatives = dbConnection()->query("				SELECT id,first_name,last_name,middle_name FROM representatives				WHERE				(first_name LIKE '%$keyword%' OR				last_name LIKE '%$keyword%' OR				middle_name LIKE '%$keyword%' OR				phone LIKE '%$keyword%' OR				phone2 LIKE '%$keyword%' OR				phone3 LIKE '%$keyword%' OR				email LIKE '%$keyword%')				AND id IN (". implode(",", array_keys($idsRepresentative)) .")			");			if ($queryForRepresentatives->num_rows) {				while($row = $queryForRepresentatives->fetch_object()){					$row->student_id = $idsRepresentative[$row->id];					$Representatives[] = $row;				}			}		}		//поиск заявок		if(count($SearchInOrders) > 0){			$queryForRequests = dbConnection()->query("				SELECT id,name FROM requests				WHERE				 (name LIKE '%$keyword%' OR				phone LIKE '%$keyword%' OR				phone2 LIKE '%$keyword%' OR				phone3 LIKE '%$keyword%')				 AND				id_student				IN (". implode(",", $SearchInOrders) .")			");			if ($queryForRequests->num_rows) {				while($row = $queryForRequests->fetch_object()){					$Requests[] = $row;				}			}		}		$query_teachers = dbEgerep()->query("			SELECT id, first_name, last_name, middle_name FROM tutors			WHERE in_egecentr > 0 AND (first_name LIKE '%$keyword%' OR				last_name LIKE '%$keyword%' OR				middle_name LIKE '%$keyword%' OR				phone LIKE '%$keyword%' OR				phone2 LIKE '%$keyword%' OR				phone3 LIKE '%$keyword%' OR				email LIKE '%$keyword%')		");		while ($row = $query_teachers->fetch_object()) {			$Teachers[] = $row;		}		$end = $this->microtime_float();		if (!count($Students) && !count($Teachers) && !count($Requests) && !count($Representatives)) {			// не найдено			return [				'result' => true,				'search' => [				]			];		} else {			return [				'timing' => $end - $start,				'result' => count($Students) + count($Teachers) + count($Requests) + count($Representatives),				'search' => [					"students" => $Students,					"teachers" => $Teachers,					'requests' => $Requests,					'representatives' => $Representatives				]			];		}	}	private function isValidJSON($str) {		json_decode($str);		return json_last_error() == JSON_ERROR_NONE;	}	public function actionSearch()	{		//проверяем на наличеи в запросе данных, что надо выводить в json		$data = file_get_contents('php://input');		if($this->isValidJSON($data)){            $d = json_decode($data,true);            $d['query'] = trim($d['query']);            if(empty($d['query'])){                echo json_encode(['result'=> false,'code' => '1']);exit;            }else{                $res = $this->search($d['query']);                echo json_encode($res);exit;                exit;            }        }		extract($_POST);		$this->setTabTitle("Рультаты поиска по запросу «". $text. "»");		$this->addJs("ng-search-app");		# если пустой запрос		$text = trim($text);		$text = str_replace("-", "", $text); // удаляем все тире, чтобы искалось по номеру телефона и так, и так		if (empty($text)) {            $this->render("no_results");            return;        }		$Students = [];		$Teachers = [];		$result = dbConnection()->query("			SELECT id_student FROM search_students			WHERE search_text LIKE '%$text%'		");		if ($result->num_rows) {            while ($row = $result->fetch_object()) {                $student_ids[] = $row->id_student;            }            $query = dbConnection()->query("				SELECT id, first_name, last_name, middle_name FROM students				WHERE id IN (". implode(",", $student_ids) .")			");            while ($row = $query->fetch_object()) {                $Students[] = $row;            }        }		$query_teachers = dbEgerep()->query("			SELECT id, first_name, last_name, middle_name FROM tutors			WHERE in_egecentr > 0 AND (first_name LIKE '%$text%' OR				last_name LIKE '%$text%' OR				middle_name LIKE '%$text%' OR				phone LIKE '%$text%' OR				phone2 LIKE '%$text%' OR				phone3 LIKE '%$text%' OR				email LIKE '%$text%')		");		while ($row = $query_teachers->fetch_object()) {            $Teachers[] = $row;        }		if (!count($Students) && !count($Teachers)) {            $this->render("no_results");        } else {            // Если найден один ученик            if (!count($Teachers) && count($Students) == 1) {                $this->redirect("student/" . $Students[0]->id, true);            }            // Если найден один учитель            else if (!count($Students) && count($Teachers) == 1) {                $this->redirect("teachers/edit/" . $Teachers[0]->id, true);            } else {                $this->render("results", [                    "Students" => $Students,                    "Teachers" => $Teachers,                ]);            }        }	}    private function _generateCondition($table, $text, $search_fields, &$sql)	{		foreach ($search_fields as &$search_field) {			$search_field = $table . "." . $search_field;		}		foreach ($search_fields as $search_field) {			$sql[] = "CONVERT($search_field USING utf8) LIKE '%$text%'";		}	}	##################################################	###################### AJAX ######################	##################################################}