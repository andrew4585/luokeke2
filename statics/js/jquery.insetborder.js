/**
 * Created by Administrator on 2015/4/2.
 */
//全景看店js鼠标滑过样式
(function($) {
	
	$.fn.borderEffect = function(options) {
		
		options = $.extend({
			speed : 150,
			borderWidth : 3,
			borderColor : '#FB9526',
			borderClass : "border-effect",
			zIndex : 100
		}, options);
		
		return this.each(function(i) {

			$(this).hover(
				function() {
					
					$(this).find('.' + options.borderClass).animate({
						"border-width": options.borderWidth + "px",
						"opacity": "1"
					}, {
						"duration": options.speed, 
						"queue": false
					});
				},
				function() {

					$(this).find('.' + options.borderClass).animate({
						"border-width": "0px",
						"opacity": "0"
					}, {
						"duration": options.speed, 
						"queue": false
					});

				}
			);

			if($(this).find('.' + options.borderClass).length <= 0){

				var border = $("<div />", {
					"class": options.borderClass,
					"css"  : {
						"position": "absolute",
						"display": "block",
						"border": "0px solid " + options.borderColor,
						"top": 0,
						"bottom": 0,
						"right": 0,
						"left": 0,
						"opacity": "0",
						"z-index": options.zIndex
					}
				});

				$(this).append(border);
			}

			$(this).css('position', 'relative');
		});

	};
	
})(jQuery);