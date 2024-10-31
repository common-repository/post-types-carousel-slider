/*Ajax function*/
function postcs_getdata(paged, id, ajaxprocessfs) {
	var ajaxprocess  = 0;
	if(ajaxprocessfs === 0) {
		ajaxprocess = ajaxprocessfs;
	}
	if(ajaxprocess == 0) {
		ajaxprocess = 1;
		/*Loading*/
		if(jQuery('#post-cs[data-id="'+id+'"]').hasClass('loading') == false) {
			jQuery('#post-cs[data-id="'+id+'"]').addClass('loading');
			jQuery('#post-cs[data-id="'+id+'"]').append('<div class="loading-box"><span></span></div>');
		}

		var data = {
			'action': 'postcs_getdata',
			'data': {'paged':paged, 'id':id},
		};

		jQuery.post(ajaxurl, data, function(response) {
			if(response == 0) {
				/*No more data*/
				if(jQuery('#post-cs').attr('data-il') == 1) {
					if(jQuery('#post-cs[data-id="'+id+'"] .nodata').length < 1) {
						jQuery('#post-cs[data-id="'+id+'"]').prepend('<div class="nodata">No more data...</div>');
						setTimeout(function() {
							jQuery('#post-cs[data-id="'+id+'"] .nodata').remove();
						}, 1000);
					}
				} else {
					ajaxprocess = 0;
					postcs_getdata(1, id);
					return false;
				}
				/*Remove loading*/
				jQuery('#post-cs[data-id="'+id+'"]').removeClass('loading');
				jQuery('#post-cs[data-id="'+id+'"] .loading-box').remove();
			} else {
				jQuery('#post-cs[data-id="'+id+'"]').html(response).removeClass('loading');
				var min_height = jQuery('#post-cs[data-id="'+id+'"]').css('min-height');
				min_height = min_height.replace('px', '');
				min_height = parseInt(min_height, 10);
				setTimeout(function() {
					var outer_height = jQuery('#post-cs[data-id="'+id+'"]').outerHeight();
					if(outer_height > min_height) {
						jQuery('#post-cs[data-id="'+id+'"]').css('min-height',outer_height);
					}				
				}, 1000);
				jQuery('#post-cs[data-id="'+id+'"] .loading-box').remove();
				if(jQuery('#post-cs[data-id="'+id+'"] .ps-prev').attr('data-paged')) {
					jQuery('#post-cs[data-id="'+id+'"] .ps-prev').show();
				} else {
					if(jQuery('#post-cs').attr('data-il') == 1) {
						jQuery('#post-cs[data-id="'+id+'"] .ps-prev').hide();
					}					
				}
			}
			ajaxprocess = 0;
		});
	}
}

/*If #post-cs exist*/
if(jQuery('div#post-cs').length > 0) {
	jQuery('div#post-cs').each(function() {
		var get_id = jQuery(this).attr('data-id');
		/*Get slider data*/
		postcs_getdata(1, get_id, 0);
	});
}

/*Next, Prev click*/
jQuery(document).on( 'click', '#post-cs .ps-prev, #post-cs .ps-next, #post-cs .ps-pagi a', function() {
	var get_paged = jQuery(this).attr('data-paged');
	var get_id = jQuery(this).closest('#post-cs').attr('data-id');
	if(jQuery(this).hasClass('ps-prev')) {
		if(!get_paged) {
			get_paged = jQuery(this).attr('data-total');
		}
	}
	/*Get slider data*/
  	postcs_getdata(get_paged, get_id);
});

jQuery('#post-cs').each(function() {
	/*Mousewheel JS*/
	var _this = jQuery(this);
	if(_this.attr('data-mw') == 1) {
		/*Stop default scroll*/
		_this.on( 'mousewheel DOMMouseScroll', function (e) { 
			var e0 = e.originalEvent;
			var delta = e0.wheelDelta || -e0.detail;
			this.scrollTop += ( delta < 0 ? 1 : -1 ) * 30;
			e.preventDefault();  
		});
		_this.on( 'mousewheel', function(event) {
			if(event.deltaY == 1) {
		    	_this.find('.ps-prev').trigger('click');
		    } else {
		    	_this.find('.ps-next').trigger('click');
		    }
		});
	}
	/*Touch Swipe JS*/
	if(_this.attr('data-ts') == 1) {
		jQuery(function() {
			_this.swipe( {
				swipe:function(event, direction, distance, duration, fingerCount, fingerData) {
					if(direction == 'left') {
						_this.find('.ps-next').trigger('click');
					} else if(direction == 'right') {
						_this.find('.ps-prev').trigger('click');
					}
				}
			});
		});
	}
});

/*wp-dmin*/
/*Design Option : Add class on click*/
if(jQuery('.design-option label').length > 0) {
	jQuery('.design-option label').on('click', function() {
		jQuery('.design-option label').removeClass('checked');
		jQuery(this).addClass('checked');
	});
}