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
    $(document).on("click", "#JSPost1", function(e) {
		$("#JSArticle").fadeIn("slow");
		$("a#JSPost1").addClass("current");
        $("#JSBookM").fadeOut("slow");
		$("a#JSPost2").removeClass("current");
	})
    $(document).on("click", "#closebtn", function(e) {
        $("#JSArticle").fadeOut("slow");
		$("a#JSPost1").removeClass("current");
    })
})

$(function(){
    $(document).on("click", "#JSPost2", function(e) {
		$("#JSBookM").fadeIn("slow");
		$("a#JSPost2").addClass("current");
        $("#JSArticle").fadeOut("slow");
		$("a#JSPost1").removeClass("current");
	})
    $(document).on("click", "#closebtn", function(e) {
        $("#JSBookM").fadeOut("slow");
		$("a#JSPost2").removeClass("current");
    })
})