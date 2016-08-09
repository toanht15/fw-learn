<?php
AAFW::import('jp.aainc.aafw.base.aafwGETActionBase');
AAFW::import('jp.aainc.aafw.db.aafwDataBuilder');
class manage_page extends aafwGETActionBase {

    protected $fb_page_service;
    protected $manager_service;

    public function validate() {
        return true;
    }

    public function doAction() {
        if ($this->login) {
            $this->Data['login'] = $this->login;
        }
        if (!$this->show_archives) {
            $this->Data['show_archives'] = 0;
        } else {
            $this->Data['show_archives'] = 1;
        }
        $this->fb_page_service = $this->createService('FbPageService');
        $this->manager_service = $this->createService('ManagerService');

        $this->Data['fb_pages'] = $this->fb_page_service->getAllFacebookPage();

        $this->Data['manager']  = $this->manager_service->getAllManagerInfo();

        return 'manage_page.php';
    }
}
