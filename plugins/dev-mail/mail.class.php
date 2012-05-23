<?php

class Mail {
	
	private $subject = '';
	private $headers = array();
	private $to = '';
	private $message;
	
	public function __construct($to, $subject, $message, $from = false, $replyto = false) {
		$from?$this->AddHeader('From', $from):null;
		$replyto?$this->AddHeaders('Reply-To', $replyto):null;
		$this->AddHeader('MIME-Version', '1.0');
		$this->AddHeader('Content-Type', 'text/plain; charset=UTF-8');
		$this->AddHeader('Content-Transfer-Encoding', 'base64');
		$this->AddHeader('X-Mailer', 'PHP/'.phpversion());

		$this->to = $to;
		$this->subject = $subject;
		$this->message = $message;
	}
	
	public function Send() {
		$subject = $this->mime_encode($this->subject);
		$body = base64_encode($this->message);
		
		$headers = implode("\r\n", $this->headers);		
		return mail($this->to, $subject, $body, $headers);
	}
	
	public function SetSubject($subject) {
		$this->subject = $subject;
	}
	
	public function SetMessage($message) {
		$this->message = $message;
	}
	
	public function SetAdressee($to) {
		$this->to = $to;
	}
	
	public function AddHeader($header, $content) {
		$this->headers[$header] = "$header: $content";
	}
	
	private function mime_encode($content) {
		return "=?UTF-8?B?".base64_encode($content)."?=";
	}
		
}
