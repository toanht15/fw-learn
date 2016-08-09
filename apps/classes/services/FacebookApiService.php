<?php

AAFW::import('jp.aainc.lib.base.aafwServiceBase');
AAFW::import('jp.aainc.aafw.db.aafwDataBuilder');
require_once 'vendor/facebook/FacebookFactory.php';

class FacebookApiService extends aafwServiceBase {

    protected $facebook;

    public function __construct() {
        $appConfigs = array(
            "appId"     => APP_ID,
            "secret"    => APP_SECRET,
            "cookie"    => true
        );
        $this->facebook = FacebookFactory::getInstance($appConfigs);
    }

    public function getFacebookApiRequestUrl($page_uid, $limit) {
        $fields = 'id,message,permalink_url,created_time,comments.limit(1000).order(reverse_chronological){id,is_hidden,message,from,created_time,comments{id,is_hidden,message,from,created_time,attachment},attachment}';
        $relative_url = '/v2.5/' . $page_uid . '/posts?fields=' . $fields . '&include_hidden=true&limit=' . $limit;
        return $relative_url;
    }

    /**
     * @param $fb_page
     * @param $access_token
     * @return array
     */
    public function getFacebookPostComments($fb_page, $access_token) {

        $limit   = FbPage::$api_limit[$fb_page->monitor_range];
        $relative_url = $this->getFacebookApiRequestUrl($fb_page->page_uid, $limit);
        $response = $this->facebook->api($relative_url, 'GET', array('access_token' => $access_token));

        return $this->processFacebookResponseData($response);

    }

    public function getFacebookAllPostsData($fb_page, $access_token) {
        if ($fb_page->all_post_crawled) {
            $since          = date('Y-m-d H:i:s', strtotime('-10 days', time()));
            $until          = date('Y-m-d H:i:s');
            $relative_url   = '/v2.5/' . $fb_page->page_uid . '/posts?fields=id,message,permalink_url,created_time&include_hidden=true';
            $response       = $this->facebook->api($relative_url, 'GET', array('since' => $since, 'until' => $until, 'access_token' => $access_token));
            return $this->processFacebookResponseData($response);
        }
        $result         = array();
        $limit          = FbPage::$api_limit[$fb_page->monitor_range];
        $relative_url   = '/v2.5/' . $fb_page->page_uid . '/posts?fields=id,message,permalink_url,created_time&include_hidden=true&limit=' . $limit;
        $response       = $this->facebook->api($relative_url, 'GET', array('access_token' => $access_token));
        $tmp_result     = $this->processFacebookResponseData($response);
        $result         = array_merge($result, $tmp_result);
        while ($response['paging']['next'] && count($tmp_result) == $limit) {
            $relative_url       = $this->getNextRelativeUrlFromPaging($response['paging']['next']);
            $response           = $this->facebook->api($relative_url, 'GET', array('access_token' => $access_token));
            $tmp_result         = $this->processFacebookResponseData($response);
            $result             = array_merge($result, $tmp_result);
        }
        return $result;
    }

    public function getNextRelativeUrlFromPaging($next_url) {
        $paths = parse_url($next_url);
        parse_str($paths['query'], $queries);
        return $paths['path'] . '?fields=' . $queries['fields'] . '&limit=' . $queries['limit'] . '&__paging_token=' . $queries['__paging_token'] . '&until=' . $queries['until'];
    }

    /**
     * @param $fb_post
     * @param $access_token
     * @return array
     */
    public function getFacebookAllCommentsData($fb_post, $access_token) {
        $result         = array();
        $params         = array();
        $count          = 1;
        foreach ($fb_post as $post_element) {
            $data['relative_url']   = '/v2.5/' . $post_element->post_uid . '/comments?fields=id,is_hidden,message,from,created_time,comments.limit(500){id,is_hidden,message,from,created_time,attachment},attachment&order=reverse_chronological&limit=500';
            $data['access_token']   = $access_token;
            $data['method']         = 'GET';
            $params[$post_element->id] = $data;
            if ($count%50 == 0) {
                $result    += $this->executeBatchRequest($params, $access_token);
                $count      = 0;
                $params     = array();
            }
            $count++;
        }
        if(!empty($params)) {
            $result        += $this->executeBatchRequest($params, $access_token);
        }
        return $result;
    }

    /**
     * @param $params
     * @param $access_token
     * @return array
     */
    public function executeBatchRequest($params, $access_token) {
        $result     = array();
        $post_ids   = array_keys($params);
        $facebook_params                    = array();
        $facebook_params['batch']           = json_encode(array_values($params));
        $facebook_params['access_token']    = $access_token;

        try {
            $response       = $this->facebook->api('/v2.5/', 'POST', $facebook_params);
            foreach ($response as $key=>$value) {
                $comments = json_decode($value["body"], true);
                foreach ($comments['data'] as $comment_element) {
                    $comment = array();
                    $comment['comment_uid']     = $comment_element['id'];
                    $comment['is_hidden']       = $comment_element['is_hidden'];
                    $comment['message']         = $comment_element['message'];
                    $comment['from_uid']        = $comment_element['from']['id'];
                    $comment['from_name']       = $comment_element['from']['name'];
                    $comment['created_time']    = $comment_element['created_time'];
                    $comment['attachment']      = $comment_element['attachment']['url'] ? : '';
                    foreach($comment_element['comments']['data'] as $comment_reply_element) {
                        $comment_reply = array();
                        $comment_reply['comment_uid']       = $comment_reply_element['id'];
                        $comment_reply['is_hidden']         = $comment_reply_element['is_hidden'];
                        $comment_reply['message']           = $comment_reply_element['message'];
                        $comment_reply['from_uid']          = $comment_reply_element['from']['id'];
                        $comment_reply['from_name']         = $comment_reply_element['from']['name'];
                        $comment_reply['created_time']      = $comment_reply_element['created_time'];
                        $comment_reply['attachment']        = $comment_reply_element['attachment']['url'] ? : '';
                        $comment['comment_replies'][]       = $comment_reply;
                    }
                    $result[$post_ids[$key]][] = $comment;
                }
            }
        } catch (Exception $e) {
        }
        return $result;
    }

    public function processFacebookResponseData($response) {
        $results = array();
        foreach ($response['data'] as $post_element) {
            $post = array();
            $post['post_uid']     = $post_element['id'];
            $post['message']      = $post_element['message'];
            $post['post_url']     = $post_element['permalink_url'];
            $post['created_time'] = $post_element['created_time'];

            foreach($post_element['comments']['data'] as $comment_element) {
                $comment = array();
                $comment['comment_uid']     = $comment_element['id'];
                $comment['is_hidden']       = $comment_element['is_hidden'];
                $comment['message']         = $comment_element['message'];
                $comment['from_uid']        = $comment_element['from']['id'];
                $comment['from_name']       = $comment_element['from']['name'];
                $comment['created_time']    = $comment_element['created_time'];
                $comment['attachment']      = $comment_element['attachment']['url'] ? : '';
                foreach($comment_element['comments']['data'] as $comment_reply_element) {
                    $comment_reply = array();
                    $comment_reply['comment_uid']       = $comment_reply_element['id'];
                    $comment_reply['is_hidden']         = $comment_reply_element['is_hidden'];
                    $comment_reply['message']           = $comment_reply_element['message'];
                    $comment_reply['from_uid']          = $comment_reply_element['from']['id'];
                    $comment_reply['from_name']         = $comment_reply_element['from']['name'];
                    $comment_reply['created_time']      = $comment_reply_element['created_time'];
                    $comment_reply['attachment']        = $comment_reply_element['attachment']['url'] ? : '';
                    $comment['comment_replies'][]       = $comment_reply;
                }
                $post['comments'][] = $comment;
            }
            $results[] = $post;
        }
        return $results;
    }


//    public function getFacebookApiRequestParams($page_uid, $access_token, $limit) {
//        $params = array();
//        $params['relative_url'] = '/v2.5/' . $page_uid . '/posts?fields=' . $this->getFacebookApiRequestFields() . '&limit=' . $limit;
//        $params['access_token'] = $access_token;
//        $params['method'] = 'GET';
//        return $params;
//    }

}
