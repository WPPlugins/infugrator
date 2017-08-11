(function( $ ) {

	//init tablist
	$(document).on('click', '.tabs-menu li', function(event) {
        event.preventDefault();

        var _this = $(this),
        	tabContent = _this.closest('.tabs-container').find('> .tab > .tab-content'),
        	tab = $('#'+_this.attr('data-target'));

        _this.addClass("current");
        _this.siblings().removeClass("current");
        tabContent.not(tab).hide();
        tab.fadeIn();
    });

})( jQuery );
