$(function(){
		$('a[href^=#]').click(function() {
			var speed = 600;
			var href= $(this).attr("href");
			var target = $(href == "#" || href == "" ? 'html' : href);
			var position = target.offset().top;
			$($.browser.safari ? 'body' : 'html').animate({scrollTop:position}, speed, 'swing');
			return false;
		});
	});
	
    $(".totop").hide();
		$(function () {
			$(window).scroll(function () {
				if ($(this).scrollTop() > 150) {
					$('.totop').fadeIn("slow");
				} else {
					$('.totop').fadeOut("slow");
				}
        });
});
		
$(function(){
	$("#JSPost1").click(function() {
    	$("#JSArticle").fadeIn("slow");
		$("a#JSPost1").addClass("current");
        $("#JSBookM").fadeOut("slow");
		$("a#JSPost2").removeClass("current");
	})
	$(".closebtn").click(function() {
        $("#JSArticle").fadeOut("slow");
		$("a#JSPost1").removeClass("current");
    })
})

$(function(){
	$("#JSPost2").click(function() {
		$("#JSBookM").fadeIn("slow");
		$("a#JSPost2").addClass("current");
        $("#JSArticle").fadeOut("slow");
		$("a#JSPost1").removeClass("current");
	})
	$(".closebtn").click(function() {
        $("#JSBookM").fadeOut("slow");
		$("a#JSPost2").removeClass("current");
    })
})