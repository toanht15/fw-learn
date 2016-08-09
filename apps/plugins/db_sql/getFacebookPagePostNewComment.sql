SELECT
  PAGE.id                   AS fb_page_id,
  PAGE.page_uid             AS fb_page_page_uid,
  PAGE.name                 AS fb_page_name,
  PAGE.page_url             AS fb_page_page_url,
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
  INNER JOIN fb_post AS POST ON COMMENT.fb_post_id = POST.id
  INNER JOIN fb_page AS PAGE ON POST.fb_page_id = PAGE.id
WHERE
  COMMENT.del_flg = 0
  AND POST.del_flg = 0
  AND PAGE.del_flg = 0
  AND COMMENT.created_time >= ?before_date?
ORDER BY fb_page_id, fb_post_created_time DESC, comment_created_time ASC

