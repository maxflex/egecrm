<?php	// Контроллер	class StreamController extends Controller	{        const PER_PAGE = 50;		public $defaultAction = 'index';		// Папка вьюх		protected $_viewsFolder	= 'stream';		public function beforeAction()		{			$this->addJs('ng-stream-app');		}		// Страница входа		public function actionIndex()		{            # @rights-refactored            $this->checkRights(Shared\Rights::EC_STREAM);			$this->setTabTitle('Стрим');            $actions = [];            $query = dbConnection()->query("SELECT action FROM stream GROUP BY action");            while($row = $query->fetch_object()) {                $actions[] = $row->action;            }            $types = [];            $query = dbConnection()->query("SELECT type FROM stream WHERE (type != '' AND type IS NOT NULL) GROUP BY type");            while($row = $query->fetch_object()) {                $types[] = $row->type;            }			$this->render('index', [				'ang_init_data' => angInit(compact('actions', 'types')),			]);		}        public function actionGet()        {            $page = intval($_GET['page']);            if (! $page) {                $page = 1;            }            // С какой записи начинать отображение, по формуле            $start_from = ($page - 1) * self::PER_PAGE;            $search = isset($_COOKIE['stream']) ? json_decode($_COOKIE['stream']) : (object)[];            $search = filterParams($search);            $conditions = [1];            // $search = filterParams($search);            // $query = Stream::orderBy('id', 'desc');            if (isset($search->mobile)) {                $conditions[] = "mobile=" . $search->mobile;            }            if (isset($search->action)) {                $conditions[] = "action=" . toString($search->action);            }            if (isset($search->google_id)) {                $conditions[] = "google_id=" . toString($search->google_id);            }            if (isset($search->type)) {                $conditions[] = "type=" . toString($search->type);            }            if (isset($search->date_start)) {                $conditions[] = "created_at >= '" . fromDotDate($search->date_start) . " 00:00:00'";            }            if (isset($search->date_end)) {                $conditions[] = "created_at <= '" . fromDotDate($search->date_start) . " 23:59:59'";            }            $count = dbConnection()->query("SELECT COUNT(*) AS cnt FROM stream WHERE " . implode(' AND ', $conditions))->fetch_object()->cnt;            // exit("SELECT * FROM stream "            //     . " WHERE " . implode(' AND ', $conditions)            //     . " ORDER BY id DESC"            //     . " LIMIT " . $start_from . ", " .self::PER_PAGE);            $query = dbConnection()->query("SELECT * FROM stream "                . " WHERE " . implode(' AND ', $conditions)                . " ORDER BY id DESC"                . " LIMIT " . $start_from . ", " .self::PER_PAGE            );            $stream = [];            while($row = $query->fetch_object()) {                $row->google_id = $row->google_id . ' ';                $stream[] = $row;            }            returnJsonAng([                'per_page' => self::PER_PAGE,                'stream' => $stream,                'last_page' => ($start_from + self::PER_PAGE) > $count,                'count' => $count            ]);        }	}