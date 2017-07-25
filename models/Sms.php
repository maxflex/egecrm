<?php

class SMS extends Model
{
	public static $mysql_table	= "sms";

    public $log_except = [
        'id_smsru',
        'id_status'
    ];

	const INLINE_SMS_LENGTH = 60;
	const PER_PAGE = 50; // Сколько отображать на странице списка


	public function __construct($array, $light = false)
	{
		parent::__construct($array);

        if (!$light) {
            $this->getCoordinates();
        }

		if (mb_strlen($this->message) > self::INLINE_SMS_LENGTH) {
			$this->message_short = mb_strimwidth($this->message, 0, self::INLINE_SMS_LENGTH, '...', 'utf-8');
		}
	}

	public static function applySearchFilters($filter)
	{
		$phone   = isset($filter['phone']) ? cleanNumberForSearch($filter['phone']) : '';
		$search = isset($filter['search']) ? $filter['search'] : '';

		if ($phone || $search) {
			return "number LIKE '%$phone%' AND message LIKE '%$search%'";
		}
		return '';
	}

	/**
	 * Получить заявки по номеру страницы и ID списка из RequestStatuses Factory.
	 *
	 */
	public static function getByPage($page, $filter = false)
	{
		if (!$page) {
			$page = 1;
		}
		// С какой записи начинать отображение, по формуле
		$start_from = ($page - 1) * self::PER_PAGE;

		$condition = [
			"order" 	=> "id DESC",
			"limit" 	=> $start_from. ", " .self::PER_PAGE
		];

		$condition['condition'] = self::applySearchFilters($filter);

		$SMS = self::findAll($condition);

		return $SMS;
	}

	public static function pagesCount($search)
	{
		return SMS::count(["condition" => self::applySearchFilters($search)]);
	}

	public static function sendToNumbers($numbers, $message, $create = true) {
		foreach ($numbers as $number) {
			self::send($number, $message);
		}
	}


	public static function send($to, $message, $create = true)
	{
		$to = explode(",", $to);
		foreach ($to as $number) {
			$number = cleanNumber($number);
			$number = trim($number);
			if (!preg_match('/[0-9]{10}/', $number) || $number[1] != '9') {
				continue;
			}
			$params = array(
				"api_id"	=>	"8d5c1472-6dea-d6e4-75f4-a45e1a0c0653",
				"to"		=>	$number,
				"text"		=>	$message,
				"from"      =>  "EGE-Centr",
			);
			$result = self::exec("http://sms.ru/sms/send", $params, $create);
		}


		return $result;
	}

	protected static function exec($url, $params, $create = true)
	{
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT, 30);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
		$result = curl_exec($ch);
		curl_close($ch);

		// Сохраняем отправленную смс
		$info = explode("\n", $result);

		$info = [
			"id_status" => $info[0],
			"id_smsru"	=> $info[1],
			"balance"	=> $info[2],

			"message"	=> $params["text"],
			"number"	=> $params["to"],
		];

		// создаем объект для истории
		if ($create) {
            return SMS::add($info);
        }
	}

	public function beforeSave()
	{
		if ($this->isNewRecord) {
			$this->date = now();
			$this->id_user = User::fromSession() ? User::fromSession()->id : 0; // если смс отправлено системой (без сесссии), то 0
		}
	}

	public function getCoordinates()
	{
		if ($this->id_user) {
            if ($user = findObjectInArray(User::getCached(), ['id' => $this->id_user])) {
                $this->user_login = $user['login'];
            } else {
                $this->user_login = User::findById($this->id_user)->login;
            }
		} else {
			$this->user_login = "system";
		}
		$this->coordinates = $this->user_login. " ". dateFormat($this->date);

		$this->coordinates .= '
		<svg class="sms-status ' . ($this->id_status == 103 ? 'delivered' : ($this->id_status == 102 ? 'inway' : 'not-delivered') ) .'">
			<circle r="3" cx="7" cy="7"></circle>
		</svg>';
	}

	public function getStatus()
	{
		return static::textStatus($this->id_status);
	}

	/**
	 * Получить текстовый статус в зависимости от когда СМС.
	 *
	 */
	public static function textStatus($sms_status)
	{
		// Статусы тут: http://sms.ru/?panel=api&subpanel=method&show=sms/status
		switch ($sms_status) {
			case -2 : return "не доставлено";
			case 100: return "в очереди";
			case 101: return "передается оператору";
			case 102: return "в пути";
			case 103: return "доставлено";
			case 104: return "время жизни истекло";
			case 105: return "удалено оператором";
			case 106: return "сбой в телефоне";
			case 107: return "не доставлено";
			case 108: return "отклонено";
			case 207: return "недопустимый номер";
			default:  return "неизвестно";
		}
	}

	public static function notifyStatus($SMS)
    {
        // если групповая смс, не отсылать событие
        if (! $SMS->id_user) {
            return false;
        }

        // если прошло более минуты – не отсылать
        if (time() - strtotime($SMS->date) > 60) {
            return false;
        }

        // Отправлять только пользователю, который отправил СМС
        Socket::trigger(
            'user_' . $SMS->id_user,
            'sms', [
                'id' => $SMS->id,
                'status' => $SMS->id_status
            ],
            'egecrm'
        );
        return true;
    }

    public static function verify($User)
    {
        $code = mt_rand(1000, 9999);
        $client = new Predis\Client();
        $client->set("egecrm:codes:{$User->id}", $code, 'EX', 120);

        Sms::send($User->phone, $code . ' – код для входа в ЛК', false);
        // cache(["codes:{$user_id}" => $code], 3);
        return $code;
    }
}
