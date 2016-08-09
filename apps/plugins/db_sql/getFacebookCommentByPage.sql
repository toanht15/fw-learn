SELECT
  POST.id                   AS fb_post_id,
  POST.post_uid             AS fb_post_post_uid,
  POST.post_url             AS fb_post_post_url,
  POST.created_time         AS fb_post_created_time,
  COMMENT.id                AS comment_id,
  COMMENT.comment_uid       AS comment_comment_uid,
  COMMENT.message           AS comment_message,
  COMMENT.comment_reply     AS comment_comment_reply,
  COMMENT.is_hidden         AS comment_is_hidden,
  COMMENT.from_uid          AS comment_from_uid,
  COMMENT.from_name         AS comment_from_name,
  COMMENT.created_time      AS comment_created_time,
  COMMENT.attachment        AS comment_attachment
FROM
  comment AS COMMENT
  INNER JOIN
  (
    SELECT *
    FROM fb_post fp
    WHERE fp.fb_page_id = ?fb_page_id?
    ORDER BY fp.created_time DESC
    LIMIT ?post_limit?                              ?POST_LIMIT?
  ) POST
  ON COMMENT.fb_post_id = POST.id
WHERE
  COMMENT.del_flg = 0
  AND POST.del_flg = 0
  AND COMMENT.created_time >= ?start_date?          ?START_DATE?
  AND COMMENT.created_time <= ?end_date?            ?END_DATE?
ORDER BY comment_created_time ASC
