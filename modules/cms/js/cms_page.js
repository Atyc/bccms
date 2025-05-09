function cms_page_toolbar_title(){

	var page_title = $('#page_title').val();
	
	if (page_title == ''){
		page_title = '[ no title ]';
	}
	
	if (page_title.length > 50){
		page_title = page_title.substr(0, 48) + '..';
	}
	
	$('.cms_page_toolbar_title').html(page_title);

}

function cms_page_save(params){
	
	params = params || {'success':function(){}};

	get_ajax('cms/cms_page_operations', {
		'cms_page_id': $('.cms_page_id').val(),
		'do': 'cms_page_save',
		'language': $('.cms_language_select_current').data('language'),
		'sort': $('.cms_page_sort').val(),
		'title': $('#page_title').val(),
		'slug': $('#page_slug').val(),
		'status': $('.cms_page_status').val(),
		'seo_title': $('#cms_page_seo_title').val(),
		'description': $('.cms_page_description').val(),
		'image': $('.cms_image_input_cms_page_image').val(),
		'video': $('.cms_file_input_cms_page_video').val(),
		'video_id': $('#cms_page_video_id').val(),
		'layout': $('.cms_page_layout').val(),
		'position': $('.cms_page_position').val(),
		'positions': $('.cms_page_positions > select').map((ok, ob) => {return {name:$(ob).attr('name'), value:$(ob).val()}}).get(),
		'success': function(data){
			
			// update possible changes on form
			$('.cms_page_id').val(data.result.cms_page_id)
			$('.cms_page_slug').val(data.result.slug),
			cms_notification('Page saved', 3);
			
			// update url in browser when page has changed
			change_url(_cms_base + 'admin/page/' + $('.cms_page_id').val() + '/');
			
			// update new page panel target
			$('.cms_input_page_panels_add').data('page', $('.cms_page_id').val());
			
			params.success(data);
			
		}
	})

}

function cms_page_delete(){

	get_ajax_panel('cms/cms_popup_yes_no', {}, function(data){
		panels_display_popup(data.result._html, {
			'yes': function(){
				
				var page_id = $('.cms_page_id').val();
				
				// if empty, page doesn't exist in database
				if (page_id > 0){ 
					get_ajax('cms/cms_page_operations', {
						'page_id': page_id,
						'do': 'cms_page_delete',
						'success': function(data){
							window.location.href = _cms_base + 'admin/pages/';
						}
					})
				} else {
					window.location.href = _cms_base + 'admin/pages/';
				}
				
			}
		}); 
	});

}

function cms_page_init(){

	$('.cms_page_save').on('click.cms', function(){
		cms_page_save();
	});
	
	$('.cms_page_delete').on('click.cms', function(){
		cms_page_delete();
	});
	
	cms_page_toolbar_title();
	$('.cms_page_title').on('keyup.cms', cms_page_toolbar_title);
	
	$('.cms_page_panel_delete').on('click.cms', function(){
		var $this = $(this);
		var cms_page_panel_id = $this.data('cms_page_panel_id');
		get_ajax_panel('cms/cms_popup_yes_no', {'text':'Delete block shortcut?'}, function(data){
			panels_display_popup(data.result._html, {
				'yes': function(){
					get_ajax_panel('cms/cms_page_panel_operations', {
						'cms_page_panel_id': cms_page_panel_id,
						'do': 'cms_page_panel_delete' 
					}, function(){
						$this.closest('li').remove();
					})
				}
			}); 
		});
	});
	
	// cms page saves block order automatically
	$('.cms_page_sortable').sortable({
		'stop': function(event, ui){
			// save order
			var block_orders = {};
			$('.cms_page_sortable .block_id').each(function(index, value){
				block_orders[$(this).val()] = index + 1;
			});
			get_ajax('cms/cms_page_operations', {
				'do': 'cms_page_panel_order',
				'orders': block_orders,
				'cms_page_id': $('.cms_page_id').val()
			});
		},
	}).disableSelection();
	
}

function cms_page_resize(){
	
}

$(document).ready(function() {
	
	$(window).on('resize.cms', function(){
		cms_page_resize();
	});

	cms_page_init();

	cms_page_resize();

});
