<?php

# Контроллер
class SearchController extends Controller
{
    public $defaultAction = "search";
    private static $resultsInSearch = 30;

    // Папка вьюх
    protected $_viewsFolder = "search";

    private function microtime_float()
    {
        list($usec, $sec) = explode(" ", microtime());
        return ((float)$usec + (float)$sec);
    }

    /**
     * Новый поиск
     * @param string $keyword поисковый запрос
     */
    private function search($query)
    {
        # замер скорости поиска, старт
        $start = $this->microtime_float();

        # удаляем все тире, чтобы искалось по номеру телефона и так, и так
        $query = str_replace("-", "", $query);

        # очистка и разбиение запроса на ключевые слова и формирование текста для FULLTEXT
        $query = trim($query);
        $queryArray = explode(' ', $query);

        # результативные и промежуточные массивы
        $Students = []; # массив для хранения учеников
        $Tutors = []; # массив для храненния преподавателей
        $Requests = []; # массив для хранения результатов поиска по заявкам
        $Representatives = []; # массив для хранения преставителей
        $Contracts = []; # массив для хранения контракты

        # запрос по поиску студента
        $sqlSearchStudents = "SELECT
                                id, first_name, last_name, middle_name
                              FROM
                                students
                              WHERE
                                " . $this->sqlLikeGenerator($queryArray) . "
							  ORDER BY id DESC
                              LIMIT " . self::$resultsInSearch;

        $studientsResult = dbConnection()->query($sqlSearchStudents);
        if ($studientsResult->num_rows) {
            while ($row = $studientsResult->fetch_object()) {
				$row->link = "student/{$row->id}";
                $Students[] = $row;
            }
        }

        # запрос по поиску представителя
        $sqlSearchRepresentatives = "SELECT
                                id, first_name, last_name, middle_name
                              FROM
                                representatives
                              WHERE
                               " . $this->sqlLikeGenerator($queryArray) . "
							  ORDER BY id DESC
                              LIMIT " . self::$resultsInSearch;

        #  обработка ответа по представителям
        $representativesResult = dbConnection()->query($sqlSearchRepresentatives);
        if ($representativesResult->num_rows > 0) {
            while ($row = $representativesResult->fetch_object()) {
                $row->id_student = dbConnection()->query('SELECT id FROM students WHERE id_representative = ' . $row->id)->fetch_object()->id;
				$row->link = "student/{$row->id_student}";
                $Representatives[] = $row;
            }
        }

        # запрос по поиску преподавателя
        $sqlSearchTutors = "SELECT
                                id, first_name, last_name, middle_name
                              FROM
                                tutors
                              WHERE
                                in_egecentr=2 AND " . $this->sqlLikeGenerator($queryArray) . "
                               LIMIT " . self::$resultsInSearch;

        #  обработка ответа по преподователям
        $tutorsResult = dbEgerep()->query($sqlSearchTutors);
        if ($tutorsResult->num_rows > 0) {
            while ($row = $tutorsResult->fetch_object()) {
				$row->link = "teachers/edit/{$row->id}";
                $Tutors[] = $row;
            }
        }

        # запрос по поиску заявок
        $sqlSearchRequest = "SELECT
                                id, name, phone, phone2, phone3
                              FROM
                                requests
                              WHERE
                                adding=0 AND " . $this->sqlLikeGenerator($queryArray, ['name', 'phone', 'phone2', 'phone3']) . "
							  ORDER BY id DESC
                              LIMIT " . self::$resultsInSearch;

        # обработка ответа по заявкам
        $requestResult = dbConnection()->query($sqlSearchRequest);
        if ($requestResult->num_rows > 0) {
            while ($row = $requestResult->fetch_object()) {
				$row->link = "requests/edit/{$row->id}";
                $Requests[] = $row;
            }
        }

        // # запрос по поиску по номеру договора
        // $sqlSearchContacts = "SELECT
        //                         id_contract, id_student
        //                       FROM
        //                         contract_info
        //                       WHERE
        //                       " . $this->sqlLikeGenerator($queryArray, ['id_contract']) . "
        //                        LIMIT " . self::$resultsInSearch;
		//
        // # обработка ответа по договорам
        // $contractsResult = dbConnection()->query($sqlSearchContacts);
        // if ($contractsResult->num_rows > 0) {
        //     while ($row = $contractsResult->fetch_object()) {
        //         $Contracts[] = $row;
        //     }
        // }

        # окончание работы таймера
        $end = $this->microtime_float();

        if (!count($Students) && !count($Tutors) && !count($Requests) && !count($Representatives) && !count($Contracts)) {
            // не найдено
            return [
                'result' => 0,
                'search' => []
            ];
        } else {
            return returnJson([
                'timing' => $end - $start,
                'result' => count($Students) + count($Tutors) + count($Requests) + count($Representatives) + count($Contracts),
                'search' => [
                    "students" => $Students,
                    "tutors" => $Tutors,
                    'requests' => $Requests,
                    'representatives' => $Representatives,
                    // 'contracts' => $Contracts
                ]
            ]);
        }
    }

    /**
     * формирвоание части запроса
     * @param array $words
     * @param array $fields
     */
    private function sqlLikeGenerator($words = [], $fields = ['first_name', 'last_name', 'middle_name', 'email', 'phone', 'phone2', 'phone3'])
    {
        $conditions = [];
        foreach ($words as $word) {
            $condition = array_map(function($field) use ($word) {
                return "{$field} LIKE '%{$word}%'";
            }, $fields);
            $conditions[] = '(' . implode(' OR ', $condition) . ')';
        }
        return implode(' AND ', $conditions);
    }

    public function actionSearch()
    {
        //проверяем на наличеи в запросе данных, что надо выводить в json
        if (! empty($_POST['query'])) {
            $res = $this->search($_POST['query']);
            echo json_encode($res);
        } else {
            echo json_encode(['error' => 'bad request']);
        }
        exit;
    }

}
