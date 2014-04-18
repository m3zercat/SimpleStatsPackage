<?php

define('EMAILDIR', STAT_ROOT.'/templates');

class emailer {

	private $mail = null;
	private $twig = null;

	public function __construct()
	{
		$this->mail = $this->getPHPMailer();
		Twig_Autoloader::register();
		$loader = new Twig_Loader_Filesystem(EMAILDIR);
		$this->twig = new Twig_Environment($loader, array());
	}

	private function getPHPMailer()
	{
		$mail = new PHPMailer(true);
		$mail->isSMTP();
		$mail->Host = SMTP_HOSTNAME;
		$mail->Port = SMTP_PORT;
		$mail->Username = SMTP_USERNAME;
		$mail->Password = SMTP_PASSWORD;
		$mail->SMTPAuth = true;
		$mail->setFrom(SMTP_USERNAME, 'STATS PACKAGE');
		return $mail;
	}

	public function send()
	{
		try{
			$this->mail->send();
		}
		catch (Exception $e)
		{
			error_log($e);
			return $e;
		}
		return true;
	}

	public function reset()
	{
		$this->mail->clearAllRecipients();
		$this->mail->clearAttachments();
		$this->mail->clearCustomHeaders(); // clears embedded images too
		$this->mail->Subject = null;
		$this->mail->Body = null;
		$this->mail->AltBody = null;
	}

	public function addTo($address, $name)
	{
		$this->mail->addAddress($address, $name);
	}

	public function setSubject($subject)
	{
		$this->mail->Subject = $subject;
	}

	public function setBody($tpl, $content)
	{
		$template = $this->twig->loadTemplate($tpl);
		$this->mail->msgHTML($template->render($content), EMAILDIR);
	}
}

