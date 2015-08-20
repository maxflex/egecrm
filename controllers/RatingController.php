<?php	// Контроллер	class RatingController extends Controller	{		public $defaultAction = "rating";				// Папка вьюх		protected $_viewsFolder	= "rating";				public function actionRating()		{			$this->setTabTitle("Рейтинг филиалов");						/*			$Students = Student::findAll([				"condition" => "branches != ''"			]);						foreach ($Students as &$Student) {				$Student->Contract = $Student->getLastContract();				foreach ($Student->branches as $id_branch) {					$rating[$id_branch]++;					if ($Student->Contract) {						$rating[$id_branch] += count($Student->Contract->subjects); 					}				}			}*/						$rating = array();						foreach (Branches::$all as $id_branch => $name) {				$data = self::_actualAndPrognozRating($id_branch);								$rating_actual = 0;				foreach ($data['actual'] as $grade => $rating_data) {					foreach ($rating_data as $id_subject => $rating) {						$rating_actual += $rating;					}				}								$rating_prognoz = 0;				foreach ($data['prognoz'] as $grade => $rating_data) {					foreach ($rating_data as $id_subject => $rating) {						$rating_prognoz += $rating;					}				}								$r[$id_branch] = ($rating_actual + $rating_prognoz);				$r_data[$id_branch] = [					"actual"	=> $rating_actual,					"prognoz"	=> $rating_prognoz,				];			}									asort($r);			$r = array_reverse($r, true);						$this->render("rating", [				"rating"	=> $r,				"rdata"		=> $r_data,			]);		}				public function actionBranchRating()		{			$id_branch = $_GET['id_branch'];						$this->setTabTitle("Рейтинг филиала ".Branches::getById($id_branch));						$data = self::_actualAndPrognozRating($id_branch);						$this->render("branch_rating", [				"id_branch" 	=> $id_branch,				"result" 		=> $data['actual'],				"result_prognoz"=> $data['prognoz'],			]);		}				private function _actualAndPrognozRating($id_branch)		{			// Актуальный рейтинг из договоров			$Students = Student::getWithContractByBranch($id_branch);						foreach ($Students as $Student) {				$Contracts = $Student->getActiveContracts();				foreach ($Contracts as $Contract) {/*					if ($Contract->grade == 9) {						preType($Contract);										}*/					foreach ($Contract->subjects as $Subject) {						$id_subject = $Subject['id_subject'];												// количество филиалов без учета Тургеневской						$branches_count = count($Student->branches);												if ($branches_count > 1) {							if (in_array(Branches::TRG, $Student->branches)) {								$branches_count--; // если есть Тургеневская, вычетаем ее								}						}						$result[$Contract->grade][$id_subject] += round(1 / $branches_count, 1);					}				}			}						// Прогнозируемый рейтинг из заявок			$Requests = Request::findAll([				"condition" => "id_branch=$id_branch AND id_status IN (" . RequestStatuses::AWAITING . "," . RequestStatuses::NOT_DECIDED .")",				"group"		=> "id_student",			]);						foreach ($Requests as $Request) {				foreach ($Request->subjects as $id_subject) {					$coef = $Request->id_status == RequestStatuses::AWAITING ? 0.5 : 0.25;					$result_prognoz[$Request->grade][$id_subject] += $coef;				}			}						foreach ($result_prognoz as &$prognoz) {				foreach ($prognoz as &$prognoz_data) {					$prognoz_data = round($prognoz_data, 1);				}			}						return [				"actual" => $result,				"prognoz"=> $result_prognoz,			];		}				##################################################		###################### AJAX ######################		##################################################							}