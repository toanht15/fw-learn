<?php

require_once 'text/aafwTemplateTag.php';

AAFW::import('jp.aainc.aafw.base.aafwException');
AAFW::import('jp.aainc.aafw.mail.aafwMail');

class MailManager {

	public $FromAddress		= '';
	public $Subject			= '';
	public $BodyPlain		= '';
	public $BodyHTML		= '';
	public $ToAddress		= '';
	public $CcAddress		= '';
	public $BccAddress		= '';
	public $Envelope		= '';
	public $Charset			= '';
	public $RealCharset		= '';
	public $ReplaceParams	= '';

	/**
	 * consutruct()
	 *
	 * @param <array> $mailParams array('Subject' => %subject%, 'FromAddress' => %from%, ...)
	 *                'Subject', 'FromAddress', 'Body', 'AltText', 'ToAddress', 'CcAddress', 'BccAddress', 'Envelope', 'Charset', 'RealCharset', 'ReplaceParams'
	 */
	public function __construct($mailParams = array()) {
		$properties = array('Subject', 'FromAddress', 'BodyPlain', 'BodyHTML', 'ToAddress', 'CcAddress', 'BccAddress', 'Envelope', 'Charset', 'RealCharset', 'ReplaceParams');
		foreach ($properties as $property) {
			if (array_key_exists($property, $mailParams)) {
				$this->$property = $mailParams[$property];
			}
		}

		$settings = aafwApplicationConfig::getInstance();

		//デフォルトセット
		if (!$this->FromAddress && $settings->Mail['Default']['FromAddress'])	$this->FromAddress	= $settings->Mail['Default']['FromAddress'];
		if (!$this->BccAddress && $settings->Mail['Default']['BccAddress'])		$this->BccAddress	= $settings->Mail['Default']['BccAddress'];
		if (!$this->Envelope && $settings->Mail['Default']['Envelope'])			$this->Envelope		= $settings->Mail['Default']['Envelope'];
		if (!$this->Charset && $settings->Mail['Default']['Charset'])			$this->Charset		= $settings->Mail['Default']['Charset'];
		if (!$this->RealCharset && $settings->Mail['Default']['RealCharset'])	$this->RealCharset	= $settings->Mail['Default']['RealCharset'];
	}

	public static function restoreFromQueue($mailQueue){
		if(!$mailQueue instanceof mail_queue) return false;
		$params = array();
		$params['Subject']		= $mailQueue->subject;
		$params['FromAddress']	= $mailQueue->from_address;
		$params['BodyPlain']	= $mailQueue->body_plain;
		$params['BodyHTML']	= $mailQueue->body_html;
		$params['ToAddress']	= $mailQueue->to_address;
		$params['CcAddress']	= $mailQueue->cc_address;
		$params['BccAddress']	= $mailQueue->bcc_address;
		$params['Envelope']	= $mailQueue->envelope;
		$params['Charset']		= $mailQueue->charset;
		$params['RealCharset']	= $mailQueue->real_charset;
		return new self($params);
	}

	public function sendNow($ToAddress = null, $replaceParams = null, $CcAddress = null, $BccAddress = null) {
		if ($ToAddress)		$this->ToAddress	= $ToAddress;
		if ($CcAddress)		$this->CcAddress	= $CcAddress;
		if ($BccAddress)	$this->BccAddress	= $BccAddress;

		if( $this->validate() )	$this->send($replaceParams);
		else					throw new aafwException ("can't send mail");
	}

	public function getReplaceTemplate($params){
		$content  = trim ($this->BodyHTML ? $this->BodyHTML : $this->BodyPlain);
		$subject  = trim ( $this->Subject );
		if ( !$content ) throw new Exception( "can't send mail with no body" );
		if ( $params ){
			$tmpl     = new aafwTemplateTag ( $content,  $params );
			$content  = $tmpl->evalTag();
			$tmpl     = new aafwTemplateTag ( $subject,  $params );
			$subject  = $tmpl->evalTag();
		}
		$content  = str_replace( array ( "\r\n","\r", "\n" ), "\n", $content );
		return array('subject' => $subject, 'content' => $content);
	}

	private function validate() {
		if ( !$this->Charset ) return false;
		if ( !$this->ToAddress ) return false;
		if ( !$this->FromAddress ) return false;
		if ( !$this->BodyPlain && !$this->BodyHTML ) return false;

		return true;
	}

	private function send($replaceParams = null) {
		$mail = new aafwMail($this->Subject,
				($this->BodyHTML ? $this->BodyHTML : $this->BodyPlain),
				($this->BodyHTML ? true : false),
				$this->Charset,
				$this->RealCharset);
		$mail->setFrom($this->FromAddress);
		//if($this->Envelope) $mail->setEnvelope($this->Envelope);
		//if($this->BodyHTML) $mail->setAltText($this->BodyText);

		$mail->send($this->ToAddress, $replaceParams, $this->CcAddress, $this->BccAddress);
	}

	public function sendLater($ToAddress = null, $replaceParams = null, $CcAddress = null, $BccAddress = null, $sendSchedule = null) {
		if ($ToAddress)		$this->ToAddress	= $ToAddress;
		if ($CcAddress)		$this->CcAddress	= $CcAddress;
		if ($BccAddress)	$this->BccAddress	= $BccAddress;

		if(is_array($replaceParams)){
			$tmpl = new aafwTemplateTag($this->BodyPlain, $replaceParams);
			$this->BodyPlain = $tmpl->evalTag();
			$tmpl = new aafwTemplateTag($this->BodyHTML, $replaceParams);
			$this->BodyHTML = $tmpl->evalTag();
			$tmpl = new aafwTemplateTag($this->Subject, $replaceParams);
			$this->Subject = $tmpl->evalTag();
		}

		if( !$this->validate() )	throw new aafwException ("can't send mail");

		$mail_queue = new mail_queue();
		$mail_queue->send_schedule	= $sendSchedule ? $sendSchedule : '1970-01-01 00:00:00';
		$mail_queue->charset		= $this->Charset;
		$mail_queue->real_charset	= $this->RealCharset;
		$mail_queue->to_address		= $this->ToAddress;
		$mail_queue->cc_address		= $this->CcAddress;
		$mail_queue->bcc_address	= $this->BccAddress;
		$mail_queue->subject		= $this->Subject;
		$mail_queue->body_plain		= $this->BodyPlain;
		$mail_queue->body_html		= $this->BodyHTML;
		$mail_queue->from_address	= $this->FromAddress;
		$mail_queue->envelope		= $this->Envelope;
		$mail_queue->save();

	}

	public function loadSubject($template_id){
		$file = AAFW_DIR . '/mail_templates/' . $template_id . '_subject.txt';
		if(is_file($file)){
			$this->Subject = file_get_contents($file);
		}
	}

	public function loadBodyPlain($template_id){
		$file = AAFW_DIR . '/mail_templates/' . $template_id . '_body_plain.txt';
		if(is_file($file)){
			$this->BodyPlain = file_get_contents($file);
		}
	}

	public function loadBodyHTML($template_id){
		$file = AAFW_DIR . '/mail_templates/' . $template_id . '_body_html.txt';
		if(is_file($file)){
			$this->BodyHTML = file_get_contents($file);
		}
	}

	public function loadMailContent($template_id){
		$this->loadSubject($template_id);
		$this->loadBodyPlain($template_id);
		$this->loadBodyHTML($template_id);
	}
}