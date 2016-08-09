<?php write_html($this->Widgets->loadWidget('UserHeader')->render(array('User' => $this->user))) ?>
<article class="mainCol">
    <h2><a href="manage_page.php">FBページ管理</a> | CSVダウンロード</h2>
    <table class="mBottom10">
        <thead>
        <tr>
            <th>FBページID</th>
            <th>FBページ名</th>
            <th>監視状態</th>
            <th>管理者/有効期限</th>
            <th>ダウンロード</th>
        </tr>
        <?php foreach($this->fb_pages as $element):?>
            <?php if (!$element['is_hidden']):?>
                <tr>
                    <td><?php assign($element['page_uid'])?></td>
                    <td><a target="_blank" href="<?php assign($element['page_url'])?>"><?php assign($element['name'])?></a></td>
                    <td><?php assign(FbPage::$monitor_statuses[$element['monitor_flg']]) ?></td>
                    <td style="color: <?php assign(strtotime('+7 days', time()) > strtotime($element['manager_expire_date']) ? '#ff0000' : '')?>"><?php assign($element['manager_name'])?><br><?php assign(date('Y-m-d', strtotime($element['manager_expire_date'])))?></td>
                    <td><a target="_blank" href="/process_csv_download.php?id=<?php assign($element['page_uid'])?>&post_limit=5">最新投稿５件</a>/<a target="_blank" href="/process_csv_download.php?id=<?php assign($element['page_uid'])?>">全件</a></td>
                </tr>
            <?php endif;?>
        <?php endforeach;?>
        </thead>
        <tbody>
        </tbody>
    </table>

    <!-- /.mainCol -->
</article>
<?php write_html($this->Widgets->loadWidget('UserFooter')->render())?>
