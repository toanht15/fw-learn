<?php

AAFW::import('jp.aainc.lib.base.aafwServiceBase');
AAFW::import('jp.aainc.aafw.db.aafwDataBuilder');
AAFW::import('jp.aainc.classes.entities.Admin');

class AdminService extends aafwServiceBase {

    protected $admins;

    public function __construct() {
        $this->admins = $this->getModel('Admins');
    }

    /**
     * @param $email
     * @return mixed
     */
    public function getAdminByEmail($email) {
        $filter = array(
            'conditions' => array(
                'email' => $email,
            ),
        );
        return $this->admins->findOne($filter);
    }

    public function getAllAdmin() {
        return $this->admins->find();
    }

    /**
     * @param $data
     * @return mixed
     */
    public function updateAdmin($data) {
        $admin = $this->getAdminByEmail($data['email']);
        if (!$admin) return $this->createDefaultAdmin($data);
        $admin->name = $data['name'];
        return $this->saveAdminData($admin);
    }

    /**
     * @param $data
     * @return mixed
     */
    public function createDefaultAdmin($data) {
        $admin          = $this->createEmptyAdminData();
        $admin->name    = $data['name'];
        $admin->email   = $data['email'];
        return $this->saveAdminData($admin);
    }

    /**
     * @return mixed
     */
    public function createEmptyAdminData() {
        return $this->admins->createEmptyObject();
    }

    /**
     * @param $admin
     * @return mixed
     */
    public function saveAdminData($admin) {
        return $this->admins->save($admin);
    }
}
