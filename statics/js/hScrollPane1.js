(function($){
	$.extend(jQuery.easing,{
		easeOutQuint: function (x, t, b, c, d) {
			return c*((t=t/d-1)*t*t*t*t + 1) + b;
		}
	});
	$.fn.hScrollPane=function(settings){
		settings=$.extend(true,{},$.fn.hScrollPane.defaults,settings);
		this.each(function(){
			var container=$(this),
				mover=container.find(settings.mover),
				c=settings.moverW || mover.width(),
				maxLeft = c-600<0?0:c-600;
			mover.css("width",c);
			mover.stop().css("left","0px");
			mover.bind("touchstart",function(e){
				this._startX = e.pageX || e.originalEvent.touches[0].pageX;
			})
			mover.bind("touchmove",function(e){
				var nowLeft = $(this).offset().left-20;
				var left = (e.pageX || e.originalEvent.touches[0].pageX)-this._startX;
				left = 	nowLeft+left;
				left=left>0?0:(-left>=maxLeft?-maxLeft:left);
				mover.stop().animate({
					left:left			
				},200);
				return false;
			});
			container.unbind();//避免多次初始化时的事件重复绑定;
		
		})
	}
	
	$.fn.hScrollPane.defaults = {
		showArrow:false,
		handleMinWidth:0,
		dragable:true,
		easing:true,
		mousewheel:{bind:true,moveLength:290}
	};
	
})(jQuery);