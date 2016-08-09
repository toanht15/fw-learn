<?php
AAFW::import('jp.aainc.aafw.base.aafwGETActionBase');
AAFW::import('jp.aainc.aafw.db.aafwDataBuilder');
class api_update_fb_page_displaying extends aafwGETActionBase {

    protected $fb_page_service;

    public function validate() {
        return true;
    }

    public function doAction() {

        $this->fb_page_service = $this->createService('FbPageService');

        if (!$this->page_uid) {
            $this->Data['status'] = 'ERROR INPUT';
            return 'api_update_fb_page_displaying.php';
        }
        $fb_page = $this->fb_page_service->updateFbPageDisplaying($this->page_uid);
        if ($fb_page) {
            $this->Data['status'] = FbPage::$archives[$fb_page->is_hidden];
        } else {
            $this->Data['status'] = 'ERROR INPUT';
        }

        return 'api_update_fb_page_displaying.php';
    }
}
