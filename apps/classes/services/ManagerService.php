<?php

AAFW::import('jp.aainc.lib.base.aafwServiceBase');
AAFW::import('jp.aainc.aafw.db.aafwDataBuilder');
AAFW::import('jp.aainc.classes.entities.Manager');

class ManagerService extends aafwServiceBase {

    protected $managers;

    public function __construct() {
        $this->managers = $this->getModel('Managers');
    }

    /**
     * @param $manager_id
     * @return mixed
     */
    public function getAccessToken($manager_id) {
        $filter = array('conditions' => array(
            'id' => $manager_id
        ));
        $result = $this->managers->findOne($filter);
        return $result->access_token;
    }

    /**
     * @return mixed
     */
    public function getAllManagerInfo() {
        return $this->managers->find();
    }

    /**
     * @param $data
     * @return mixed
     */
    public function createDefaultManager($data) {
        $manager                        = $this->createEmptyManagerData();
        $manager->facebook_uid          = $data['facebook_uid'];
        $manager->name                  = $data['name'];
        $manager->access_token          = $data['access_token'];
        $manager->expire_date           = $data['expire_date'];
        return $this->saveManagerData($manager);
    }

    /**
     * @param $data
     * @return mixed
     */
    public function updateManagerData($data) {
        $manager = $this->getManagerByFacebookUid($data['facebook_uid']);

        if (!$manager) return $this->createDefaultManager($data);

        $manager->name                  = $data['name'];
        $manager->access_token          = $data['access_token'];
        $manager->expire_date           = $data['expire_date'];
        return $this->saveManagerData($manager);
    }

    public function getManagerByFacebookUid($facebook_uid) {
        if (!$facebook_uid) return null;
        $filter = array(
            'conditions' => array(
                'facebook_uid'  => $facebook_uid
            ),
        );
        return $this->managers->findOne($filter);
    }

    /**
     * @return mixed
     */
    public function createEmptyManagerData() {
        return $this->managers->createEmptyObject();
    }

    /**
     * @param $manager
     * @return mixed
     */
    public function saveManagerData($manager) {
        return $this->managers->save($manager);
    }



}
