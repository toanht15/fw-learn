<?php
AAFW::import('jp.aainc.aafw.base.aafwEntityBase');
class FbPage extends aafwEntityBase {
    const STATUS_MONITORING         = 1;            //監視
    const STATUS_NON_MONITORING     = 0;            //非監視

    const MONITOR_FIVE_POST         = 1;            //最新投稿５件
    const MONITOR_ALL_POST          = 2;            //過去すべて

    const API_LIMIT_DEFAULT         = 5;
    const API_LIMIT_ALL             = 100;

    const ARCHIVES_SHOW             = 0;
    const ARCHIVES_HIDE             = 1;

    public static $monitor_statuses = array(
        self::STATUS_MONITORING         => '監視中',
        self::STATUS_NON_MONITORING     => ''
    );

    public static $monitor_ranges = array(
        self::MONITOR_FIVE_POST     => '最新投稿５件',
        self::MONITOR_ALL_POST      => '過去すべて'
    );

    public static $archives = array(
        self::ARCHIVES_SHOW => '非表示にする',
        self::ARCHIVES_HIDE => '非表示状態を解除する'
    );

    public static $api_limit = array(
        self::MONITOR_FIVE_POST     => self::API_LIMIT_DEFAULT,
        self::MONITOR_ALL_POST      => self::API_LIMIT_ALL
    );


}
