<?php

AAFW::import('jp.aainc.lib.base.aafwServiceBase');
AAFW::import('jp.aainc.aafw.db.aafwDataBuilder');
AAFW::import('jp.aainc.classes.entities.FBAdminPage');

class FbPageAdminService extends aafwServiceBase {

    protected $fb_page_admins;

    public function __construct() {
        $this->fb_page_admins = $this->getModel('FbPageAdmins');
    }

    /**
     * @param $fb_page_id
     * @param $admin_id
     * @return mixed
     */
    public function updateFbPageAdmin($fb_page_id, $admin_id) {
        return $this->getFbPageAdmin($fb_page_id, $admin_id) ? : $this->createDefaultFbPageAdmin($fb_page_id, $admin_id);
    }

    public function unsetAdminRelation($fb_page_id, $admins) {
        $fb_page_admin = $this->getFbPageAdminByFbPageId($fb_page_id);
        foreach ($fb_page_admin as $element) {
            if (!in_array($element->admin_id, $admins)) {
                $this->deleteFbPageAdminData($element);
            }
        }
    }

    /**
     * @param $admin_id
     * @return mixed
     */
    public function getFbPageAdminByAdminId($admin_id) {
        $filter = array('conditions' => array(
            'admin_id' => $admin_id
        ));
        return $this->fb_page_admins->find($filter);
    }

    /**
     * @param $fb_page_id
     * @return mixed
     */
    public function getFbPageAdminByFbPageId($fb_page_id) {
        $filter = array('conditions' => array(
            'fb_page_id' => $fb_page_id
        ));
        return $this->fb_page_admins->find($filter);
    }

    /**
     * @param $fb_page_id
     * @param $admin_id
     * @return null
     */
    public function getFbPageAdmin($fb_page_id, $admin_id) {
        if (!$fb_page_id || !$admin_id) return null;
        $filter = array(
            'conditions' => array(
                'fb_page_id'    => $fb_page_id,
                'admin_id'      => $admin_id
            ),
        );
        return $this->fb_page_admins->findOne($filter);
    }

    /**
     * @param $fb_page_id
     * @param $admin_id
     * @return mixed
     */
    public function createDefaultFbPageAdmin($fb_page_id, $admin_id) {
        $fb_page_admin                    = $this->createEmptyFbPageAdminData();
        $fb_page_admin->fb_page_id        = $fb_page_id;
        $fb_page_admin->admin_id          = $admin_id;
        return $this->saveFbPageAdminData($fb_page_admin);
    }

    /**
     * @return mixed
     */
    public function createEmptyFbPageAdminData() {
        return $this->fb_page_admins->createEmptyObject();
    }

    /**
     * @param $fb_page_admin
     * @return mixed
     */
    public function saveFbPageAdminData($fb_page_admin) {
        return $this->fb_page_admins->save($fb_page_admin);
    }

    /**
     * @param $fb_page_admin
     * @return mixed
     */
    public function deleteFbPageAdminData($fb_page_admin) {
        return $this->fb_page_admins->delete($fb_page_admin);
    }
}
