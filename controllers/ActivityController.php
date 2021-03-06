<?php

	// Контроллер
	class ActivityController extends Controller
	{
		public $defaultAction = "index";

		public static $allowed_users = [Admin::USER_TYPE];

		// Папка вьюх
		protected $_viewsFolder	= "activity";

		public function beforeAction()
		{
			$this->addJs('ng-activity-app');
		}

		public function actionIndex()
		{
            $this->checkRights(Shared\Rights::EC_ACTIVITY);

			$this->setTabTitle("Активность");

			$this->render("index", [
				"ang_init_data" => $ang_init_data,
			]);
		}

        public function actionGet()
        {
            extract($_GET);

            $query = ['DATE(created_at)="' . fromDotDate($date) . '"', "user_id={$user_id}"];
            $mango_query = ['DATE(FROM_UNIXTIME(start)) = "' . fromDotDate($date) . '"'];

            $tmp = dbConnection()->query("select created_at from `logs` where " . static::query($query) . " order by created_at asc");

            if (! $tmp->num_rows) {
                returnJSON(-1);
            }

            $data = [];
            while($row = $tmp->fetch_object()) {
                $data[] = $row->created_at;
            }

            $return['first_action_time'] = self::timeFormat($data[0]);
            $return['last_action_time'] = self::timeFormat($data[count($data) - 1]);

            // подсчитываем разницу во времени между действиями
            $pauses = [];

            if (count($data) > 2) {
                $diffs = [];

                foreach(range(0, count($data) - 2) as $i) {
                    $d1 = new DateTime($data[$i]);
                    $d2 = new DateTime($data[$i + 1]);
                    $interval = $d1->diff($d2);
                    $diffs[$i] = ($interval->h * 60) + $interval->i; // разница в минутах
                }

                asort($diffs);

                foreach(array_slice(array_reverse($diffs, true), 0, 5, true) as $i => $diff) {
                    $pauses[] = [
                        'start' => self::timeFormat($data[$i]),
                        'end'   => self::timeFormat($data[$i + 1]),
                        'diff'  => $diff
                    ];
                }

                usort($pauses, function($a, $b) {
                    return $a['start'] - $b['start'];
                });
            }

            $return['pauses'] = $pauses;
            $return['database_operations'] = Log::count(['condition' => self::query($query, ['row_id>0'])]);
            $return['url_views'] = Log::count(['condition' => self::query($query, ["type='url'"])]);
            $return['outgoing_calls_successful'] = self::mangoCount($mango_query, ["from_extension='{$user_id}'", 'answer>0']);
            $return['outgoing_calls_failed'] = self::mangoCount($mango_query, ["from_extension='{$user_id}'", 'answer=0']);
            $return['incoming_calls'] = self::mangoCount($mango_query, ["to_extension='{$user_id}'", 'answer>0']);
            $return['calls_duration'] = round(dbEgerep()->query("select sum(finish - answer) as s from mango where " . self::query($mango_query, ['answer>0', "(from_extension={$user_id} or to_extension={$user_id})"]))->fetch_object()->s / 60);

            returnJSON($return);
        }

        private static function query($query, $conditions = [])
        {
            $query = array_merge($query, $conditions);
            return implode(' AND ', $query);
        }

        private static function mangoCount($query, $conditions = [])
        {
            $query = self::query($query, $conditions);
            return dbEgerep()->query("select count(*) as cnt from mango where {$query}")->fetch_object()->cnt;
        }

        private static function timeFormat($date)
        {
            return date('H:i', strtotime($date));
        }
	}
