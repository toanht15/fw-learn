<?php
AAFW::import ( 'jp.aainc.aafw.base.aafwPOSTActionBase' );
AAFW::import ( 'jp.aainc.aafw.db.aafwDataBuilder' );
AAFW::import ( 'jp.aainc.classes.entities.FbPage' );
require_once 'vendor/facebook/FacebookFactory.php';
class process_monitoring_config extends aafwPOSTActionBase {

    protected $monitor_pages;
    protected $monitor_admins = array();
    protected $monitor_config_draft = array();
    protected $monitor_ranges = array();

    protected $ContainerName = 'manage_page';
    protected $ErrorPage = 'redirect: /manage_page';
    protected $Form = array(
        'action'    => 'manage_page',
        'package'   => ''
    );
    protected $_ModelDefinitions = array (
        'FbPages',
        'Admins',
        'FbPageAdmins'
    );

    public function doThisFirst() {
        $this->monitor_pages    = $this->POST['form_monitor_pages'];
    }
    public function validate() {
        foreach ($this->monitor_pages as $element) {
            if (!in_array($this->POST['form_monitor_range_'.$element], array_keys(FbPage::$monitor_ranges))) {
                if (!$this->POST['form_monitor_admin_'.$element]) {
                    //$this->Validator->setError('error_monitor_range', 'ERROR_MONITOR_RANGE');
                    return false;
                }
            }
            $this->monitor_ranges[$element] = $this->POST['form_monitor_range_'.$element];
            if (!$this->POST['form_monitor_admin_'.$element]) {
                //$this->Validator->setError('empty_admin', 'EMPTY_ADMIN');
                return false;
            }
            $this->monitor_config_draft[$element] = $this->POST['form_monitor_admin_'.$element];
            $this->monitor_admins[$element] = $this->getMonitorAdminInfo($this->POST['form_monitor_admin_'.$element]);
            if (!$this->monitor_admins[$element]) {
                //$this->Validator->setError('admin_error_format', 'ADMIN_ERROR_FORMAT');
                return false;
            }
        }

        return true;
    }
    public function doAction() {

        $fb_page_service        = $this->createService('FbPageService');
        $admin_service          = $this->createService('AdminService');
        $fb_page_admin_service  = $this->createService('FbPageAdminService');

        /** 監視するページを更新する */
        $current_monitoring_pages = $fb_page_service->getCurrentMonitoringPage();
        foreach ($current_monitoring_pages as $current_page) {
            if (!in_array($current_page->page_uid, $this->monitor_pages)) {
                $fb_page_service->updateFacebookPageMonitoring($current_page->page_uid, FbPage::STATUS_NON_MONITORING);
            }
        }
        foreach ($this->monitor_pages as $element) {
            $fb_page    = $fb_page_service->updateFacebookPageMonitoring($element, FbPage::STATUS_MONITORING, $this->monitor_ranges[$element], $this->monitor_config_draft[$element]);
            $admins     = array();
            foreach($this->monitor_admins[$element] as $monitor_admin) {
                $admin = $admin_service->updateAdmin($monitor_admin);
                $fb_page_admin_service->updateFbPageAdmin($fb_page->id, $admin->id);
                $admins[] = $admin->id;
            }
            $fb_page_admin_service->unsetAdminRelation($fb_page->id, $admins);
        }

        return 'redirect : /manage_page?update_monitoring=1';
    }
    private function getMonitorAdminInfo($form_monitor_admin) {
        $result = array();
        $form_monitor_admins = explode(PHP_EOL, $form_monitor_admin);
        foreach ($form_monitor_admins as $element) {
            $elements = explode(',', $element);
            if(count($elements) == 2) {
                $monitor_admin_info             = array();
                $monitor_admin_info['name']     = trim($elements[0]);
                $monitor_admin_info['email']    = trim($elements[1]);
                $result[]                       = $monitor_admin_info;
            } else {
                return null;
            }
        }
        return $result;
    }
}