jQuery(function() {
	$.datepicker.regional['ja'] = {
		clearText : '消す',
		clearStatus : '',
		closeText : '閉じる',
		closeStatus : '',
		prevText : '戻る',
		prevStatus : '',
		nextText : '次へ',
		nextStatus : '',
		currentText : '今日',
		currentStatus : '',
		monthNames : [ '1月', '2月', '3月', '4月', '5月', '6月', '7月', '8月', '9月',
				'10月', '11月', '12月' ],
		monthNamesShort : [ '1月', '2月', '3月', '4月', '5月', '6月', '7月', '8月',
				'9月', '10月', '11月', '12月' ],
		monthStatus : '',
		yearStatus : '',
		weekHeader : '週.',
		weekStatus : '',
		dayNames : [ '日曜日', '月曜日', '火曜日', '水曜日', '木曜日', '金曜日', '土曜日' ],
		dayNamesShort : [ '日', '月', '火', '水', '木', '金', '土' ],
		dayNamesMin : [ '日', '月', '火', '水', '木', '金', '土' ],
		dayStatus : '',
		dateStatus : '',
		dateFormat : 'yy-mm-dd',
		firstDay : 0,
		initStatus : 'Choisir la date',
		isRTL : false
	};
	$.datepicker.setDefaults($.datepicker.regional['ja']);
	$("#datepicker").datepicker({
		changeMonth : true,
		changeYear : true,
		yearRange : "1950:+0",
		dateFormat : 'yy-mm-dd',
		showButtonPanel : true,
		autoclose: true,
		maxDate : "+0M +0D"
	});
});