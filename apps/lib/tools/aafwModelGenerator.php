<?php
/**
 * よく使うディレクトリ構成を適当に初期化する
 * あるディレクトリは作らないです
 **/
class aafwModelGenerator {
  public static function showHelp () { ?>
与えられた引数のリスト分だけモデルクラスを作る
DBからテーブル構造を抜いて自動生成はローカルで
作業するのが微妙なのでやってない
<?php  }
  /**
   * 短い名前です
   **/
  public static function getShortName () {
    return 'm-gen';
  }

  /**
   *
   **/
  public static function doService ( $argv ){
    array_shift ( $argv );
    array_shift ( $argv );
    foreach ( $argv as $arg ) {
      $class = self::convertName ( $arg );
      $fname = AAFW::$AAFW_ROOT . '/classes/entities/' . self::convertManyToOne ( $class ) . '.php';
      if ( !is_file ( $fname ) )  file_put_contents ( $fname, self::getOneClass ( $class ) );

      $fname = AAFW::$AAFW_ROOT . '/classes/stores/' .  $class . '.php';
      if ( !is_file ( $fname ) )  file_put_contents ( $fname, self::getStoreClass ( $class ) );
    }
  }

  public static function getOneClass ( $class ) { ob_start () ?>
<?php print '<?php' . "\n" ?>
AAFW::import ( 'jp.aainc.aafw.base.aafwEntityBase' );
class <?php echo self::convertManyToOne ( $class ) ?> extends aafwEntityBase {
}
<?php return ob_get_clean ();
  }

  public static function getStoreClass ( $class ) { ob_start () ?>
<?php print '<?php' . "\n" ?>
AAFW::import ( 'jp.aainc.aafw.base.aafwEntityStoreBase' );
class <?php echo $class ?> extends aafwEntityStoreBase {
}
<?php return ob_get_clean ();
  }

  public static function convertName ( $str ) {
    $ret = '';
    if ( preg_match ('#[a-z]#', $str ) ) {
      $count = strlen ( $str );
      for( $i = 0; $i < $count; $i++ ){
        if     ( !$i )                           $ret .= strtoupper ( $str[$i] );
        elseif ( preg_match( '#_#', $str[$i] ) ) $ret .= strtoupper ( $str[++$i] );
        else                                     $ret .= strtolower ( $str[$i] );
      }
    }
    else {
      $ret = $str;
    }
    return $ret;
  }
  public static function convertManyToOne ( $str ) {
    return preg_replace_callback ( '#(Codes|ies|es|s)$#',  function ( $m ) {
      if     ( $m[1] == 'ies' )   return 'y';
      elseif ( $m[1] == 'Codes' ) return 'Code';
      else                        return '';
    }, $str );
  }
}
