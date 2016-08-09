<?php
AAFW::import ( 'jp.aainc.aafw.base.aafwGETActionBase' );
AAFW::import ( 'jp.aainc.aafw.db.aafwDataBuilder' );
AAFW::import ( 'jp.aainc.aafw.parsers.CSVParser' );
AAFW::import ( 'jp.aainc.classes.entities.Comment' );
class process_csv_download extends aafwGETActionBase {

    protected $ContainerName = 'process_csv_download';
    protected $start_date;
    protected $end_date;
    protected $page_uid;
    protected $post_limit;
    protected $fb_page;

    public function doThisFirst() {

        if ($this->GET['from']) {
            $this->start_date   = date('Y-m-d', strtotime($this->GET['from']));
        }
        if ($this->GET['to']) {
            $this->end_date     = date('Y-m-d 23:59:59', strtotime($this->GET['to']));
        }
        if ($this->GET['post_limit']) {
            $this->post_limit   = $this->GET['post_limit'];
        }
        $this->page_uid         = $this->GET['id'];

    }
    public function validate() {

        if (!$this->page_uid) return false;
        if($this->start_date && $this->end_date) {
            return $this->start_date < $this->end_date;
        }
        return true;

    }
    public function doAction() {
        set_time_limit(3600);
        ini_set('memory_limit', '512M');

        $fb_page_service    = $this->createService('FbPageService');
        $this->fb_page      = $fb_page_service->getFacebookPageByPageUid($this->page_uid);
        if (!$this->fb_page->id) return 'redirect : /csv_download?status=0';

        $csv_download_conditions    = array();
        if ($this->start_date) {
            $csv_download_conditions['start_date']  = $this->start_date;
            $csv_download_conditions['START_DATE']  = '__ON__';
        }
        if ($this->end_date) {
            $csv_download_conditions['end_date']    = $this->end_date;
            $csv_download_conditions['END_DATE']    = '__ON__';
        }
        if ($this->post_limit) {
            $csv_download_conditions['post_limit']  = $this->post_limit;
            $csv_download_conditions['POST_LIMIT']  = '__ON__';
        }
        $csv_download_conditions['fb_page_id']      = $this->fb_page->id;

        $common_service     = $this->createService('CommonService');
        $csv_data           = $common_service->getFacebookCommentByFbPageId($csv_download_conditions);
        if (empty($csv_data)) return 'redirect : /csv_download?status=2';

        $this->exportCSV($csv_data);
        
    }
    public function exportCSV($csv_data) {
        $csv = new CSVParser();
        header('Content-Encoding:UTF-8');
        header("Content-type: text/csv;;charset=UTF-8");
        header($csv->getDisposition());

        $bom = chr(255) . chr(254);

        $csvData['list'][] = array('コメント発生日時', 'FB投稿投稿日', '表示状態', 'ユーザー名', 'コメント', '添付', '投稿URL', 'コメントID', '親コメントID', '投稿ID');

        foreach($csv_data as $element) {
            $csv_element        = array();
            $csv_element[]      = $element["comment_created_time"];
            $csv_element[]      = $element["fb_post_created_time"];
            $csv_element[]      = Comment::$comment_hidden[$element["comment_is_hidden"]];
            $csv_element[]      = $element["comment_from_name"];
            $csv_element[]      = $this->editColumnValue($element["comment_message"]) ? : ' ';
            $csv_element[]      = $element["comment_attachment"] ? : ' ';
            $csv_element[]      = 'https://www.facebook.com/' . $element["comment_comment_uid"];
            $csv_element[]      = $element["comment_comment_uid"];
            if ($element['comment_comment_reply']) {
                $csv_element[]  = $csv_data[$element["comment_comment_reply"]]["comment_comment_uid"];
            } else {
                $csv_element[]  = "";
            }
            $csv_element[]      = $element["fb_post_post_uid"];
            $csvData['list'][]  = $csv_element;
        }
        $record = $this->convertTextEncoding($csvData);

        //エンコード
        $encoded  = $bom . mb_convert_encoding($record, 'UTF-16LE', 'UTF-8');

        $out = fopen('php://output','w');
        fputs( $out, $encoded );
        fclose($out);
        exit();
    }
    private function editColumnValue ( $col ) {
        $col = trim( $col );
        $col = str_replace( '"', '\\"' , $col );
        $col = str_replace( "\r\n", ' ', $col );
        $col = str_replace( "\r"  , ' ', $col );
        $col = str_replace( "\n"  , ' ', $col );
        $col = '"'  . $col . '"';
        return $col;
    }

    private function convertTextEncoding($csvData) {
        $record = "";
        foreach ($csvData['list'] as $element) {
            $record .= implode("\t", $element) . "\r\n";
        }
        return $record;
    }
}