<?php
require_once 'base/aafwParserBase.php';
class CSSParser extends aafwParserBase {
  public function getContentType(){
    return 'text/css; charset=utf-8';
  }
	public function in ( $data ) { return $data; }
	public function out ( $data ) { return $data; }
}