<?php
/**
 * /usr/local/bin/php /Users/ta_minh_ha/Desktop/www/fb_comment_monitor/apps/lib/AAFW.php bat jp.aainc.actions.cli.request_facebook_all_post_data
 */
AAFW::import ( 'jp.aainc.aafw.base.aafwGETActionBase' );
AAFW::import ( 'jp.aainc.aafw.db.aafwDataBuilder' );
AAFW::import ( 'jp.aainc.aafw.factory.aafwServiceFactory' );
AAFW::import ( 'jp.aainc.aafw.factory.aafwEntityStoreFactory' );
AAFW::import ( 'jp.aainc.classes.entities.FbPage' );
class request_facebook_all_post_data extends aafwGETActionBase {
    protected $ContainerName = 'request_facebook_all_post_data';
    public function validate() {
        if (php_sapi_name() != 'cli') return false;
        return true;
    }
    public function doAction() {
        set_time_limit(3600);
        ini_set('memory_limit', '512M');

        $this->setServiceFactory(new aafwServiceFactory ());

        $manager_service        = $this->createService('ManagerService');
        $facebook_api_service   = $this->createService('FacebookApiService');
        $fb_page_service        = $this->createService('FbPageService');
        $fb_post_service        = $this->createService('FbPostService');
        $comment_service        = $this->createService('CommentService');

        $fb_pages               = $fb_page_service->getCurrentMonitoringPage(FbPage::MONITOR_ALL_POST);

        foreach ($fb_pages as $page_element) {

            /** FBページの全投稿情報を取得する */
            $access_token             = $manager_service->getAccessToken($page_element->manager_id);
            $facebook_posts_data      = $facebook_api_service->getFacebookAllPostsData($page_element, $access_token);

            /** FB投稿情報を保存する */
            foreach ($facebook_posts_data as $post_element) {
                $fb_post_service->addFacebookPost($page_element->id, $post_element);
            }

            /** 監視中投稿をゲットする */
            $monitor_posts            = $fb_post_service->getPostsByFbPageId($page_element->id);

            $facebook_comments_data   = $facebook_api_service->getFacebookAllCommentsData($monitor_posts, $access_token);

            foreach ($facebook_comments_data as $fb_post_id=>$comments) {

                foreach ($comments as $comment_element) {
                    /** FB投稿のコメントを保存する */
                    $comment = $comment_service->addFacebookComment($fb_post_id, $comment_element);

                    foreach($comment_element['comment_replies'] as $comment_reply_element) {
                        /** FB投稿のコメントに対するコメントを保存する */
                        $comment_reply_element = $comment_service->addFacebookComment($fb_post_id, $comment_reply_element, $comment->id);

                    }
                }
            }
            $fb_page_service->updateAllPostCrawlerStatus($page_element->page_uid);
        }

        return 'cli/request_facebook_post_data.php';
    }

}