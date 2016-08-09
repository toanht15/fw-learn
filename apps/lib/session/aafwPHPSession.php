<?php
AAFW::import ( 'jp.aainc.aafw.base.aafwSessionBase' );
AAFW::import ( 'jp.aainc.aafw.web.aafwMobileDispatcher' );
class aafwPHPSession extends aafwSessionBase{

  public function __construct($Config){

    $sessionTime = $Config->SessionTime;
    if ( is_numeric ( $sessionTime ) || preg_match( '#^\d+[YMWDHIS]$#i', $sessionTime )  ){
      $this->setSessionTime($sessionTime);
    }
//    if ( $Config->MobileSessionHandler ) {
//      $def = aafwMobileDispatcher::isMobile( $_SERVER ) ;
//      if     ( $def['is_mobile'] )               require dirname( __FILE__ ) . '/session_handler/' . $Config->MobileSessionHandler  . '.php';
//      elseif ( $Config->DefaultSessionHandler )  require dirname( __FILE__ ) . '/session_handler/' . $Config->DefaultSessionHandler . '.php';
//    } elseif ( $Config->DefaultSessionHandler ) {
//      require dirname( __FILE__ ) . '/session_handler/' . $Config->DefaultSessionHandler . '.php';
//    }
    $this->start();
  }

  public function start(){
    $def = aafwMobileDispatcher::isMobile( $_SERVER ) ;
    if ( $def['is_mobile'] ) {
      if( trim ( $_COOKIE['PHPSESSID'] ) && $def['type'] != 'kddi' ){
        session_id($_COOKIE['PHPSESSID']);
      } elseif ( trim ( $_GET['snid'] ) ) {
        session_id( $_GET['snid']);
      } elseif ( trim ( $_POST['snid'] ) ) {
        session_id( $_POST['snid']);
      } else{
        $_COOKIE=array();
        session_start();
        $_GET['snid'] = session_id();
      }
    }
    session_start();
  }

  public function setSessionTime($sessionTime){
    if( preg_match( '#^\d+[YMWDHIS]$#i', $sessionTime ) ){
      ini_set( "session.gc_maxlifetime", $this->convertSecond( $sessionTime ) );
      session_set_cookie_params( $this->convertSecond( $sessionTime ) );
    }
    elseif ( is_numeric ( $sessionTime ) ) {
      ini_set( "session.gc_maxlifetime", $sessionTime );
      session_set_cookie_params( $sessionTime  );
    }
  }

  public function __set( $key, $value ){
    $_SESSION[$key] = $value;
  }

  public function __get( $key ){
    return $_SESSION[$key];
  }
  public function clear(){
    $_SESSION = array();
  }

  public function getValues(){
    return $_SESSION;
  }

  public function setValues( $val ){
    $_SESSION = $val;
  }
}
