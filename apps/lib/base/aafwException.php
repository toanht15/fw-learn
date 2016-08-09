<?php
/**
 * aafwException
 * @author A.Takahashi
 */

class aafwException extends Exception{

	const ERR_LEVEL_WARNG	= 1;
	const ERR_LEVEL_ALERT	= 2;
	const ERR_LEVEL_CRITL	= 3;

	private static $err_level_name_master = array(
		self::ERR_LEVEL_WARNG => 'WARNG',
		self::ERR_LEVEL_ALERT => 'ALERT',
		self::ERR_LEVEL_CRITL => 'CRITL',
	);

	protected $err_title;
	protected $err_type;
	protected $err_code;
	protected $err_message;
	protected $err_level;
  protected $InnerException = null;

	/**
	 * コンストラクタ
	 * @param mixed   $err_message エラーメッセージ もしくは Exceptionオブジェクト
	 * @param string  $err_code   エラーコード
	 * @param string  $err_type   エラータイプ
	 * @param string  $err_title  エラータイトル
	 * @param integer $err_level  エラーレベル
	 */
	public function  __construct($err_message = null, $err_code = null, $err_type = null, $err_title = null, $err_level = self::ERR_LEVEL_CRITL) {

		//旧仕様に対応
		if($err_message instanceof Exception){
			$err_code		= $err_message->getMessage();
			$err_message	= null;
			$this->InnerException = $err_message;
		}
		if ( is_array ( $err_message ) ) {
      $this->message = $this->err_message = $err_message['message'];
			$this->err_code 	 = $err_message['code'];
			$this->err_title 	 = $err_message['title'];
			$this->err_type 	 = $err_message['type'];
			$this->err_level 	 = $err_message['level'];
		} else {
			$this->message = $this->err_message = $err_message;
			$this->err_code    = $err_code;
			$this->err_title   = $err_title;
			$this->err_type  	 = $err_type;
			$this->err_level 	 = $err_level;
		}
	}

	public function getErrorCode(){
		return $this->err_code;
	}

	/**
	 * エラータイトルを返す
	 *  $this->err_titleが設定されている場合はそれを
	 *  そうでない場合はaafwErrorMessage->getErrorTitle($this->err_type)を返す
	 */
	public function getErrorTitle(){
		if($this->err_title){
			return $this->err_title;
		}else{
			require_once 'aafwErrorMessages.php';
			return aafwErrorMessages::getInstance()->getErrorTitle($this->err_type);
		}
	}

	/**
	 * エラーメッセージを返す
	 *  aafwErrorMessage->getErrorMessage($this->err_code) . $this->err_message
	 *  (コードで取得したメッセージ ＋ $this->err_message)
	 */
	public function getErrorMessage(){
		if($this->err_code){
			require_once 'aafwErrorMessages.php';
			if   ( aafwErrorMessages::getInstance()->isExistsMessage( $this->err_code ) ) $message = aafwErrorMessages::getInstance()->getErrorMessage($this->err_code);
      else {
        $message = $this->err_code;
        if ( $this->InnerException ) $message .= $this->InnerException->getTraceAsString();
      }
		}
		return $message . ($message && $this->err_message ? "\n" : null) . $this->err_message;
	}

	/**
	 * ログ出力する
	 */
	public function log(){
		require_once 'aafwApplicationConfig.php';
		$settings = aafwApplicationConfig::getInstance()->getValues();
		if($path = $settings['Log']['ErrorLogPath']){
			require_once 'aafwLogger.php';
			$logger = aafwLogger::getInstance($path);
			if($this->err_level >= self::ERR_LEVEL_CRITL){
				$logger->append(self::$err_level_name_master[$this->err_level], $this->getErrorMessage(), $this->getTraceAsString());
			}else{
				$logger->append(self::$err_level_name_master[$this->err_level], $this->getErrorMessage());
			}
			$logger->save();
		}
	}
}
