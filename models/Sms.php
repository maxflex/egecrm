<?php

class SMS extends Model
{
	public static $mysql_table	= "sms";

    public $log_except = [
        'external_id',
        'id_status'
    ];

	const INLINE_SMS_LENGTH = 60;
	const PER_PAGE = 50; // Сколько отображать на странице списка


	public function __construct($array, $light = false)
	{
		parent::__construct($array);

        if (!$light) {
        	$this->status = $this->getStatus();
			$this->number_formatted = formatNumber($this->number);
        }

		if (mb_strlen($this->message) > self::INLINE_SMS_LENGTH) {
			$this->message_short = mb_strimwidth($this->message, 0, self::INLINE_SMS_LENGTH, '...', 'utf-8');
		}
		$this->coordinates = $this->getCoordinates();
	}

	public static function applySearchFilters($search)
	{
		if ($search) {
			return "number LIKE '%{$search}%' OR message LIKE '%{$search}%'";
		}
		return null;
	}

	public static function getByPage($page, $search = false)
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

		$condition['condition'] = self::applySearchFilters($search);

		$SMS = self::findAll($condition);

		return $SMS;
	}

	public static function pagesCount($search)
	{
		return SMS::count(["condition" => self::applySearchFilters($search)]);
	}

	public static function sendToNumbers($numbers, $message, $create = true) {
		foreach ($numbers as $number) {
			self::send($number, $message, $create);
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
				"login"		=> SMS_LOGIN,
				"psw"		=> SMS_PSW,
                "fmt"       => 1, // 1 – вернуть ответ в виде чисел: ID и количество SMS через запятую (1234,1)
                "charset"   => "utf-8",
				"phones"	=> $number,
				"mes"		=> $message,
				"sender"    => "EGE-Centr",
			);
			$result = self::exec(SMS_HOST, $params, $create);
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
		// Сохраняем отправленную смс
			$info = explode(",", $result);

			$info = [
				"id_status"   => 0,
				"external_id" => $info[0],
				"message"	=> $params["mes"],
				"number"	=> $params["phones"],
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
			$this->id_user = User::fromSession() ? User::id() : 0; // если смс отправлено системой (без сесссии), то 0
		}
	}

	public function getCoordinates()
	{
		if ($this->id_user) {
			if ($user = findObjectInArray(User::getCached(), ['id' => $this->id_user])) {
 				$this->user_login = $user['login'];
			} else {
				$this->user_login = Admin::getLogin($this->id_user);
			}
		} else {
			$this->user_login = "system";
		}
		return $this->user_login. " ". dateFormat($this->date);
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
		// Статусы тут: https://smsc.ru/api/http/status_messages/statuses/#menu
		switch ($sms_status) {
			case -3 : 	return "сообщение не найдено";
			case -1: 	return "ожидает отправки";
			case 0: 	return "передано оператору";
			case 1: 	return "доставлено";
			case 2: 	return "прочитано";
			case 3: 	return "просрочено";
			case 20: 	return "невозможно доставить";
			case 22:	return "неверный номер";
			case 23:	return "запрещено";
			case 24:	return "недостаточно средств";
			case 25:	return "недоступный номер";
			default:	return "неизвестно";
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
        $code = mt_rand(10000, 99999);
        $client = new Predis\Client();
        $client->set("egecrm:codes:{$User->id}", $code, 'EX', 120);

        Sms::send($User->phone, $code . ' – код для входа в ЛК', false);
        // cache(["codes:{$user_id}" => $code], 3);
        return $code;
    }
}
