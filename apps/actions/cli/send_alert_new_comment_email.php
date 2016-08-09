<?php
/**
 * /usr/local/bin/php /Users/ta_minh_ha/Desktop/www/fb_comment_monitor/apps/lib/AAFW.php bat jp.aainc.actions.cli.send_alert_new_comment_email
 */
AAFW::import ( 'jp.aainc.aafw.base.aafwGETActionBase' );
AAFW::import ( 'jp.aainc.aafw.db.aafwDataBuilder' );
AAFW::import('jp.aainc.aafw.factory.aafwServiceFactory');
AAFW::import('jp.aainc.aafw.factory.aafwEntityStoreFactory');
class send_alert_new_comment_email extends aafwGETActionBase {
    protected $ContainerName = 'send_alert_new_comment_email';
    public function validate() {
        if (php_sapi_name() != 'cli') return false;
        return true;
    }
    public function doAction() {
        $this->setServiceFactory(new aafwServiceFactory ());

        $mail                   = new MailManager();
        $mail->loadMailContent('alert_new_comment');

        $common_service         = $this->createService('CommonService');
        $comment_service        = $this->createService('CommentService');
        $admin_service          = $this->createService('AdminService');
        $fb_page_admin_service  = $this->createService('FbPageAdminService');


        $before_comments        = $comment_service->getFacebookNewComments();

        if ($before_comments) {
            $mail_contents          = $common_service->getEmailContentList();

            $admins                 = $admin_service->getAllAdmin();

            foreach ($admins as $admin) {
                $fb_page_admins     = $fb_page_admin_service->getFbPageAdminByAdminId($admin->id);
                $mail_content       = "";
                foreach ($fb_page_admins as $fb_page_admin) {
                    $mail_content  .= $mail_contents[$fb_page_admin->fb_page_id] ? : "";
                }
                if($mail_content != "") {
                    $replaceParams = array(
                        'USER_NAME'     => $admin->name,
                        'START_DATE'    => date('Y-m-d H', strtotime('-1 days', time())),
                        'END_DATE'      => date('Y-m-d H'),
                        'CONTENT'       => $mail_content
                    );
                    $mail->sendNow($admin->email, $replaceParams);
                }

            }

            $comment_service->updateCommentSendMailStatus($before_comments);
        }
        return 'cli/send_alert_new_comment_email.php';
    }

}