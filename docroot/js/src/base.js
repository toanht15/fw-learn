(function () {

    var socialinFn = window.socialinFn || {};

    $.extend(socialinFn, {

        version: 0.1,
        context: null,

        init: function () {

            $("#sidebar li a").each(function () {
                var current = $(this);
                var parent = current.parent();

                parent.removeClass("active");

                if (location.pathname == current.attr("href")) {
                    parent.addClass("active");
                }
            });
        }
    });

    window.socialinFn = socialinFn;

})();

$(document).ready(function () {
    socialinFn.init();
});
