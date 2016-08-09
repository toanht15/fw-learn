<?php
AAFW::import ( 'jp.aainc.aafw.db.DB' );
AAFW::import ( 'jp.aainc.aafw.file.aafwStandardIO' );
AAFW::import ( 'jp.aainc.aafw.file.aafwDirectoryManager' );
class aafwDBMigration {
    private static $standardIO       = null;
    private static $config           = null;
    private static $directoryManager = null;

    public static function getShortName () {
        return 'migrate';
    }

    public static function setStandardIO ( $obj ) {
        self::$standardIO = $obj;
    }

    public static function setConfig ( $obj ) {
        self::$config = $obj;
    }

    public static function setDirectoryManager ( $obj ) {
        self::$directoryManager = $obj;
    }

    public static function doService() {
        self::$standardIO       = new aafwStandardIO();
        self::$config           = aafwApplicationConfig::getInstance();
        self::$directoryManager = new aafwDirectoryManager();
        DB::loadConfig();
        $groups  = DB::getDBGroups();
        $numbers = self::getTargetGroups ( $groups );
        $scripts = self::getScripts ();
        self::main ( $groups, $numbers, $scripts );
    }

    public static function main ( $groups, $numbers, $scripts ){
        $logs    = array ();
        foreach ( $numbers as $number ) {
            $dbID = $groups[$number];
            $db = DB::getInstance ( $dbID, 'w' );
            if ( !$db ) {
                $logs['fail'][] = "not found - ${dbID}:w";
            }
            else {
                try {
                    foreach ( $scripts as $script ) {
                        $db->execute ( $script );
                    }
                    $logs['success'][] = $dbID;
                }
                catch ( Exception $e ) {
                    $logs['fail'][] =  "${dbID}:" . $e->getMessage ();
                }
            }
        }
    }

    public static function getTargetGroups ( $groups ) {
        while ( true ) {
            $labels =  "DBList:\n";
            $i = 1; foreach ( $groups as $group_name ) {
                $labels .= '  ' . $i++ . ':'  . $group_name . "\n";
            }

            $val     = self::$standardIO->readLine ( $labels . 'input no [1-' . count ( $groups ) . '] or all: ' );
            $numbers = array ();
            if ( strtoupper ( $val ) == 'ALL' ) {
                $numbers = range (0, count ( $groups ) - 1 );
            }
            else {
                foreach ( preg_split ( '#\s*,\s*#', $val ) as $number ) {
                    if ( !preg_match ( '#^\d+$#', $number ) ) continue;
                    if ( $number > count ( $groups )        ) continue;
                    $numbers[] = $number - 1;
                }
            }
            if ( $numbers ) break;
        }
    }

    public static function getScript () {
        $from = self::$config->query ( '@migrate.FromVersion' );
        if ( !$from ) $from = -1;
        $path = AAFW_DIR . '/migrations';
        if ( !self::$directoryManager->isDirectory ( $path ) )
            throw new Exception ( 'no migration directory' );

        $fm = self::$directoryManager->getFileManager();
        $result = array ();
        foreach (  self::$directoryManager->getList ( AAFW_DIR . '/migrations') as $fn ) {
            if ( !self::$directoryManager->isDirectory ( $fn ) ) continue;
            if ( basename ( $fn ) < $from ) continue;
            foreach ( self::$directoryManager->getList ( $fn ) as $fn2 ) {
                if ( self::$directoryManager->isDirectory ( $fn2 ) ) continue;
                if ( !preg_match ( '#\.sql$#', $fn2 ) )              continue;
                $result[] = $fm->readALL ( $fn );
            }
        }
        return $result;
    }
}
