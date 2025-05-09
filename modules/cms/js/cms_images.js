
var cms_images_loading = false;
var cms_images_load_parameters = {};

function cms_images_mark() {
	$('.cms_images_mark').remove();
	$('.cms_images_selected').append('<img class="cms_images_mark" src="' + _cms_base + 'modules/cms/img/green_tick.png">');
}

/*
 * attaches functions when cms images panel contents are changed
 */
function cms_images_activate() {
	
	$('.cms_images_image')
		.off('click.r,mouseenter.r,mouseleave.r')
		.on('click.r', function(){
			$('.cms_images_selected').removeClass('cms_images_selected');
			$(this).addClass('cms_images_selected');
			$('.popup_select').data('value', $(this).data('filename'));
			cms_images_mark();
		})
		.on('mouseenter.r', function(){
			var $that = $(this);
			$('.cms_images_image_delete', this).on('click.r', function(e){
				e.stopPropagation();
				
				var text = 'Are you sure?';
				var usage = $('.cms_images_image_usage', $that).html();
				if (usage == '1'){
					text = text + '<div class="cms_images_warning">This image is in use!</div>' +
							'<div class="cms_images_warning_extra">Deleting this image may cause missing images in the front end.</div>';
				} else if (usage != '0'){
					text = text + '<div class="cms_images_warning">This image is in use at ' + usage + ' places!</div>' +
							'<div class="cms_images_warning_extra">Deleting this image may cause missing images in the front end.</div>';
				}
				
				get_ajax_panel('cms/cms_popup_yes_no', {'text':text}, function(data){
					panels_display_popup(data.result._html, {
						'yes': function(){
							$('.cms_images_image_cell', $that).animate({'opacity':'0'}, 100);
							get_ajax_panel('cms/cms_images_operations', {
								'filename': $that.data('filename'), 
								'do': 'cms_images_delete_by_filename' 
							}, function(){
								var page = $('.cms_images_area').data('page');
								if ($('.cms_images_image').length == 1 && page > 0){
									page = page - 1;
								}
								cms_images_load_images(
										page, 
										$('.cms_images_area').data('limit'), 
										$('.cms_images_area').data('filename')
								);
							})
						}
					}); 
				});
			});
			
			$('.cms_images_image_edit', this).on('click.r', function(e){
				e.stopPropagation();
				get_ajax_panel('cms/cms_image', {'filename': $that.data('filename')}, function(data){
					$('body').append('<div class="cms_image_overlay"></div>')
					$('body').append(data.result._html)
				})
			})
			
		})
		.on('mouseleave.r', function(){
			$('.cms_images_image_delete,.cms_images_image_usage,.cms_images_image_edit', this).off('click.r');
		});
	
	cms_images_mark();
	
	$('.cms_images_search_input').focus();

}

function cms_images_upload(){
	
	var data = new FormData( $('.cms_images_new_image_form').get(0) );
	data.append('panel_id', 'cms/cms_images_upload');
	data.append('category', $('.cms_images_category').val());
	
	cms_images_transfer(data, function(data){
    	
    	$('.cms_images_search_input').val('');

    	// reload images from zero
    	cms_images_load_images('0', $('.cms_images_area').data('limit'), $('.cms_images_area').data('filename'));
		
	});

}

function cms_images_transfer(data, success){
	
	// show overlay
	var load_in_progress = true;
	var percentage = 0;
	var label = '0%';
	setTimeout(function(){
		if (load_in_progress){
			$('.cms_images_content').append('<div class="cms_images_container_upload"><div class="cms_images_container_cell"><div class="cms_images_container_bar">' +
					'<div class="cms_images_container_bg"></div><div class="cms_images_container_label">' + label + '</div></div></div></div>');
		}
	}, 300);
	
	$.ajax( {
		url: _cms_base + 'ajax_api/get_panel',
	    type: 'POST',
	    data: data,
	    processData: false,
	    contentType: false,
	    dataType: 'json',
	    success: function(data){
	    	
	    	$('.cms_images_container_upload').remove();
	    	
	    	// reinit file upload form input
	    	$('.cms_images_new_image').on('change.r', function(){
	    		$('.cms_images_new_image').off('change.r');
	    		cms_images_upload();
	    	});
	    	
	    	load_in_progress = false;

	    	success(data);

	    },
	    xhr: function() {
	        var xhr = new window.XMLHttpRequest();
	
	        xhr.upload.addEventListener('progress', function(evt) {
	        	if (evt.lengthComputable) {
	        	  
	        		var percentComplete = evt.loaded / evt.total;
	        		percentComplete = parseInt(percentComplete * 100);
	        		
        			$('.cms_images_container_bg').css({'width': percentComplete + '%'});
        			percentage = percentComplete;
	        		if (percentComplete < 99){
		        		$('.cms_images_container_label').html(percentComplete + '%');
		        		label = percentComplete + '%';
	        		} else {
		        		$('.cms_images_container_label').html('finishing');
		        		label = 'finishing';
	        		}
	
	        	}
	        }, false);
	
	        return xhr;
	    }
	});
	
}

function cms_images_load_images(page, limit, filename){
	
	cms_images_load_parameters = {
		'page': page,
		'limit': limit,
		'filename': filename,
		'category': $('.cms_images_category').val(),
		'search': $('.cms_images_search_input').val()
	}
	
	var current_page = page;
	
	if (cms_images_loading){
		return false;
	}
	
	cms_images_loading = true;
	
	$('.cms_images_paging_enabled').addClass('cms_images_paging_disabled').removeClass('cms_images_paging_enabled').off('click.cms');
	$('.cms_images_image').css({'opacity':'0.5'});
	
	get_ajax_panel('cms/cms_images_page', cms_images_load_parameters, function(data){
		
		$('.cms_images_area').html(data.result._html).data('page', page);
		
		// update buttons etc - first
		if (parseInt(data.result.cms_images_max_page) >= 1 && parseInt(data.result.cms_images_current) > 0){
			$('.cms_images_paging_first').removeClass('cms_images_paging_disabled').addClass('cms_images_paging_enabled').data('page', '0');
		}
		// previous
		if (parseInt(data.result.cms_images_max_page) >= 1 && parseInt(data.result.cms_images_current) > 0){
			$('.cms_images_paging_previous').removeClass('cms_images_paging_disabled').addClass('cms_images_paging_enabled')
					.data('page', parseInt(data.result.cms_images_current) - 1);
		}
		// next
		if (parseInt(data.result.cms_images_max_page) >= 1 && parseInt(data.result.cms_images_current) < parseInt(data.result.cms_images_max_page)){
			$('.cms_images_paging_next').removeClass('cms_images_paging_disabled').addClass('cms_images_paging_enabled')
					.data('page', parseInt(data.result.cms_images_current) + 1);
		}
		// last
		if (parseInt(data.result.cms_images_max_page) >= 1 && parseInt(data.result.cms_images_current) < parseInt(data.result.cms_images_max_page)){
			$('.cms_images_paging_last').removeClass('cms_images_paging_disabled').addClass('cms_images_paging_enabled')
					.data('page', parseInt(data.result.cms_images_max_page));
		}
		
		$('.cms_images_paging_current').html(parseInt(data.result.cms_images_current) + 1);
		$('.cms_images_paging_total').html(parseInt(data.result.cms_images_max_page) + 1);
		
		$('.cms_images_paging_enabled').off('click.cms').on('click.cms', function(){
			cms_images_load_images($(this).data('page'), $('.cms_images_area').data('limit'), $('.cms_images_area').data('filename'));
		});
		
		// activate functionality
		cms_images_activate();
		
		cms_images_loading = false;
		
		// check if need to reload, limit and selected filename shouldn't change so quickly 
		if (current_page != cms_images_load_parameters.page || $('.cms_images_category').val() != cms_images_load_parameters.category 
				|| $('.cms_images_search_input').val() != cms_images_load_parameters.search){
			
			cms_images_load_images(cms_images_load_parameters.page, limit, filename);
			
		}
		
	});
	
}

$(document).ready(function() {
	
	$('.cms_images_upload').on('click.r', function(){
		$('.cms_images_new_image').click();
	});
	
	/*
	 * manipulates hidden image upload form
	 */
	$('.cms_images_new_image').on('change.r', function(){
		$('.cms_images_new_image').off('change.r');
		cms_images_upload();
	});
	
	// load overlay
	$('.cms_images_container').detach().appendTo('body').css({'display':''});
	setTimeout(function(){
		$('.cms_images_container').css({'opacity':'1'});
	}, 30);
	
	// category select
	$('.cms_images_category').on('change.cms', function(){
		cms_images_load_images('0', $('.cms_images_area').data('limit'), $('.cms_images_area').data('filename'));
	});
	
	// load first page
	cms_images_load_images($('.cms_images_area').data('page'), $('.cms_images_area').data('limit'), $('.cms_images_area').data('filename'));
	
	// init search input
	$('.cms_images_search_input').on('keyup.cms', function(e){
		if (e.which != 37 && e.which != 38 && e.which != 39)
			cms_images_load_images('0', $('.cms_images_area').data('limit'), $('.cms_images_area').data('filename'));
	});
	
	// init keys
	$(document).off('keyup.cms').on('keyup.cms', function(e) {
		
	    switch(e.which) {

	    	case 37: // left
	        	$('.cms_images_paging_previous').click();
	    		e.preventDefault();
	        	return false;
	        break;

	        case 38: // up
	        	$('.cms_images_search_input').val('');
	        	cms_images_load_images('0', $('.cms_images_area').data('limit'), $('.cms_images_area').data('filename'));
	    		e.preventDefault();
	        	return false;
	        break;

	        case 39: // right
	        	$('.cms_images_paging_next').click();
	    		e.preventDefault();
	        	return false;
	        break;

	        default: return true; // exit this handler for other keys
	    }

	});

})
