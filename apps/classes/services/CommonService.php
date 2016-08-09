<?php
AAFW::import('jp.aainc.lib.base.aafwServiceBase');
AAFW::import('jp.aainc.aafw.db.aafwDataBuilder');

require_once 'parsers/YAMLParser.php';

class CommonService extends aafwServiceBase {

    protected $db;
    protected $settings;
    public function __construct() {
        $this->settings = aafwApplicationConfig::getInstance();
        $this->db       = new aafwDataBuilder();
    }

    public function getCSVDownloadUrl($page_uid, $from) {
        $protocol       = $this->settings->Protocol['Secure'];
        $domain         = $this->settings->Domain;
        return  $protocol . '://' . $domain . '/process_csv_download?id=' . $page_uid . '&from=' . $from;
    }

    /**
     * DBに新しいコメントを抽出する
     * @return array
     */
    public function getNewCommentsForNotify() {
        $result = array();
        $conditions = array('before_date' => date('Y-m-d H:i:s', strtotime('-1 days', time())));
        $rows = $this->db->getFacebookPagePostNewComment($conditions);
        foreach ($rows as $element) {
            $result[$element['fb_page_id']]['page_uid']       = $element['fb_page_page_uid'];
            $result[$element['fb_page_id']]['name']           = $element['fb_page_name'];
            $result[$element['fb_page_id']]['page_url']       = $element['fb_page_page_url'];

            $result[$element['fb_page_id']]['posts'][$element['fb_post_id']]['post_uid']        = $element['fb_post_post_uid'];
            $result[$element['fb_page_id']]['posts'][$element['fb_post_id']]['post_url']        = $element['fb_post_post_url'];
            $result[$element['fb_page_id']]['posts'][$element['fb_post_id']]['created_time']    = $element['fb_post_created_time'];
            if ($element['comment_comment_reply']) {
                $result[$element['fb_page_id']]['posts'][$element['fb_post_id']]['comments'][$element['comment_comment_reply']]['comment_replies'][$element['comment_id']]['comment_uid']   = $element['comment_comment_uid'];
                $result[$element['fb_page_id']]['posts'][$element['fb_post_id']]['comments'][$element['comment_comment_reply']]['comment_replies'][$element['comment_id']]['message']       = $element['comment_message'];
                $result[$element['fb_page_id']]['posts'][$element['fb_post_id']]['comments'][$element['comment_comment_reply']]['comment_replies'][$element['comment_id']]['is_hidden']     = $element['comment_is_hidden'];
                $result[$element['fb_page_id']]['posts'][$element['fb_post_id']]['comments'][$element['comment_comment_reply']]['comment_replies'][$element['comment_id']]['from_uid']      = $element['comment_from_uid'];
                $result[$element['fb_page_id']]['posts'][$element['fb_post_id']]['comments'][$element['comment_comment_reply']]['comment_replies'][$element['comment_id']]['from_name']     = $element['comment_from_name'];
                $result[$element['fb_page_id']]['posts'][$element['fb_post_id']]['comments'][$element['comment_comment_reply']]['comment_replies'][$element['comment_id']]['created_time']  = $element['comment_created_time'];
                $result[$element['fb_page_id']]['posts'][$element['fb_post_id']]['comments'][$element['comment_comment_reply']]['comment_replies'][$element['comment_id']]['attachment']    = $element['comment_attachment'];

            } else {
                $result[$element['fb_page_id']]['posts'][$element['fb_post_id']]['comments'][$element['comment_id']]['comment_uid']       = $element['comment_comment_uid'];
                $result[$element['fb_page_id']]['posts'][$element['fb_post_id']]['comments'][$element['comment_id']]['message']           = $element['comment_message'];
                $result[$element['fb_page_id']]['posts'][$element['fb_post_id']]['comments'][$element['comment_id']]['is_hidden']         = $element['comment_is_hidden'];
                $result[$element['fb_page_id']]['posts'][$element['fb_post_id']]['comments'][$element['comment_id']]['from_uid']          = $element['comment_from_uid'];
                $result[$element['fb_page_id']]['posts'][$element['fb_post_id']]['comments'][$element['comment_id']]['from_name']         = $element['comment_from_name'];
                $result[$element['fb_page_id']]['posts'][$element['fb_post_id']]['comments'][$element['comment_id']]['created_time']      = $element['comment_created_time'];
                $result[$element['fb_page_id']]['posts'][$element['fb_post_id']]['comments'][$element['comment_id']]['attachment']        = $element['comment_attachment'];
            }
        }
        return $result;
    }

    public function getFacebookCommentByFbPageId($conditions) {
        $result         = array();
        $comment_data   = $this->db->getFacebookCommentByPage($conditions);
        foreach ($comment_data as $element) {
            $result[$element['comment_id']] = $element;
        }
        return $result;
    }

    /**
     * コメント情報をメールコンテンツに切り替える
     * @return array
     */
    public function getEmailContentList() {
        $result = array();
        $data_rows = $this->getNewCommentsForNotify();
        foreach ($data_rows as $fb_page_id=>$fb_page) {
            $mail_content  = "FBページ================================================================\n";
            $mail_content .= $fb_page['name'] . "(" . $fb_page['page_uid'] . ")\n";
            $mail_content .= $fb_page['page_url'] . "\n\n";

            $mail_content .= "CSVダウンロード\n";
            $mail_content .= $this->getCSVDownloadUrl($fb_page['page_uid'], date('Ymd', strtotime('-3 days', time()))) . "\n\n";

            foreach ($fb_page['posts'] as $fb_post) {
                $mail_content .= "投稿================================================\n";
                $mail_content .= $fb_post['post_url'] . "\n\n";

                foreach ($fb_post['comments'] as $comment) {
                    $mail_content .= ">>>\n";
                    $mail_content .= "https://www.facebook.com/" . $comment['comment_uid'] . "\n";
                    $is_hidden     = $comment['is_hidden'] ? '（非表示）' : '';
                    $mail_content .= "From " . $comment['from_name'] . " " . $comment['created_time'] . $is_hidden . "\n\n";

                    $message       = $comment['message'] ? : '';
                    $attachment    = $comment['attachment'] ? "\n" . $comment['attachment'] : '';
                    $mail_content .= $message . $attachment . "\n\n";

                    foreach($comment['comment_replies'] as $comment_reply) {
                        $mail_content .= ">>>>>>\n";
                        $mail_content .= "https://www.facebook.com/" . $comment_reply['comment_uid'] . "\n";
                        $is_hidden     = $comment_reply['is_hidden'] ? '（非表示）' : '';
                        $mail_content .= "From " . $comment_reply['from_name'] . " " . $comment_reply['created_time'] . $is_hidden . "\n\n";

                        $message       = $comment_reply['message'] ? : '';
                        $attachment    = $comment_reply['attachment'] ? "\n" . $comment_reply['attachment'] : '';
                        $mail_content .= $message . $attachment . "\n\n";

                    }
                }

            }
            $result[$fb_page_id] = $mail_content;
        }
        return $result;
    }

}