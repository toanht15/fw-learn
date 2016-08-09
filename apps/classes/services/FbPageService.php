<?php

AAFW::import('jp.aainc.lib.base.aafwServiceBase');
AAFW::import('jp.aainc.aafw.db.aafwDataBuilder');
AAFW::import('jp.aainc.classes.entities.FbPage');

class FbPageService extends aafwServiceBase {

    protected $fb_pages;
    protected $db;

    public function __construct() {
        $this->fb_pages = $this->getModel('FbPages');
        $this->db       = new aafwDataBuilder();
    }

    /**
     * @param $conditions
     * @return mixed
     */
    public function getAllFacebookPage($conditions) {
        return $this->db->getFacebookPageList($conditions);
    }

    /**
     * @param $monitor_range
     * @return mixed
     */
    public function getCurrentMonitoringPage($monitor_range = null) {
        if ($monitor_range) {
            $filter = array('conditions' => array(
                'monitor_flg'   => FbPage::STATUS_MONITORING,
                'monitor_range' => $monitor_range
            ));
        } else {
            $filter = array('conditions' => array(
                'monitor_flg' => FbPage::STATUS_MONITORING
            ));
        }
        return $this->fb_pages->find($filter);
    }

    /**
     * @param $page_uid
     * @return null
     */
    public function updateAllPostCrawlerStatus($page_uid) {
        $fb_page = $this->getFacebookPageByPageUid($page_uid);
        if (!$fb_page) return null;
        $fb_page->all_post_crawled = 1;
        $this->saveFacebookPageData($fb_page);
    }
    /**
     * @param $page_uid
     * @param null $monitor_flg
     * @param null $monitor_range
     * @param null $monitor_config_draft
     * @return mixed|null
     */
    public function updateFacebookPageMonitoring($page_uid, $monitor_flg = null, $monitor_range = null, $monitor_config_draft = null) {
        $fb_page = $this->getFacebookPageByPageUid($page_uid);
        if (!$fb_page) return null;
        $fb_page->monitor_flg           = ($monitor_flg !== null) ? $monitor_flg : $fb_page->monitor_flg;
        $fb_page->monitor_range         = ($monitor_range !== null) ? $monitor_range : $fb_page->monitor_range;
        $fb_page->monitor_config_draft  = ($monitor_config_draft !== null) ? $monitor_config_draft : $fb_page->monitor_config_draft;
        return $this->saveFacebookPageData($fb_page);
    }

    /**
     * @param $manager_id
     * @param $fb_page_list
     */
    public function updateAllFacebookPage($manager_id, $fb_page_list) {
        foreach($fb_page_list as $element) {
            $fb_page = $this->getFacebookPageByPageUid($element['id']);
            if (!$fb_page) {
                $this->createDefaultFacebookPage($manager_id, $element);
            } else {
                $fb_page->name          = $element['name'];
                $fb_page->page_url      = $element['link'];
                $fb_page->manager_id    = $manager_id;
                $this->saveFacebookPageData($fb_page);
            }
        }
    }

    /**
     * @param $page_uid
     * @return null
     */
    public function getFacebookPageByPageUid($page_uid) {
        if (!$page_uid) return null;
        $filter = array(
            'conditions' => array(
                'page_uid'  => $page_uid
            ),
        );
        return $this->fb_pages->findOne($filter);
    }

    public function updateFbPageDisplaying($page_uid) {
        $fb_page = $this->getFacebookPageByPageUid($page_uid);
        if (!$fb_page) return null;
        $fb_page->is_hidden = !$fb_page->is_hidden;
        return $this->saveFacebookPageData($fb_page);
    }

    /**
     * @param $manager_id
     * @param $data
     * @return mixed
     */
    public function createDefaultFacebookPage($manager_id, $data) {
        $fb_page                    = $this->createEmptyFacebookPageData();
        $fb_page->manager_id        = $manager_id;
        $fb_page->name              = $data['name'];
        $fb_page->page_uid          = $data['id'];
        $fb_page->page_url          = $data['link'];
        $fb_page->monitor_flg       = FbPage::STATUS_NON_MONITORING;
        $fb_page->monitor_range     = FbPage::MONITOR_FIVE_POST;
        return $this->saveFacebookPageData($fb_page);
    }

    /**
     * @return mixed
     */
    public function createEmptyFacebookPageData() {
        return $this->fb_pages->createEmptyObject();
    }

    /**
     * @param $fb_page
     * @return mixed
     */
    public function saveFacebookPageData($fb_page) {
        return $this->fb_pages->save($fb_page);
    }
}
