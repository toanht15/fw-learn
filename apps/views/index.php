<?php write_html($this->Widgets->loadWidget('UserHeader')->render()) ?>
    <div class="row-fluid">
        <div class="span12">
            <?php echo phpinfo()?>
        </div>
    </div>
<?php write_html ( $this->Widgets->loadWidget( 'UserFooter' )->render () )?>