<?php
class AAFW {
  public static $AAFW_ROOT  = '';

  /**
   * フレームワークスタート
   */
  public static function start ( $config = null ) {
    if ( !self::$AAFW_ROOT ) self::$AAFW_ROOT = realpath ( dirname( __FILE__ ) . '/..' );
    if     ( $config  ) require_once $config;
    elseif ( is_file ( self::$AAFW_ROOT . '/config/define.php' ) ) require_once self::$AAFW_ROOT . '/config/define.php';
  }

  /**
   * クラスをとりこむ
   */
  public static function import ( $class_path ) {
    if( !AAFW::$AAFW_ROOT ) AAFW::$AAFW_ROOT  = dirname( __FILE__ );
    $target = self::getTargets( $class_path );
    $result = array();
    foreach( $target as $class ){
      if( preg_match( '#\.php$#', $class ) ){
        if( !is_file( $class ) ) throw new Exception( 'not found' . $class );
        $class_name = preg_replace( '#\.php$#', '', basename( $class ) );
        if ( !class_exists ( $class_name, false ) && !interface_exists ( $class_name, false ) ) require $class;
        $result[] = $class_name;
      } else {
        $result[] = $class;
     }
    }
    return $result;
  }

  /**
   *
   */
  public static function config ( $q ) {
    $conf = aafwApplicationConfig::getInstance();
    return $conf->query ( $q );
  }

  /***********************
   * aafwのパス表記をファイルシステムのフルパスで返す
   * @param aafwのパス表記
   * @return ファイルシステムでのフルパス(配列)
   ***********************/
  public static function getTargets( $path ){
    $target = array();
    $path = self::toFileSystemPath( $path );
    if( preg_match( '#/\*$#', $path ) ){
      $dir =  preg_replace( '#\*$#', '', $path );
      if( is_dir( $dir ) ){
        $d = opendir( $dir );
        while( $fn = readdir( $d ) ){
          if( !preg_match( '#\.(?:php|sql)$#', $fn ) ) continue;
          $target[] = realpath ( $dir . '/' .  $fn );
        }
      }
    } else {
      $target[] = realpath ( $path . '.php' );
    }
    return  $target;
  }

  /**
   * AAFWのパスからファイルシステムのパスに変換する
   * @param AAFW表記のパス
   * @return ファイルシステムのパス
   */
  public static function toFileSystemPath ( $class_path ) {
    $path = '';
    if     ( preg_match ( '#^jp.aainc.aafw\.#', $class_path ) ) $path = self::$AAFW_ROOT . '/lib/' . str_replace ( '.', '/', preg_replace ( '#^jp\.aainc\.aafw\.#', '', $class_path ) );
    elseif ( preg_match ( '#^jp.aainc.#'      , $class_path ) ) $path = self::$AAFW_ROOT . '/'     . str_replace ( '.', '/', preg_replace ( '#^jp\.aainc#',         '', $class_path ) );
    return  $path;
  }

  /*******************************
   * ダンプする
   * @param オブジェクトまたは配列
   * @return <ul><li>の形の文字列</li></ul>
   *******************************/
  public static function dump( $var ){
    if( DEBUG )                    return ;
    if( php_sapi_name() == 'cli' ) say( self::buildTree( $var ) );
    else                           say( self::dumpForWeb( $var ) );
  }

  /*******************************
   * Web向けにダンプ文字列
   * @param オブジェクトまたは配列
   * @return ダンプ文字列
   *******************************/
  public static function dumpForWeb( $var ){
    $var = self::toArray( $var );
    $ret = '<ul>' . "\n";
    $ret .= '<li><input type="button" onclick="var p=this.parentNode;var c=p.parentNode.getElementsByTagName(\'li\');for(var i=0,l=c.length;i<l;i++)if(c[i]!=p) c[i].style.display=c[i].style.display ? \'\':\'none\';" value="toggle" /></li>';
    foreach( $var as $key => $value ) $ret .= '<li>[' . htmlspecialchars( $key, ENT_QUOTES ). '] =&gt; ' . ( is_scalar( $value ) ?  htmlspecialchars( $value ): self::dumpForWeb( $value ) ) . '</li>' . "\n";
    $ret .=  '</ul>' . "\n";
    return $ret;
  }

  /*******************************
   * 起点からのファイルシステムのツリー構造を取得する
   * @param 起点
   * @return 再帰的な配列
   *******************************/
  public static function getFiles( $path ){
    if( !is_dir( $path ) ) return '';
    $path = preg_replace( '#/$#', '', $path  );
    $d = opendir( $path );
    $ret = array();
    while( $fn = readdir( $d ) ){
      if( preg_match( '#^\.+$#', $fn ) ) continue;
      if ( is_dir ( $path . '/' . $fn ) && preg_match ( '#^.svn#', $fn ) ) continue;
      if    ( is_file( $path . '/' . $fn ) && preg_match( '#php$#', $fn ) ) $ret[]    = preg_replace( '#\.php#', '',  $fn );
      elseif( is_dir( $path . '/' . $fn ) )                                 $ret[$fn] = self::getFiles( $path . '/' . $fn );
    }
    return $ret;
  }

  /*******************************
   * 再帰的名配列をツリーの階層テキストに変換する
   * @param 配列
   * @return 文字列
   *******************************/
  public static function buildTree( $arr, $level = 0 ){
    $ret = '';
    $arr = self::toArray( $arr );
    foreach( $arr as $key => $value ){
      if( is_numeric( $key) ) $key = '- ';
      else                    $key = "$key: ";
      if( is_scalar( $value ) ){
        for( $i = 0; $i < $level; $i++ ) $ret .= ' ';
        if( preg_match( '#\n#', $value ) )  $value = "|\n" . $value;
        else                                $value = '"' . str_replace( '"', '\\"', $value ) . '"';
        $ret .= "$key" . $value  . "\n";
      } else{
        for( $i = 0; $i < $level; $i++ ) $ret .= ' ';
        $ret .= "$key\n";
        $ret .= self::buildTree( $value, $level + 2 );
      }
    }
    return $ret;
  }

  /*******************************
   * fwwObjectを配列化()
   * @param オブジェクト
   * @return 配列
   *******************************/
  public function toArray( $param ){
    foreach( $param as $key => $value ){
      if    ( is_array( $value ) )                                       $param[$key] = self::toArray( $value );
      elseif( is_scalar( $value ) )                                      $param[$key] = $value;
      elseif( is_object( $value ) && get_class( $value ) == 'stdClass' ) $param[$key] = (array)$value;
      else                                                               $param[$key] = 'object';
    }
    return $param;
  }

}

//
// コマンドラインから直接呼ばれている場合にはコマンドラインツールを起動
//
if( php_sapi_name() == 'cli' && basename( __FILE__ ) == basename( $_SERVER['SCRIPT_NAME'] ) ) {
  error_reporting ( E_ALL - E_NOTICE );
  AAFW::start();
  if ( $argv[1] == 'bat' ||  $argv[1] == 'batch' ) {

	if (extension_loaded('newrelic')) {
		$config = aafwApplicationConfig::getInstance();
		newrelic_set_appname($config->ConsoleApplicationName);
	}

    AAFW::import( 'jp.aainc.aafw.cli.aafwCLIController' );
    array_shift( $argv );
    array_shift( $argv );
    $controller = new aafwCLIController();
    $controller->run( $argv );
  } else {
    AAFW::import( 'jp.aainc.aafw.tools.aafwCommandLineTool' );
    aafwCommandLineTool::doService( $argv );
  }
}

