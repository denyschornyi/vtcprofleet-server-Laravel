$(document).ready(function(){
    /* Sidebar - if active */
	function sidebarIfActive(){
		// $('.list-unstyled ul > li').removeClass('active');
		// $('.list-unstyled ul').removeClass('in');
		// $('.list-unstyled ul').removeAttr('aria-expanded');
        // var url = window.location;
        var url = window.location.href.split('?')[0];
	    var element = $('.list-unstyled ul > li > a').filter(function () {
	        return this.href == url;// || url.href.indexOf(this.href) == 0;
	    });
        element.parent().addClass('active');
        element.parent().parent().attr('aria-expanded', true);
        element.parent().parent().addClass('in');
        element.parent().parent().parent().addClass('active');
        element.parent().parent().parent().children('a').addClass('show');
        element.parent().parent().parent().children('a').attr('aria-expanded', true);
        element.parent().parent().parent().children('a>i').removeClass('fa-plus');
        element.parent().parent().parent().children('a>i').addClass('fa-chevron-right');
        
		$('.list-unstyled li:not(.menu-title)').removeClass('active');
        element = $('.list-unstyled li:not(.menu-title) > a').filter(function() {
            return this.href == url;
        });
        element.parent().addClass('active');
	}
	sidebarIfActive();
});