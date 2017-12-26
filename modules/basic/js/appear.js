( function( $ ) {
	
    $.fn.basic_appear = function(params) {
    	
    	params = $.extend({'trigger': 50 }, params)

    	var $set = $(this);
    	
    	var basic_appear = function($this){
    		
    		var page_bottom = (window.pageYOffset || document.documentElement.scrollTop) + window.innerHeight;

			if (page_bottom > $this.data('_basic_appear_centre')){
				$this.removeClass('basic_hidden');
			} else {
				$this.addClass('basic_hidden');
			}
    	
    	}
		
		$set.each(function(){

			var $this = $(this);
			$this.data('trigger', params.trigger);
			
			var basic_appear_centre = function($this){
				$this.data('_basic_appear_centre', $this.offset().top + ($this.height() * ($this.data('trigger')/100) ));
			}
			
			basic_appear_centre($this);
			
			// update on resize
			$(window).on('resize.basic_appear_centre', function(){
				basic_appear_centre($this);
			});
			
			$this.addClass('basic_hidden');

			$(window).on('scroll.basic_appear', function(){
				basic_appear($this);
			});
			
			basic_appear($this);
			
			// repeat later, as loading images etc may change heights
			setTimeout(function(){
				basic_appear_centre($this);
				basic_appear($this);
			}, 1000);
			setTimeout(function(){
				basic_appear_centre($this);
				basic_appear($this);
			}, 2000);
			setInterval(function(){
				basic_appear_centre($this);
				basic_appear($this);
			}, 5000);
			
		});

		return $set;

    }

}( jQuery ));
