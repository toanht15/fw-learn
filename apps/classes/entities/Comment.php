<?php
AAFW::import('jp.aainc.aafw.base.aafwEntityBase');
class Comment extends aafwEntityBase {
    const STATUS_SENT       = 1;
    const STATUS_NOT_SENT   = 0;

    const STATUS_SHOW       = 0;
    const STATUS_HIDE       = 1;

    public static $comment_hidden = array(
        self::STATUS_HIDE   => "非表示",
        self::STATUS_SHOW   => "表示"
    );
}
