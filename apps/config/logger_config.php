<?

$settings = aafwApplicationConfig::getInstance();

return array(

	'rootLogger' => array(
		'level' => 'INFO',
		'appenders' => array('file'),
	),

	'loggers' => array(

		'curely-sample' => array(
			'level' => $settings->Log4php['loggers']['curely-sample']['level'],
			'appenders' => array('file'),
			'additivity' => false,
		),

	),

	'appenders' => array(

		'default' => array(
			'class' => 'LoggerAppenderPDO',
			'params' => array(
				'dsn' => $settings->Log4php['appenders']['db']['dsn'],
				'user' => $settings->Log4php['appenders']['db']['user'],
				'password' => $settings->Log4php['appenders']['db']['password'],
				'table' => $settings->Log4php['appenders']['db']['table'],
				'insertSQL' => "INSERT INTO __TABLE__ (timestamp, logger, level, message, thread, file, line, request, cookie) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)",
				'insertPattern' => "%date{Y-m-d H:i:s},%logger,%level,%message,%pid,%file,%line,%request,%cookie",
			),
		),

		'file' => array(
			'class' => 'LoggerAppenderDailyFile',
			'layout' => array(
				'class' => 'LoggerLayoutPattern',
				'params' => array(
					'conversionPattern' => '%d{Y-m-d H:i:s} %file %line %logger %-5level  %msg %newline%ex',
				)
			),
			'params' => array(
				'datePattern' => 'Y-m-d',
				'append' => true,
				'file' => $settings->Log4php['appenders']['file']['name'],
			)
		),
	)
);
