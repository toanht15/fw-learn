<?php
require_once  dirname(__FILE__) . '/../apps/config/define.php';
AAFW::import ( "jp.aainc.aafw.web.aafwController" );
try{
  print aafwController::getInstance()->run();
} catch( Exception $e ) {
  print "Fatal Error!";
  if ( DEBUG ) var_dump( $e );
}
