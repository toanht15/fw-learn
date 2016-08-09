SELECT
  P.*,
  M.name as manager_name,
  M.expire_date AS manager_expire_date
FROM
  fb_page AS P
  INNER JOIN manager AS M ON P.manager_id = M.id
WHERE
  P.del_flg = 0
  AND M.del_flg = 0
ORDER BY P.monitor_flg DESC, M.expire_date DESC
