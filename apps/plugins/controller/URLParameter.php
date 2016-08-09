<?php
AAFW::import ( 'jp.aainc.aafw.base.aafwControllerPluginBase' );
class URLParameter extends aafwControllerPluginBase {
  protected $HookPoint = 'First';
  protected $Priority  = 1;

  public function doService(){
    list( $p, $g, $s, $c, $f, $e, $sv, $r ) = $this->Controller->getParams();
    if ( @$g['action'] )                                                   return ;
    if ( !$sv['REQUEST_URI'] || $sv['REQUEST_URI'] == '/' || preg_match( '#^/\?#', $sv['REQUEST_URI'] ) ){
      $g['action']  = 'index';
      $this->Controller->rewriteParams( $p, $g, $s, $c, $f, $e, $sv, $r );
      return ;
    }
    if ( !preg_match( '#^/([^\?]+)(?:\?|$)#', $sv['REQUEST_URI'], $tmp ) ) return ;
    $subdir  = str_replace( '/', '',  $this->Controller->getSubDirectory() );
    $ac_path = preg_replace ( array( '#//#', '#/$#' ), array( '/', '' ), $this->Controller->getActionPath() );

    list( $package_name, $action_name, $path ) = array( '', '', '' );

    if ( $subdir )  $tmp[1] = preg_replace( '#/?' . $subdir . '/?#', '' , $tmp[1] );
    $path = preg_grep( '#.#', preg_split( '#/#', $tmp[1] ) );
    $tmp  = array();
    foreach( $path as $x ){
      if( preg_match( '#^\.+$#', $x ) ) continue;
      $tmp[] = $x;
    }
    $g['__path'] = $path = $tmp;

    // 該当ファイルがある場合はファイルを優先
	if ( is_file( $ac_path . '/' .  ($this->Controller->getSite() ? $this->Controller->getSite() . '/' : '') . preg_replace( '#\..+$#','', $path[0] ) . '.php' ) ){
      $action_name = array_shift( $path );
    }

    // 該当するディレクトリがある場合
    elseif ( is_dir( $ac_path . '/' . ($this->Controller->getSite() ? $this->Controller->getSite() . '/' : '') . $path[0] ) ) {
      $package_name = array_shift( $path );
      $action_name  = array_shift( $path );
      if ( !$action_name ) $action_name = 'index';
    }
    if ( preg_match( '#^(.+?)\.([^\.]+)$#', $action_name, $tmp ) ){
      $action_name = $tmp[1];
      $req         = $tmp[2];
    }
    elseif ( preg_match( '#^(.+?)\.([^\.]+)#', $path[count($path)-1], $tmp ) ){
      $path[count($path)-1] = $tmp[1];
      $req                  = $tmp[2];
    }
    $g['req']     = $req;
    $g['exts']    = $path;
    $g['action']  = $action_name;
    $g['package'] = $package_name;
    $this->Controller->rewriteParams( $p, $g, $s, $c, $f, $e, $sv, $r );
  }
}
