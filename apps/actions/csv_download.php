<?php
AAFW::import('jp.aainc.aafw.base.aafwGETActionBase');
AAFW::import('jp.aainc.aafw.db.aafwDataBuilder');
class csv_download extends aafwGETActionBase {

    private $fb_page_service;

    public function validate() {
        return true;
    }

    public function doAction() {
        $this->fb_page_service = $this->createService("FbPageService");
        $fb_pages = $this->fb_page_service->getAllFacebookPage();
        $this->Data['fb_pages'] = $fb_pages;
        return 'csv_download.php';
    }
}
