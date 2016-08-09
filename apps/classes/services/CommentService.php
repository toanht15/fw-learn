<?php

AAFW::import('jp.aainc.lib.base.aafwServiceBase');
AAFW::import('jp.aainc.aafw.db.aafwDataBuilder');
AAFW::import('jp.aainc.classes.entities.Comment');

class CommentService extends aafwServiceBase {

    protected $comments;

    public function __construct() {
        $this->comments = $this->getModel('Comments');
    }

    /**
     * @param $fb_post_id
     * @param $data
     * @param null $comment_reply
     * @return mixed
     */
    public function addFacebookComment($fb_post_id, $data, $comment_reply = null) {
        return $this->getFacebookCommentByCommentUid($data['comment_uid']) ? : $this->createDefaultFacebookPost($fb_post_id, $data, $comment_reply);
    }

    public function updateCommentSendMailStatus($comment_data) {
        foreach($comment_data as $comment) {
            $comment->send_flg  = Comment::STATUS_SENT;
            $this->comments->save($comment);
        }
    }
    /**
     * @param $fb_post_id
     * @return mixed
     */
    public function getFacebookCommentByFbPostId($fb_post_id) {
        $filter = array('conditions' => array(
            'fb_post_id' => $fb_post_id,
        ));
        return $this->comments->find($filter);
    }
    /**
     * @param $comment_uid
     * @return mixed
     */
    public function getFacebookCommentByCommentUid($comment_uid) {
        $filter = array('conditions' => array(
            'comment_uid' => $comment_uid,
        ));
        return $this->comments->findOne($filter);
    }

    public function getFacebookNewComments() {
        $filter = array('conditions' => array(
            'send_flg' => Comment::STATUS_NOT_SENT
        ));
        return $this->comments->find($filter);
    }

    /**
     * @param $fb_post_id
     * @param $data
     * @param null $comment_reply
     * @return mixed
     */
    public function createDefaultFacebookPost($fb_post_id, $data, $comment_reply = null) {
        $comment                    = $this->createEmptyFacebookCommentData();
        $comment->fb_post_id        = $fb_post_id;
        $comment->comment_uid       = $data['comment_uid'];
        $comment->comment_reply     = $comment_reply;
        $comment->message           = $data['message'];
        $comment->attachment        = $data['attachment'];
        $comment->is_hidden         = $data['is_hidden'];
        $comment->from_uid          = $data['from_uid'];
        $comment->from_name         = $data['from_name'];
        $comment->created_time      = date('Y-m-d H:i:s', strtotime($data['created_time']));
        return $this->saveFacebookCommentData($comment);
    }

    /**
     * @return mixed
     */
    public function createEmptyFacebookCommentData() {
        return $this->comments->createEmptyObject();
    }

    /**
     * @param $comment
     * @return mixed
     */
    public function saveFacebookCommentData($comment) {
        return $this->comments->save($comment);
    }
}
