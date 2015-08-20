<?php

class Email extends Model
{
	public static $mysql_table	= "email";
	
	// путь хранения электронных версий договоров
	const UPLOAD_DIR = "files/email/";

	public $_serialized = ["files"];


	const INLINE_EMAIL_LENGTH = 60;
	
	public function __construct($array)
	{
		parent::__construct($array);
		
		$this->getCoordinates();
		
		if (mb_strlen($this->message) > self::INLINE_EMAIL_LENGTH) {
			$this->message_short = mb_strimwidth($this->message, 0, self::INLINE_EMAIL_LENGTH, '...', 'utf-8');
		}
	}
	
	public static function send($email, $subject, $message, $files)
	{
		foreach ($files as $file) {
			unset($file['email_uploaded_file_id']);	
		}
		
		$Email = new self([
			'email' 	=> $email,
			'subject' 	=> $subject,
			'message' 	=> $message,
			'files'		=> $files,
		]);
		
		$mail = self::initMailer();
		
		foreach ($files as $file) {
			$mail->addAttachment(self::UPLOAD_DIR . $file['name'], $file['uploaded_name']);  	
		}
		
		$mail->addAddress($email);
		$mail->Subject = $subject;
		$mail->Body = $message;
		
		$mail->send();
		
		$Email->save();
		return $Email;
	}
	
	public static function initMailer()
	{
		$mail = new PHPMailer;

		$mail->isSMTP();                                      // Set mailer to use SMTP
		$mail->Host = 'smtp.yandex.ru'; 					  // Specify main and backup SMTP servers
		$mail->SMTPAuth = true;                               // Enable SMTP authentication
		$mail->Username = 'maksim@kolyadin.com';                // SMTP username
		$mail->Password = 'rrn1840055';                        // SMTP password
		$mail->SMTPSecure = 'ssl';                            // Enable TLS encryption, `ssl` also accepted
		$mail->Port = 465;                                    // TCP port to connect to
		
		$mail->From = 'maksim@kolyadin.com';
		$mail->FromName = 'ЕГЭ Центр';
		
		$mail->CharSet = 'UTF-8';
		$mail->isHTML(true); 
		
		return $mail;
	}

	public function beforeSave()
	{
		$this->date = now();
		$this->id_user = User::fromSession() ? User::fromSession()->id : 0; // если смс отправлено системой (без сесссии), то 0
	}
	
	public function getCoordinates()
	{
		if ($this->id_user) {
			$this->user_login = User::getCached()[$this->id_user]['login'];
			
			$this->coordinates = $this->user_login. " ". dateFormat($this->date);
		} else {
			$this->user_login = "system";
		}
	}	
}