jQuery(function($){
    footerPosition();
    $(window).resize(function(){
        footerPosition();
    });

    // header menu
    $('header nav a').hover(function(){
        $(this).next('span').stop(true, true).fadeIn(300);
    },function(){
        $(this).next('span').stop(true, true).fadeOut(100);
    });

    // modal adjust
    $('.modal1').before('<div class="modal1BG"></div>');

    // modal colse
    $('[href="#modalCancel"]').click(function(){
        $('.modal1').fadeOut(200, function(){
            $('.modal1BG').fadeOut(200);
        });
        return false;
    });
    $('.modal1BG').click(function(){
        $('.modal1').fadeOut(200, function(){
            $('.modal1BG').fadeOut(200);
        });
        return false;
    });

    // delete modal
    $('[href="#deleteData"]').click(function(){
        $('.modal1BG').fadeIn(200, function(){
            $('.modal1.delete').fadeIn(200);
        });
        return false;
    });

    // input focus select
    $('.focusText1').click(function(){
        $(this).select();
    })

});

function footerPosition(){
    // adjust contents body height
    var winH = $(window).outerHeight();
    var headH = $('header').outerHeight();
    var loginH; // .loginName1's height + margin bottom
    if($('.loginName1')){
        loginH = 29;
    }else{
        loginH = 0;
    }
    var mainH = $('#mainCont').outerHeight() + 100;
    var footH = $('footer').outerHeight();

    var contH = headH + loginH + mainH + footH;
    if(winH > contH){
        $('footer').css({
            'position':'absolute'
        });
    }else{
        $('footer').css({
            'position':'relative'
        })
    }
}