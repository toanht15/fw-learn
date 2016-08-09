<?php
/**
 * Created by JetBrains PhpStorm.
 * User: sekine-hironori
 * Date: 2013/02/08
 * Time: 15:45
 * To change this template use File | Settings | File Templates.
 */
spl_autoload_register(array('aafwAutoLoader', 'autoload'));


class aafwAutoLoader {

	/**
	 * @param $class
	 */
	public static function autoload($class) {

		if     ( is_file ( $path = AAFW_DIR . '/' . 'models/'  . $class . '.php' ) ) require_once $path;
		elseif ( is_file ( $path = AAFW_DIR . '/' . 'classes/' . $class . '.php' ) ) require_once $path;
		elseif ( is_file ( $path = AAFW_DIR . '/' . 'vendor/'  . $class . '.php' ) ) require_once $path;
		# for find
//		elseif ( is_file ( $path = AAFW_DIR . '/' . 'classes/find/'  . $class . '.php' ) ) require_once $path;
//		elseif ( is_file ( $path = AAFW_DIR . '/' . 'classes/find/form/'  . $class . '.php' ) ) require_once $path;
		else {
			if( @include_once (  $class . '.php' ) ) {
			} else {
				@include_once (  strtolower ( $class ) . '.php' );
			}
		}
	}
}