<?php
require_once 'base/aafwParserBase.php';
class JSParser extends aafwParserBase {
  public function getContentType(){
    return 'application/x-javascript; charset=utf-8';
  }
	public function in ( $data ) { return $data; }
	public function out ( $data ) { return $data; }
}
