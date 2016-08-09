<?php

AAFW::import('jp.aainc.lib.base.aafwServiceBase');
AAFW::import('jp.aainc.aafw.db.aafwDataBuilder');
AAFW::import('jp.aainc.classes.entities.FBPost');

class FbPostService extends aafwServiceBase {

    protected $fb_posts;

    public function __construct() {
        $this->fb_posts = $this->getModel('FbPosts');
    }

    /**
     * @param $fb_page_id
     * @return mixed
     */
    public function getPostsByFbPageId($fb_page_id) {
        $filter = array(
            'conditions' => array(
                'fb_page_id' => $fb_page_id
            )
        );
        return $this->fb_posts->find($filter);
    }

    /**
     * @param $fb_page_id
     * @param $data
     * @return mixed
     */
    public function addFacebookPost($fb_page_id, $data) {
        return $this->getFacebookPostByPostUid($data['post_uid']) ? : $this->createDefaultFacebookPost($fb_page_id, $data);
    }

    /**
     * @param $post_uid
     * @return mixed
     */
    public function getFacebookPostByPostUid($post_uid) {
        $filter = array('conditions' => array(
            'post_uid' => $post_uid,
        ));
        return $this->fb_posts->findOne($filter);
    }
    /**
     * @param $fb_page_id
     * @param $data
     * @return mixed
     */
    public function createDefaultFacebookPost($fb_page_id, $data) {
        $fb_post                    = $this->createEmptyFacebookPostData();
        $fb_post->fb_page_id        = $fb_page_id;
        $fb_post->post_uid          = $data['post_uid'];
        $fb_post->post_url          = $data['post_url'];
        $fb_post->message           = $data['message'];
        $fb_post->created_time      = date('Y-m-d H:i:s', strtotime($data['created_time']));
        return $this->saveFacebookPostData($fb_post);
    }

    /**
     * @return mixed
     */
    public function createEmptyFacebookPostData() {
        return $this->fb_posts->createEmptyObject();
    }

    /**
     * @param $fb_post
     * @return mixed
     */
    public function saveFacebookPostData($fb_post) {
        return $this->fb_posts->save($fb_post);
    }
}
