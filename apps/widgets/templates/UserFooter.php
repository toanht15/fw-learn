</div>
<!-- container -->
</div>
<div id="push"></div>
</div>
<div id="footer">
	<div>
		<p class="muted">
			<small>Copyright (c) 2012-2013 Allied Architects, Inc. All Rights
				Reserved.</small> <small>&nbsp;|&nbsp;<a target="_blank"
				href="http://www.aainc.co.jp/">運営会社</a>
			</small> <small>&nbsp;|&nbsp;<a target="_blank"
				href="http://www.aainc.co.jp/policy/">プライバシーポリシー</a>
			</small> <small>&nbsp;|&nbsp;<a target="_blank"
				href="http://www.aainc.co.jp/service/social-in/rule/">利用規約</a>
			</small> <small>&nbsp;|&nbsp;</small>
		</p>
	</div>
</div>
<script
	src="//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
<script
	src="//code.jquery.com/ui/1.10.3/jquery-ui.js"></script>
<script
	src="/lib/bootstrap/js/bootstrap.min.js"></script>
<!--<script src="/js/src/base.js"></script>
<script src="/js/src/jquery.dump.js"></script>
<script src="/js/src/prettify.js"></script>
<script src="/js/src/social-in.js"></script>-->
<script src="/js/dest/all-min.js"></script>
<script type="text/javascript">
    function updateMonitoringStatus(id) {
        if (!$("#chk_"+id).is(':checked')) {
            $("#txt_"+id).attr('disabled', 'disabled');
            $(".radio_"+id).attr('disabled', 'disabled');
        } else {
            $("#txt_"+id).removeAttr('disabled');
            $(".radio_"+id).removeAttr('disabled');
        }
    }

    function updatePageDisplayStatus(page_uid, show_archives) {
        var postData = {
            "page_uid" : page_uid
        };
        $.ajax({
            type: "GET",
            url: "api_update_fb_page_displaying.php",
            data: postData,
            dataType: "html",   //expect html to be returned
            success: function(response){
                if (!show_archives && response == '非表示状態を解除する') {
                    $("#element_"+page_uid).css('display', 'none');
                } else {
                    $("#archives_"+page_uid).html(response);
                }
            }

        });
    }
</script>
<?php if (extension_loaded ('newrelic')) {
	echo newrelic_get_browser_timing_footer();
} ?>
</body>
</html>
