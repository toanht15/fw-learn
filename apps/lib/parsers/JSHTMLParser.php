<?php
require_once 'base/aafwParserBase.php';
require_once 'parsers/PHPParser.php';

class JSHTMLParser extends ParserBase {
  public function getContentType(){ return 'text/javascript'; }
  public function in( $data ) { return true; }
  public function out( $data ) {
    if( $data['__HTML__'] ) $html = $data['__HTML__'];
    else                    $html = PHPParser::out( $data );
    return "document.write('" . str_replace( "'", "\'", preg_replace( "/(\r\n|\r|\n)/", '', $html ) ) . "');";
  }
}
