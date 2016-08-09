$(document).ready(function () {

    $('#api-info').on('click', function () {
        var res = $('#sample-response');
        if (res.text() == '') {
            $.ajax({
                url: $('#sample-request').text(),
                cache: false,
                dataType: 'JSONP',
                jsonp: 'callback',
                success: function (response) {
                    $('#sample-response').text($.dump(response)).addClass('prettyprint linenums');
                    prettyPrint();
                },
                error: function () {
                }
            });
        }
    });

    $('#frmTwitterAdd').submit(function (e) {
        var submit = true;
        $('input[type=checkbox]').each(function () {
            if (!$('input[type=checkbox]:checked').length) {
                $('#error').removeClass('hide');
                submit = false;
            }
        });
        return submit;
    });

    $('#frmFacebookAdd').submit(function (e) {
        var submit = true;
        $('input[type=checkbox]').each(function () {
            if (!$('input[type=checkbox]:checked').length) {
                $('#error').removeClass('hide');
                submit = false;
            }
        });
        return submit;
    });

//twitter register
    if ('2' == $('#twitter_kind').val()) {
        //$('#twitter_timeline').addClass('hide');
        $('#twitter_keyword').removeClass('hide')
    } else {
        //$('#twitter_timeline').removeClass('hide');
        $('#twitter_keyword').addClass('hide');
    }

    $('#twitter_kind').change(function () {
        if ('2' == $(this).val()) {
            //$('#twitter_timeline').addClass('hide');
            $('#twitter_keyword').removeClass('hide');
        } else {
            //$('#twitter_timeline').removeClass('hide');
            $('#twitter_keyword').addClass('hide');
        }
    });
//#End twitter register

//twitter register with token
    $('#twitter_kind1').change(function () {
        if ('2' == $(this).val()) {
            //$('#twitter_name').addClass('hide');
            $('#twitter_keyword').removeClass('hide');
        } else {
            //$('#twitter_name').removeClass('hide');
            $('#twitter_keyword').addClass('hide');
        }
    });
});

del = function (type, id) {
    switch (type) {
        case 'twitter':
            $('#social_account_id').val(id);
            //$('#frmDelTwitter').submit();
            break;
        case 'facebook':
            $('#social_account_id').val(id);
            //$('#frmDelFB').submit();
            break;
        case 'monipla_twitter_stream':
            $('#stream_id').val(id);
            break;
		case 'monipla_facebook_stream':
            $('#stream_id').val(id);
            break;
        case 'userNgWord':
            $('#user_ng_word_id').val(id);
            break;
        case 'userNgAccount':
            $('#user_ng_twitter_account_id').val(id);
            break;
    }
    $('#delete_modal').modal('show')

};

hide = function (type, id) {
    switch (type) {
        case 'twitter':
            $('#entry_id').val(id);
            $('#frmHideTwitter').submit();
            break;
        case 'facebook':
            $('#entry_id').val(id);
            $('#frmHideFb').submit();
            break;
        case 'rss':
            $('#entry_id').val(id);
            $('#frmHideRss').submit();
            break;
        case 'monipla_twitter':
            $('#entry_id').val(id);
            $('#frmHideMTwitter').submit();
            break;
		case 'monipla_facebook':
            $('#entry_id').val(id);
            $('#frmHideMFacebook').submit();
            break;
    }
};

changeHiddenFlg = function (type, hidden_flg) {
    switch (type) {
        case 'twitter':
            var entryIds = getSelectedIds("twitterCb");
            if (entryIds.length > 0) {
                $('#entry_ids').val(entryIds);
                $('#hidden_flg').val(hidden_flg);
                $('#frmHideTwitterEntries').submit();
            }
            break;
        case 'facebook':
            var entryIds = getSelectedIds("facebookCb");
            if (entryIds.length > 0) {
                $('#entry_ids').val(entryIds);
                $('#hidden_flg').val(hidden_flg);
                $('#frmHideFbEntries').submit();
            }
            break;
        case 'rss':
            var entryIds = getSelectedIds("rssCb");
            if (entryIds.length > 0) {
                $('#entry_ids').val(entryIds);
                $('#hidden_flg').val(hidden_flg);
                $('#frmHideRssEntries').submit();
            }
            break;
        case 'monipla_twitter':
            var entryIds = getSelectedIds("mtwitterCb");
            if (entryIds.length > 0) {
                $('#entry_ids').val(entryIds);
                $('#hidden_flg').val(hidden_flg);
                $('#frmHideMTwitterEntries').submit();
            }
            break;
		case 'monipla_facebook':
            var entryIds = getSelectedIds("mfacebookCb");
            if (entryIds.length > 0) {
                $('#entry_ids').val(entryIds);
                $('#hidden_flg').val(hidden_flg);
                $('#frmHideMFacebookEntries').submit();
            }
            break;
    }
};

triggerCheckboxes = function (cb, cb_class) {
    var checked = cb.checked;
    $("." + cb_class).each(function () {
        $(this).prop('checked', checked);
    });
    if (checked) {
        $('#btn-group-action').show();
    } else {
        $('#btn-group-action').hide();
    }
};

getSelectedIds = function (cb_class) {
    var ids = new Array();
    $("." + cb_class).each(function () {
        if ($(this).is(':checked')) {
            ids[ids.length] = $(this).val();
        }
    });
    return ids;
},

    changeCb = function (cb) {
        if (getSelectedIds($(cb).attr('class')).length > 0) {
            $('#btn-group-action').show();
        } else {
            $('#btn-group-action').hide();
        }
    },

    refreshEntry = function (id, type) {
        $('#refresh_entry_id').val(id);
        switch (type) {
            case 'twitter':
                $('#frmGetEntryTwitter').submit();
                break;
            default:
                $('#frmGetEntryFacebook').submit();
                break;
        }
    };

showDetail = function (elId) {
    $('.hideRss').each(function () {
        $(this).addClass('hide');
    });

    if (el == elId) {
        $('#' + elId).addClass('hide');
        el = '';
    } else {
        el = elId;
        $('#' + elId).removeClass('hide');
    }
};

addNgAccount = function (type, account_data) {

    switch (type) {
        case 'twitter':
            $('#account_data').val(account_data);
            $('#frmAddNgAccount').submit();
            break;
        case 'facebook':
            $('#account_data').val(account_data);
            $('#frmAddNgAccount').submit();
            break;
        case 'monipla_twitter':
            $('#screen_name').val(account_data);
            $('#frmAddNgAccount').submit();
            break;
    }
};

hideComment = function (id) {
    $('#comment_entry_id').val(id);
    $('#frmHideComment').submit();
}

//smooth scroll
$("a[href^='#']").on('click', function (e) {
    e.preventDefault();
    $('html, body').animate({ scrollTop: $(this.hash).offset().top }, 300, 'swing');
});
