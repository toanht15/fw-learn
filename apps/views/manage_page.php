<?php write_html($this->Widgets->loadWidget('UserHeader')->render(array('User' => $this->user))) ?>
<article class="mainCol">
    <h2>FBページ管理 | <a href="csv_download.php">CSVダウンロード</a></h2>
    <article class="mainBox1">
        <?php if(!$this->manager):?>
            <p align="center"><a href="/process_facebook_login" class = "btn btn-primary">Facebookログイン</a></p>
        <?php else:?>
            <?php foreach($this->manager as $element):?>
                <p align="center" style="color: #ff0000"><b><?php assign($element->name)?></b>様のFacebookAPIの有効期限　▶　<?php assign($element->expire_date);?></p>
            <?php endforeach;?>
            <p align="center"><a href="/process_facebook_login" class = "btn btn-primary">FacebookAPIの有効期限を更新する</a></p>
        <?php endif;?>
    </article>
    <form action="process_monitoring_config" method="post">
        <table class="mBottom10">
            <thead>
            <p style="color: #ff0000; font-size: small;">(*)担当者を複数設定する場合は改行してください。</p>
            <?php if($this->show_archives):?>
                <p><a href="manage_page.php">非表示状態のページを一覧に表示しない</a></p>
            <?php else:?>
                <p><a href="manage_page.php?show_archives=1">非表示にしたページを一覧に表示する</a></p>
            <?php endif;?>
            <tr>
                <th colspan="1">監視状態</th>
                <th colspan="1">FBページID/名</th>
                <th colspan="1">▶担当者の名前・メールアドレス◀</th>
                <th colspan="1">監視範囲</th>
                <th colspan="1">FBページ管理者</th>
            </tr>
            <?php foreach($this->fb_pages as $element):?>
                <?php if(!$element['is_hidden'] || $this->show_archives):?>
                    <tr id="element_<?php assign($element['page_uid'])?>">
                        <td><input id="chk_<?php assign($element['page_uid'])?>" type="checkbox" onchange="updateMonitoringStatus(<?php assign($element['page_uid'])?>)" name="form_monitor_pages[]" value="<?php assign($element['page_uid'])?>" <?php assign(($element['monitor_flg'] == FbPage::STATUS_MONITORING) ? 'checked' : '')?>></td>
                        <td>
                            <a href="<?php assign($element['page_url'])?>" target="_blank"><?php assign($element['page_uid'])?><br><?php assign($element['name'])?></a><br>
                            <?php if(!$element['monitor_flg']):?>
                                <a href="javascript:void(0);" style="color: <?php assign($element['is_hidden'] ? '#ff0000' : '')?>" id="archives_<?php assign($element['page_uid'])?>" onclick="updatePageDisplayStatus(<?php assign($element['page_uid'])?>, <?php assign($this->show_archives)?>)"><?php assign(FbPage::$archives[$element['is_hidden']])?></a>
                            <?php endif;?>
                        </td>
                        <td>
                            <textarea id="txt_<?php assign($element['page_uid'])?>" name="form_monitor_admin_<?php assign($element['page_uid'])?>" rows="5" placeholder="名前, メールアドレス" class="input-block-level" <?php assign(($element['monitor_flg'] == FbPage::STATUS_NON_MONITORING) ? 'disabled=disabled' : '')?>><?php assign($element['monitor_config_draft'])?></textarea>
                        </td>
                        <td>
                            <input class="radio_<?php assign($element['page_uid'])?>" type="radio" name="form_monitor_range_<?php echo $element['page_uid']?>" value="<?php assign(FbPage::MONITOR_FIVE_POST)?>" <?php assign(($element['monitor_range'] == FbPage::MONITOR_FIVE_POST)? 'checked' : '')?> <?php assign(($element['monitor_flg'] == FbPage::STATUS_NON_MONITORING) ? 'disabled=disabled' : '')?>><?php assign(FbPage::$monitor_ranges[FbPage::MONITOR_FIVE_POST])?>
                            <input class="radio_<?php assign($element['page_uid'])?>" type="radio" name="form_monitor_range_<?php echo $element['page_uid']?>" value="<?php assign(FbPage::MONITOR_ALL_POST)?>" <?php assign(($element['monitor_range'] == FbPage::MONITOR_ALL_POST)? 'checked' : '')?> <?php assign(($element['monitor_flg'] == FbPage::STATUS_NON_MONITORING) ? 'disabled=disabled' : '')?>><?php assign(FbPage::$monitor_ranges[FbPage::MONITOR_ALL_POST])?>
                        </td>
                        <td><?php assign($element['manager_name'])?></td>
                    </tr>
                <?php endif;?>
            <?php endforeach;?>
            </thead>
            <tbody>
            </tbody>
        </table>
        <?php if($this->fb_pages):?>
            <p align="center"><button class='btn btn-success' type='submit'>監視を設定する</button></p>
        <?php endif;?>
    </form>
    <!-- /.mainCol -->
</article>
<?php write_html($this->Widgets->loadWidget('UserFooter')->render())?>
