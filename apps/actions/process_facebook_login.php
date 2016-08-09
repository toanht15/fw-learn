<?php
AAFW::import ( 'jp.aainc.aafw.base.aafwGETActionBase' );
AAFW::import ( 'jp.aainc.aafw.db.aafwDataBuilder' );
require_once 'vendor/facebook/FacebookFactory.php';
class process_facebook_login extends aafwGETActionBase {

    protected $ContainerName = 'process_facebook_login';
    protected $facebook;
    protected $_ModelDefinitions = array (
        'Managers',
        'FbPages'
    );
    public function validate() {
        return true;
    }
    public function doAction() {
        $appConfigs = array(
            "appId"     => APP_ID,
            "secret"    => APP_SECRET,
            "cookie"    => true
        );


        $this->facebook = FacebookFactory::getInstance($appConfigs);
        $this->facebook = FacebookFactory::fbCheckLogin($appConfigs);
        $this->facebook->setExtendedAccessToken();

        $facebook_uid = $this->facebook->getUser();
        $access_token = $this->facebook->getAccessToken();

        $this->SESSION['facebook_uid'] = $facebook_uid;
        $this->SESSION['access_token'] = $access_token;

        $me = $this->facebook->api('me');
        $fb_page_list = $this->facebook->api('me/accounts?fields=id,name,link');


        $manager_service = $this->createService('ManagerService');
        $fb_page_service = $this->createService('FbPageService');

        $manager_data = array(
            'facebook_uid'      => $facebook_uid,
            'access_token'      => $access_token,
            'name'              => $me['name'],
            'expire_date'       => date('Y-m-d H:i:s', strtotime('+1 months', time()))
        );
        $manager = $manager_service->updateManagerData($manager_data);
        $fb_page_service->updateAllFacebookPage($manager->id, $fb_page_list['data']);

        return 'redirect : /manage_page?login=1';
    }
}