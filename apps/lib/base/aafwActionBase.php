<?php
/*********************************
 * MVCのCのCの中身
 * @author t.ishida
 * @cre    2008/02/24
 **********************************/
require_once 'aafwValidatorBase.php';
require_once 'aafwValidator.php';
require_once 'aafwApplicationConfig.php';

abstract class aafwActionBase extends aafwValidatorBase {
  protected $POST    = null;
  protected $GET     = null;
  protected $SESSION = null;
  protected $COOKIE  = null;
  protected $FILES   = null;
  protected $SERVER  = null;
  protected $REQUEST = null;
  protected $ENV     = null;

  protected $ErrorPage    = 'error_page.php';
  protected $Settings     = '';
  protected $AppConfig    = '';
  protected $WebSettings  = array();

  protected $_Plugins = array(
    'First'         => array(),
    'BeforeService' => array(),
    'Last'          => array(),
    );

  protected $PackageName   = '';
  protected $Data          = array();
  protected $Validator     = '';
  protected $AllowContent  = array(  'HTML', 'PHP' );
  protected $_Site          = '';
  protected $_ModelDefinitions = array ();
  protected $_Models           = array ();
  protected $_ServiceFactory   = null;

  /*************************
   * コンストラクタ
   *************************/
  public function __construct(
    $p = array(),
    $g = array(),
    $s = array(),
    $c = array(),
    $f = array(),
    $e = array(),
    $sv = array(),
    $r  = array() ,
    $site = '',
    $pkg = '',
    $web_settings = array()
    ){
    if ( $pkg )          $this->PackageName = $pkg;
    if ( $site )         $this->_Site       = $pkg;
    if ( $web_settings ) $this->WebSettings = $web_settings;
    $this->rewriteParams( $p, $g, $s, $c, $f, $e, $sv, $r );
    $this->AppConfig = aafwApplicationConfig::getInstance();
    $this->Settings  = aafwApplicationConfig::getInstance()->getValues();
    $this->loadPlugins ();
    $this->loadValidator ();
  }

  /**
   * プラグインを空にして実行にする
   */
  public function disablePlugins () {
    $this->_Plugins = array (
      'First'         => array(),
      'BeforeService' => array(),
      'Last'          => array(),
    );
  }

  /**
   * モデルの設定を返す
   * @return モデルの設定
   */
  public function getModelDefinitions () {
    return $this->_ModelDefinitions;
  }

  /**
   * モデルを設定する
   * @param 設定するモデル
   */
  public function setModel ( $obj ) {
    if ( !is_object ( $obj ) ) throw new aafwException ( '無効なモデルが設定されようとしました' );
    $flg = false;
    $name = '';
    foreach ( $this->getModelDefinitions () as $class  ) {
      if ( $obj instanceof $class ) {
        $name = $class;
        $flg = true;
        break;
      }
    }
    if ( !$flg ) throw new aafwException ( '無効なモデルが設定されようとしました' );
    $this->_Models[$name] = $obj;
  }

  /**
   * モデルを返す
   * @param モデルの名前
   * @return モデル
   */
  public function getModel ( $name ) {
    if ( !$name )                 throw new aafwException ( 'モデル名が不正です' );
    if ( !$this->_Models[$name] ) throw new aafwException ( '定義されていないモデルにアクセスしようとしました' );
    return $this->_Models[$name];
  }

  /*****************************
   * デフォルトのセッタ
   *****************************/
  public function __set( $key, $value ){
    $this->REQUEST[$key] = $value;
  }

  /*****************************
   * デフォルトのゲッタ
   *****************************/
  public function __get( $key ){
    return $this->REQUEST[$key];
  }

  /*****************************
   * 許可するContent-Typeを返す
   *****************************/
  public function getAllowContent() {
    return is_array( $this->AllowContent ) ? $this->AllowContent : array( $this->AllowContent );
  }

  /*************************
   * データを返す
   *************************/
  public function getData(){
    return $this->Data;
  }

  /*************************
   * データを返す
   *************************/
  public function setData( $value ){
    $this->Data = $value;
  }

  /*************************
   * リクエストを返す
   *************************/
  public function getRequest ( $key = null ) {
    if ( !$key ) return $this->REQUEST;
    else         return $this->REQUEST[$key];
  }

  /*************************
   * 片っ端から全部取得
   *************************/
  public function getParams(  ){
    return array(
      $this->POST,
      $this->GET,
      $this->SESSION,
      $this->COOKIE,
      $this->FILES,
      $this->ENV,
      $this->SERVER,
      $this->REQUEST
      );
  }

  /*************************
   * サービスを記述します。
   * {
   *    $this->Data = array( [ビューに渡すデータ構造] );
   *    return 'ビューの名前';  //ビューが必要無ければ書かなくてもよい
   * }
   *************************/
  public function getSession ( $key = null ) {
    if ( !$key ) return $this->SESSION;
    else         return $this->SESSION[$key];
  }

  public function setSession ( $key, $val ) {
    $this->SESSION[$key] = $val;
  }

  public function getSettings(){
    return $this->Settings;
  }

  public function setAppConfig ( $obj ) {
    $this->AppConfig = $obj;
  }


  ///
  /// バリデータのロード
  ///
  public function loadValidator () {
    if( $this->Validator && is_dir( $dir = preg_replace( '#/$#', '', AAFW_DIR ) . '/plugins/validator' ) ) {
      $class = $this->Validator;
      if( !is_dir( $dir ) ) throw new Exception( 'バリデータのディレクトリが存在しません:' . $dir );
      require_once $dir . '/' . $class . '.php';
      $v = new $class();
      $this->Validator = $v;
    }
  }

  ///
  /// プラグインのロード
  ///
  public function loadPlugins () {
    if ( is_dir( $dir = ( preg_replace( '#/$#', '', AAFW_DIR ) . '/plugins/action' ) ) ) {
      $plugins = opendir( $dir );
      while( $plugin = readdir( $plugins ) ){
        if( !preg_match( '#\.php$#', $plugin ) ) continue;
        if( is_file(  $dir . '/' . $plugin ) ){
          require_once $dir . '/' . $plugin;
          $class = str_replace( '.php', '', $plugin );
          $c =  new $class( $this );
          if ( !$c->canRun () ) continue;
          $this->_Plugins[$c->getHookPoint()][] = $c;
        }
      }
      foreach ( $this->_Plugins  as $key => $value ) {
        usort ( $value, create_function ( '$x, $y', 'return $x->getPriority() == $y->getPriority() ? 0 : ($x->getPriority() < $y->getPriority() ? -1 : 1);' ) );
        $this->_Plugins[$key] = $value;
      }
    }
  }

  public function getSite () {
    return $this->_Site;
  }

  /*************************
   * サービスを記述します。
   * {
   *    //セッションに何か保存したければ
   *    $this->SESSION['foo'] = 'bar';
   *    $this->Data = array( [ビューに渡すデータ構造] );
   *    return 'ビューの名前';  //ビューが必要無ければ書かなくても おけ
   * }
   *************************/
  abstract function doService( );


  /*************************
   * パラメータの上書き
   *************************/
  public function rewriteParams(
    $p = array(),
    $g = array(),
    $s = array(),
    $c = array(),
    $f = array(),
    $e = array(),
    $sv = array(),
    $r  = array() ){

    list(
      $this->POST,
      $this->GET,
      $this->SESSION,
      $this->COOKIE,
      $this->FILES,
      $this->ENV,
      $this->SERVER,
      $this->REQUEST
      ) = array( $p, $g, $s, $c, $f, $e, $sv, $r );
  }

  /***********************************
   * Actionの実施
   ***********************************/
  public function run( ){
    $methods = get_class_methods( $this );
    ///
    /// まずはじめに呼ばれるメソッド
    ///
    if( in_array( 'doThisFirst', $methods ) ){
      $ret = $this->doThisFirst();
      if( $this->canStop ( $ret ) ) {
        $this->doPlugin ( 'Finally' );
        return $ret;
      }
    }

    ///
    /// プラグイン( First )
    ///
    if( count( $this->_Plugins['First'] ) ){
      foreach( $this->_Plugins['First'] as $c ){
        $ret = $c->doService();
        if( $this->canStop( $ret ) ) {
          $this->doPlugin ( 'Finally' );
          return $ret;
        }
      }
    }

    ///
    /// validation前に呼ばれるメソッド
    ///
    if( in_array( 'beforeValidate', $methods ) ){
      $ret = $this->beforeValidate();
      if( $this->canStop ( $ret ) ) {
        $this->doPlugin ( 'Finally' );
        return $ret;
      }
    }

    ///
    /// バリデーション
    ///
    if( $this->Validator ){
      $this->Validator->setParams(
        $this->POST,
        $this->GET,
        $this->SESSION,
        $this->COOKIE,
        $this->FILES,
        $this->ENV,
        $this->SERVER,
        $this->REQUEST ,
        $this->Settings
      );
      $ret = $this->Validator->validate();
      if ( $this->canStop ( $ret ) ) {
        $this->doPlugin ( 'Finally' );
        return  $ret;
      }
      if ( !$ret ) {
        $this->Data = $this->Validator->getData();
        $this->doPlugin ( 'Finally' );
        return $this->ErrorPage;
      }
    } else {
      if ( in_array( 'validate', $methods ) ) {
        $ret = $this->validate();
        if ( $this->canStop ( $ret ) ) {
          $this->doPlugin ( 'Finally' );
          return $ret;
        }
        if ( !$ret ) {
          $this->doPlugin ( 'Finally' );
          return $this->ErrorPage;
        }
      } 
      elseif ( $this->ValidatorDefinition ) {
        $validator = new aafwValidator( $this->ValidatorDefinition );
        if ( !$validator->validate ( $this->REQUEST ) ){
          $this->Data['validator'] = $validator;
          $this->doPlugin ( 'Finally' );
          return $this->ErrorPage;
        }
      } else {
        throw new Exception( get_class( $this ) . 'にvalidateメソッドを実装して下さい。' );
      }
    }

    ///
    /// validation後に呼ばれるメソッド
    ///
    if ( in_array( 'afterValidate'  , $methods ) ) {
      $ret = $this->afterValidate();
      if ( $this->canStop ( $ret  ) ) {
        $this->doPlugin ( 'Finally' );
        return $ret;
      }
    }

    ///
    /// プラグイン( BeforeService )
    ///
    if ( count( $this->_Plugins['BeforeService'] ) ){
      foreach( $this->_Plugins['BeforeService'] as $c ){
        $ret = $c->doService();
        if ( $this->canStop ( $ret  ) ) {
          $this->doPlugin ( 'Finally' );
          return $ret;
        }
      }
    }

    ///
    /// 主処理前に呼ばれるメソッド
    ///
    if ( in_array( 'beforeDoService', $methods ) ) {
      $ret = $this->beforeDoService();
      if ( $this->canStop ( $ret  ) ) {
        $this->doPlugin ( 'Finally' );
        return $ret;
      }
    }

    ///
    /// 主処理
    ///
    $action_ret = $this->doService();

    ///
    /// 主処理後に呼ばれるメソッド
    ///
    if( in_array( 'afterDoService', $methods ) ) {
      $ret = $this->afterDoService();
      if ( $this->canStop ( $ret  ) ) {
        $this->doPlugin ( 'Finally' );
        return $ret;
      }
    }

    ///
    /// プラグイン( Last )
    ///
    if( count( $this->_Plugins['Last'] ) ){
      foreach( $this->_Plugins['Last'] as $c ){
        $ret = $c->doService();
        if ( $this->canStop ( $ret  ) ) {
          $this->doPlugin ( 'Finally' );
          return $ret;
        }
      }
    }

    ///
    /// プラグイン( Finally )
    ///
    if( count( $this->_Plugins['Finally'] ) ){
      foreach( $this->_Plugins['Finally'] as $c ){
        $ret = $c->doService();
        if ( $this->canStop ( $ret  ) ) {
          $this->doPlugin ( 'Finally' );
          return $ret;
        }
      }
    }

    ///
    /// 本当に最後に呼ばれるメソッド
    ///
    if( in_array( 'doThisLast', $methods ) ) {
      $ret = $this->doThisLast();
      if ( $this->canStop ( $ret  ) ) {
        $this->doPlugin ( 'Finally' );
        return $ret;
      }
    }

    return $action_ret;
  }

  public function assign (  ) {
    if ( !func_num_args() ) throw new aafwException ( '引数がありません' );
    if     ( func_num_args() == 1 ) $this->Data = func_get_arg(0);
    elseif ( func_num_args() == 2 ) $this->Data[func_get_arg(0)] = func_get_arg(1);
    else                            throw new aafwException ( '引数の数が不正です' );
  }

  public function refference ( $key ) {
    return $this->Data[$key];
  }

  public function setServiceFactory ( $obj ) {
    $this->_ServiceFactory = $obj;
  }

  public function createService ( $name, $params = null ) {
    if ( !$this->_ServiceFactory )
      throw new aafwException ( 'ServiceFactoryがセットされていません' );
    // return $this->_ServiceFactory->create ( $name, $params );
    $obj = $this->_ServiceFactory->create ( $name, $params );
    return $obj;
  }
  
  public function getSessionID ( ) {
    return session_id() . '';
  }

  public function setServer ( $key, $val ) {
    $this->SERVER[$key] = $val;
  }

  public function getServer ( $key ) {
    return $this->SERVER[$key];
  }

  public function doPlugin ( $phase ) {
    if( count( $this->_Plugins[$phase] ) ) {
      foreach( $this->_Plugins[$phase] as $c ) {
        $c->doService();
      }
    }
  }

  public function canStop ( $ret )  {
    return preg_match ( '#(?:redirect|404|not found|403|forbidden)#', $ret );
  }


  public function createAjaxResponse($result, $data, $errors=array(), $html="") {
 	$json_data = array();
	$json_data["result"] = $result;
	$json_data["data"] = $data;
	$json_data["errors"] = $errors;
	$json_data["html"] = $html;
	return $json_data;
  }
}
