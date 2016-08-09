<?php
AAFW::import('jp.aainc.aafw.base.aafwActionPluginBase');
AAFW::import('jp.aainc.aafw.web.aafwWidgets');
class DefaultDataSetter extends aafwActionPluginBase {
	protected $HookPoint = 'Finally';
	protected $Priority = 1;

	public function doService() {
		list($p, $g, $s, $c, $f, $e, $sv, $r) = $this->Action->getParams();
		$data = $this->Action->getData();
		if (!is_array($data)) return '';
		if (!$data['Member']) $data['Member'] = $r['Member'];
		if (!$data['Widgets']) $data['Widgets'] = aafwWidgets::getInstance();
		if (!$data['Config']) $data['Config'] = aafwApplicationConfig::getInstance();
		$this->Action->setData($data);
		$this->Action->rewriteParams($p, $g, $s, $c, $f, $e, $sv, $r);
	}
}
