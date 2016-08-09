<?php
//error_reporting ( E_ALL - E_NOTICE - E_DEPRECATED );
error_reporting(E_ERROR);
define( 'DEBUG', 1 );
define( 'AAFW_DIR'  , dirname( __FILE__ ) . '/..' );
define( 'DOC_ROOT'  , dirname( __FILE__ ) . '/../../docroot' );
define( 'DOC_ROOT_ADMIN'  , dirname( __FILE__ ) . '/../../docroot_admin' );
define( 'DOC_CONFIG', dirname( __FILE__ )  );
ini_set( 'include_path',  ini_get('include_path'). PATH_SEPARATOR . AAFW_DIR );
ini_set( 'include_path',  ini_get('include_path'). PATH_SEPARATOR . AAFW_DIR . '/lib' );
ini_set( 'include_path',  ini_get('include_path'). PATH_SEPARATOR . AAFW_DIR . '/vendor' );
ini_set( 'display_errors', DEBUG );
date_default_timezone_set ( 'Asia/Tokyo' );
require_once 'AAFW.php';
require_once 'aafwFunctions.php';
require_once 'aafwAutoLoader.php';
AAFW::start ();
AAFW::import ( 'jp.aainc.aafw.aafwApplicationConfig' );

define("APP_ID", "1083705325015701");
define("APP_SECRET", "47d2c46a0b00db3dabc32e203ea4c28b");