<?php
require_once 'base/aafwParserBase.php';
/***************************
 * PHPParserっていうか外部PHPをテンプレートエンジンとして使うだけの不便なやつ
 * それと、その場で思いついた適当なHTML生成ヘルパが付いている
 *
 * @todo: ヘルパ部分のメソッド名微妙じゃね(主にDeeplyってのが要らないよね)
 ****************************/
class PHPParser extends aafwParserBase {
  protected $values      = array();
  protected $params      = array();
  private $Logger        = null;
  private $Methods       = array();
  private $Matches = array();

  public function __construct( $settings = array() ){
    $plugin_dir = $settings['plugin_dir'];
    !$plugin_dir && $plugin_dir = dirname( __FILE__ ) . '/helpers';
    if( is_dir( $plugin_dir ) ){
      $d = opendir( $plugin_dir );
      while( $fn = readdir( $d ) ){
        if( !preg_match( '#^([^\.]+)\.php$#', $fn, $tmp ) ) continue;
        $class = $tmp[1];
        require_once $plugin_dir . '/' . $fn;
        $this->Methods[$class] = new $class();
      }
    }
  }

  /***************************
   * セッタ(笑)
   ****************************/
  public function __set( $key, $value ){
    $this->values[$key] = $value;
  }
  /***************************
   * ゲッタ(笑)
   ****************************/
  public function __get( $key ){
    return $this->values[$key];
  }

  public function __call( $name, $args ){
    if( $this->Methods[$name] ) return $this->Methods[$name]->doMethod( $args );
    if( $this->values[$name] ){
      if( preg_match( '#^\$([0-9]+),\$([0-9]+)$#', $args[0], $tmp ) ) return $this->Matches[$name][$tmp[1]][$tmp[2]];
      if( preg_match( '#^\$([1-9])$#', $args[0], $tmp ) )             return $this->Matches[$name][$tmp[1]];
      if( preg_match( '#^[^a-zA-Z0-9]#', $args[0], $tmp ) ){
        $tmp[1] = str_replace( '#', '\#', $tmp[1] );
        if( preg_match( '#' . $tmp[1] .'([a-z]*)$#', $args[0], $switches ) ){
          if( preg_match( '#m#', $switches[1] ) ){
            $args[0] = preg_replace( '#' . $tmp[1] . $switches[1] . '$#', str_replace( '\\', '', $tmp[1] ) . str_replace( 'm', '', $switches[1] ), $args[0] );
            return preg_match_all( $args[0], $this->values[$name], $this->Matches[$name] );
          }
          if( count( $args ) == 1 ) return preg_match( $args[0], $this->values[$name], $this->Matches[$name] );
          else                      return preg_replace( $args[0], $args[1], $this->values[$name] );
        }
      }
      $buf = $this->values[$name];
      if( is_array( $args[0] ) ){
        foreach( $args as $arg ){
          if( $arg[0] == 'esc'  )             $buf = $this->escapeDeeply( $buf );
          if( $arg[0] == 'fmtD' && $arg[1] )  $buf = $this->formatDate( $buf, $arg[1] );
          if( $arg[0] == 'fmtC' )             $buf = $this->formatCurrency( $buf );
          if( $arg[0] == 'half')              $buf = $this->toHalfContentDeeply( $buf );
          if( $arg[0] == 'cut' && preg_match( '#^\d+$#', $arg[1] ) ) $buf = $this->cutLongText( $buf, $arg[1] );
        }
      } else {
        if( $args[0] == 'esc'  )             $buf = $this->escapeDeeply( $buf );
        if( $args[0] == 'fmtD' && $args[1] ) $buf = $this->formatDate( $buf, $args[1] );
        if( $args[0] == 'fmtC' )             $buf = $this->formatCurrency( $buf );
        if( $args[0] == 'half')              $buf = $this->toHalfContentDeeply( $buf );
        if( $args[0] == 'cut' && preg_match( '#^\d+$#', $args[1] ) ) $buf = $this->cutLongText( $buf, $args[1] );
      }
      return $buf;
    }
  }


  /**
   * 使いようが無いtrue返すだけ
   */
  public function in($data) {
    if(preg_match( '/^http:\/\/.+?\.php\??.*/' ,$data ) )
      return file_get_contents($data);
    else
      return true;
  }

  /**
   * 普通にout
   * @param $data['__view__'] と __view__が使うべきハッシュ
   */
  public function out( $data ) {
    if( !is_array( $data ) ) throw new Exception('PHPParser.out : 引数は配列で');
    $view = $data['__view__'];
    $params = $data['__REQ__'];
    unset( $data['__view__'] );
    unset( $data['__REQ__'] );
    $this->values = $data;
    $this->params = $params;
    ob_start();
    include $view;
    return ob_get_clean();
  }


  /**
   * ハッシュからQueryStringっぽいものにする
   * @param $src ハッシュ
   * @return QueryString
   */
  public function toHidden( $src ) {
    $ret = array();
    foreach( $src as $key => $value ) {
      if(  $value == '' ) continue;
      if( $key === 'PHPSESSID' ) continue;
      if( $key === 'DSN' ) continue;
      $key = htmlspecialchars( $key, ENT_QUOTES );
      if( is_array( $value ) ) {
        foreach( $value as $elm ) {
          if(  $elm == '' ) continue;
          $elm = htmlspecialchars( $elm, ENT_QUOTES );
          $ret[] = "<input type='hidden' name='${key}[]' value='$elm' />";
        }
      } else {
        $value = htmlspecialchars( $value, ENT_QUOTES );
        $ret[] = "<input name='$key' type='hidden' value='$value' />";
      }
    }
    return join( "\n" ,$ret );
  }


  /**
   * 配列を再帰的に掘っていって片っ端からHTMLのエスケープ
   * @param 配列
   * @return 実行後の配列
   */
  public function escapeDeeply( $data ){
    return $this->deep( $data, '$x', 'return is_object( $x ) ? $x : htmlspecialchars( $x, ENT_QUOTES );' );
  }

  /**
   * 配列を再帰的に掘っていって片っ端からHTMLのデコード
   * @param 配列
   * @return 実行後の配列
   */
  public function unescapeDeeply( $data ){
    return $this->deep( $data, '$x', 'return is_object( $x ) ? $x : htmlspecialchars_decode( $x, ENT_QUOTES );' );
  }

  /**
   * PHPを普通にパースする
   * @param テンプレートのパス
   * @param データ
   * @return パースした結果
   */
  public function parseTemplate( $tmpl, $data = null ){
    ob_start();
    include( AAFW_DIR . '/views/' .  $tmpl );
    return ob_get_clean();
  }

  /**
   * PHPを普通にパースする（モバイル）
   * @param テンプレートのパス
   * @param データ
   * @return パースした結果
   */
  public function parseTemplateM( $tmpl, $data = null ){
    ob_start();
    include( AAFW_DIR . '/m_views/' .  $tmpl );
    return ob_get_clean();
  }

  /**
   * PHPを普通にパースする
   * @param テンプレートのパス
   * @param データ
   * @return パースした結果
   */
  public function parseTemplateX ( $tmpl, $data = null ){
    $def = aafwMobileDispatcher::isMobile( $_SERVER );
    $config = new aafwConfig ();
    $path = '';
    if     ( $def['is_smart']  && is_file ( $config->SmartTemplatePath  . '/' . $tmpl ) ) return $this->parseTemplateS ( $tmpl, $data );
    elseif ( $def['is_mobile'] && is_file ( $config->MobileTemplatePath . '/' . $tmpl ) ) return $this->parseTemplateM ( $tmpl, $data );
    else                                                                                  return $this->parseTemplate ( $tmpl, $data );
  }

  /**
   * PHPを普通にパースする（スマート）
   * @param テンプレートのパス
   * @param データ
   * @return パースした結果
   */
  public function parseTemplateS( $tmpl, $data = null ){
    ob_start();
    $config = new aafwConfig ();
    include( $config->SmartTemplatePath . '/' . $tmpl );
    return ob_get_clean();
  }

  /**
   * 配列を再帰的に遡ってtoHalfContentを実行する
   * @param 配列
   * @return 実行後の配列
   */
  public function toHalfContentDeeply( $data ){
    if( is_array( $data ) ){
      foreach( array_keys( $data ) as $i ){
        $data[$i] = $this->toHalfContentDeeply( $data[$i] );
      }
      return $data;
    } else {
      $data = $this->toHalfContent( $data );
      if( is_array( $data ) ) return $this->toHalfContentDeeply( $data);
      else                    return $data;
    }
    return $this->toHalfContentDeeply( $data );
  }

  /**
   * 改行をBRタグに、URLっぽい文字列をリンクに変換
   * @param 変換する文字列
   * @return 変換後の文字列
   */
  public function toHalfContent( $x ){
    if( is_object( $x ) ) return $x;
    $x = html_entity_decode( $x );
    $x = str_replace( "\n", "#br#", $x );
    $urls = array();
    if( preg_match_all( '#(https?://[0-9a-zA-Z-_\.@/\?&=~\%#]+)#', $x, $tmp ) ){
      $i = 0;
      uasort ( $tmp[1] , create_function (
        '$a,$b',
        'return strlen( $a ) == strlen( $b ) ? 0 : ( $a > $b ? -1 : 1 );'
        ));
      foreach( $tmp[1] as $url ){
        if( preg_match( '#[\'"]' . $url . '[\'"]#', $x ) ) continue;
        $x =  str_replace(
          $url,
          '#url_' . $i .'#',
          $x
          );
        $urls['#url_' . $i .'#'] = $this->getDomainName( $_SERVER['SERVER_NAME'] ) == $this->getDomainName( $url ) ? "<a href=\"$url\">$url</a>" : "<a href=\"$url\" target=\"_blank\">$url</a>";
        $i++;
      }
    }
    $x = htmlspecialchars( $x, ENT_QUOTES );
    foreach( $urls as $key => $value )  $x = str_replace( $key, $value, $x );
    $x = str_replace( "#br#", "<br />", $x );

    return $x;
  }

  /**
   * 拡張子を抽象的にしてファイルを取得する
   * @param ディレクトリパス
   * @param 拡張子を除いたファイル名
   * @param 優先度を決めた配列、小文字、大文字の順で見ていく
   * @return 見付かったファイル名
   */
  public function getSomeFile( $path, $fn ){
    $order = array( 'jpg', 'jpeg', 'gif', 'png' );
    clearstatcache();
    if( !( $x = @opendir( $path ) ) ){
      return '';
    }
    if( !$fn ) return '';
    $path = preg_replace( '#/$#'  , '', $path );
    $fn   = preg_replace( '#\.$#', '', $fn   );
    $dir  = opendir( $path );
    while( $elm = readdir( $dir ) ){
      if( preg_match( '#^\.+$#', $elm ) ) continue;
      foreach( $order as $ext ){
        if( $elm == ( "$fn." . strtolower( $ext ) ) ) return "$path/$fn." . strtolower( $ext );
        if( $elm == ( "$fn." . strtoupper( $ext ) ) ) return "$path/$fn." . strtoupper( $ext );
      }
    }
    return '';
  }

  /**
   * あったら対象のファイルを返す、無ければno_imageを返す
   * @param ファイルのパス
   * @param no_imageのパス
   */
  public function nvlFile( $file, $no_image, $ret_as_root = true ){
    clearstatcache();
    if( is_file( $file ) ) return $ret_as_root ? ( '/' . $file )     : $file;
    else                   return $ret_as_root ? ( '/' . $no_image ) : $no_image;
  }


  /**
   * input type="text"を作る
   * @param フォームの名前
   * @param 既定値 - ActionFormならActionFormで
   * @param 追加アトリビュート
   * @return できあがったHTML文字列
   */
  public function formText( $name, $value = '' ,$attr = array() ){
    if ( $value == 'ActionForm' ) $value = $this->getActionFormValue ( $name );
    return '<input type="text" name="' .  $name  . '" value="' . $this->escapeDeeply( $value ) . '" ' . $this->getAttributes( $attr ) . ' />';
  }

  /**
   * input type="password"を作る
   * @param フォームの名前
   * @param 既定値 - ActionFormならActionFormで
   * @param 追加アトリビュート
   * @return できあがったHTML文字列
   */
  public function formPassword( $name, $value = '' ,$attr = array() ){
    if ( $value == 'ActionForm' ) $value = $this->getActionFormValue ( $name );
    return '<input type="password" name="' .  $name  . '" value="' . $this->escapeDeeply( $value ) . '" ' . $this->getAttributes( $attr ) . ' />';
  }

  /**
   * textareaタグを作る
   * @param フォームの名前
   * @param 既定値 - ActionFormならActionFormで
   * @param 追加アトリビュート
   * @return できあがったHTML文字列
   */
  public function formTextArea( $name, $value = '' ,$attr = array() ){
    if ( $value == 'ActionForm' ) $value = $this->getActionFormValue ( $name );
    return '<textarea name="' .  $name  .  '"' . $this->getAttributes( $attr ) . '>' . $this->escapeDeeply( $value ) . '</textarea>' ;
  }

  /**
   * hiddenタグを作る
   * @param フォームの名前
   * @param 既定値 - ActionFormならActionFormで
   * @param 追加アトリビュート
   * @return できあがったHTML文字列
   */
  public function formHidden( $name, $value, $attr = array() ){
    if ( $value == 'ActionForm' ) $value = $this->getActionFormValue ( $name );
    return '<input type="hidden" name="' .  $name  . '" value="' . $this->escapeDeeply( $value ) . '" ' . $this->getAttributes( $attr ) . ' />';
  }

  /**
   * ラジオボタングを作る
   * @param フォームの名前
   * @param 既定値 - ActionFormならActionFormで
   * @param 追加アトリビュート
   * @param チェックボックスの配列 array ( 'value' => 'label', 'value' => 'label' )
   * @param オプション値 array ( 'value' => 'label', 'value' => 'label' )
   * @param 区切り文字
   * @return できあがったHTML文字列
   */
  public function formRadio( $name, $value, $attr = array(), $options = array(), $attrLabel = array(), $sep = '&nbsp;'){
    if ( $value == 'ActionForm' ) $value = $this->getActionFormValue ( $name );
    $buf = '';
    foreach( $options as $key => $row ){
        $buf .= '<label for="' . $name . '_' . $key . '" ' . $this->getAttributes( $attrLabel ) . '>';
        $buf .= '<input type="radio" id="'. $name . '_' . $key . '" name="' .  $name  . '" value="' . $this->escapeDeeply( $key ) . '" ';
        $buf .=  $this->getAttributes( $attr ) . ' ';
        $buf .= ( !is_null( $value ) && $value != '' && $value == $key ? 'checked="checked"' : '' );
        $buf .= ' />';
        $buf .= $row . '</label>' . $sep;
    }
    return $buf;
  }
  /**
   * チェックボックスを作る
   * @param フォームの名前
   * @param 既定値 - ActionFormならActionFormで
   * @param 追加アトリビュート
   * @param チェックボックスの配列 array ( 'value' => 'label', 'value' => 'label' )
   * @param オプション値 array ( 'value' => 'label', 'value' => 'label' )
   * @param 区切り文字
   * @return できあがったHTML文字列
   */
  public function formCheckBox( $name, $value, $attr = array(), $options = array(), $attrLabel = array(), $sep = '&nbsp;' ){
    if ( $value == 'ActionForm' ) $value = $this->getActionFormValue ( $name );
    $buf = '';
    foreach( $options as $key => $row ){
        $buf .= '<label for="' . $name . '_' . $key . '" ' . $this->getAttributes( $attrLabel ) . '">';
        $buf .= '<input type="checkbox" id="'. $name . '_' . $key . '" name="' .  $name  . (count( $options ) > 1 ? '[]' : '' ) . '" value="' . $this->escapeDeeply( $key ) . '" ' . $this->getAttributes( $attr ) . ' ' . ( ( is_array( $value ) && in_array( $key, $value ) || ( is_scalar ( $value ) && $key == $value ) ) ? 'checked="checked"' : '' ) . ' />';
        $buf .= $row . '</label>' . $sep;
    }
    return $buf;
  }

  /**
   * セレクトボックス
   * @param フォームの名前
   * @param 既定値 - ActionFormならActionFormで
   * @param 追加アトリビュート
   * @param オプション値 array ( 'value' => 'label', 'value' => 'label' )
   * @return できあがったHTML文字列
   */
  public function formSelect( $name, $value, $attr = array(), $options = array() ){
    if ( $value == 'ActionForm' ) $value = $this->getActionFormValue ( $name );
    $buf = '';
    $buf .= '<select name="' . $name . '" ' . $this->getAttributes( $attr ) .  '>';
    foreach( $options as $key => $row ){
      $buf .= '<option value="' . $this->escapeDeeply( $key ). '"' . ( !is_null( $value ) && $value != '' && $value == $key  ?  ' selected="selected"'  : '' ) . '>' . htmlspecialchars( $row, ENT_QUOTES ) . '</option>';
    }
    $buf .= '</select>';
    return $buf;
  }

  /**
   * ActionFormの値を取得する
   * @param ActionFormの名前 - 配列の入れ子はイケる(間接参照は無理)
   * @return 値
   */
  public function getActionFormValue ( $name ) {
    $value = null;
    if ( !preg_match ( '#^([^\[]+)\[.+?\]#', $name, $tmp ) ) return !is_null ( $this->ActionForm[$name] ) ? $this->ActionForm[$name] : null;
    if ( !$this->ActionForm[trim($tmp[1])] )                 return null;
    $action_form = $this->ActionForm[trim($tmp[1])];
    $name = preg_replace ( '#^[^][]+#' , '', $name );
    for ( $i = 0, $len  = mb_strlen ( $name, 'UTF8' ); $i < $len;  $i++ ) {
      $char = mb_substr ( $name, $i, 1 );
      if ( preg_match ( '#\s#', $char ) ) continue;
      if ( $char == "[" ) {
        $buf = '';
        for ( $i++; $i < $len; $i++ ) {
          $char = mb_substr ( $name, $i, 1 );
          if ( $char == "]" ) { $action_form = $action_form[$buf]; break; }
          else                { $buf .= $char; }
        }
      }
      else  {
        throw new Exception ( 'Syntax Error' );
      }
    }
    return $action_form;
  }


  /**
   * 配列からattributeを作る
   * @param attributeにしたいハッシュ
   * @return アトリビュート文字列
   */
  public function getAttributes( $attr ){
    $str = '';
    foreach( $attr as $key => $value ){
      if( $value == '' ) continue;
      $str .= ' ' .  $key .'="';
      if( is_array( $value ) ){
        // まあ、あんまり使わないで
        if( preg_match( '#^on#', $key ) ) $str .= $value;
        else                              $str .= join ( ' ', $this->escapeDeeply( $value ) );
      } else {
        if( preg_match( '#^on#', $key ) ) $str .= $value;
        else                              $str .= $this->escapeDeeply( $value );
      }
      $str .='"';
    }
    return $str;
  }

  ///
  /// こっから下はよく分からん^^;
  ///
  public function mob_link ( $url, $label = null, $params = array() ) {
    // QueryStringの生成
    !$params['q']['PHPSESSID'] && $params['q']['PHPSESSID'] = session_id();

    if ( preg_match ( '#^/#', $url ) || $this->getDomainName( $url ) == SC_DOMAIN ) {
      $q   = $this->toQueryString ( $params['q'] );
    } else {
      !$params['q']['redirect_to'] && $params['q']['redirect_to'] = $url;
      $q   = $this->toQueryString ( $params['q'] );
      $url = '/redirector';
    }

    $url .= ( preg_match ( '#\?#', $url ) ? '&': '?' ) . $q;
    $tag = '<a href="' . $url. '"';
    if ( $params['attrs'] ) $tag .= $this->getAttributes ( $params['attrs'] );
    $tag .= '>';
    if ( $label ) $tag .= htmlspecialchars ( $label, ENT_QUOTES );
    else          $tag .= $url;
    $tag .= '</a>';
    return $tag;
  }

  public function showError( $key, $error_key = 'error', $tmpl = '<p style="color:red">{val}</p>' ){
    $errors = $this->values[$error_key];
    if( $errors[$key] ) return str_replace( '{val}', $errors[$key], $tmpl );
    return '';
  }

  public function getContentType(){
    $def = aafwMobileDispatcher::isMobile( $_SERVER );
    if ( $def['is_mobile'] == "#^DoCoMo#i" ) return $def['content-type'];
    return 'text/html';
  }

	public function csrf_tag() {
		$csrf_token = hash('sha256', "social_in_csrf_token" . session_id());
		return $this->formHidden("csrf_token", $csrf_token);
	}

	function makeCickable($text)
	{
		$ret = ' ' . $text;
		$ret = preg_replace("#(^|[\n ])([\w]+?://[\w]+[^ \"\n\r\t<]*)#ise", "'\\1<a target=_\"blank\" href=\"\\2\" >\\2</a>'", $ret);
		$ret = preg_replace("#(^|[\n ])((www|ftp)\.[^ \"\t\n\r<]*)#ise", "'\\1<a target=_\"blank\" href=\"http://\\2\" >\\2</a>'", $ret);
		//$ret = preg_replace("#(^|[\n ])([a-z0-9&\-_\.]+?)@([\w\-]+\.([\w\-\.]+\.)*[\w]+)#i", "\\1<a href=\"mailto:\\2@\\3\">\\2@\\3</a>", $ret);
		$ret = substr($ret, 1);
		return($ret);
	}

}
