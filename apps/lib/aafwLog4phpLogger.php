<?php

include(dirname(__FILE__) . '/../vendor/log4php/Logger.php');
include(dirname(__FILE__) . '/../vendor/log4php/LoggerAutoloader.php');

$settings = aafwApplicationConfig::getInstance();
$config_file = DOC_CONFIG . DIRECTORY_SEPARATOR . $settings->Log4php['configFileName'];
Logger::configure($config_file);

class aafwLog4phpLogger {

	const LOGGER_TYPE_CURELY_SAMPLE = 'curely-sample';

	public static function getLogger($name) {
		return Logger::getLogger($name);
	}

	public static function getAdminLogger() {
		return self::getLogger(self::LOGGER_TYPE_CURELY_SAMPLE);
	}

}
