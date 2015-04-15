$(function(){
    var AllHet = $(window).height();
    var fixedTop = (AllHet - 450)/2;
    
    
    $('div.floatCtro p').click(function(){
        var ind = $('div.floatCtro p').index(this)+1;
        var topVal = $('#float0'+ind).offset().top;
        $('body,html').animate({scrollTop:topVal},1000);
    })
    
    $('div.floatCtro a').click(function(){
        $('body,html').animate({scrollTop:0},1000);
    })
    
     $("#izl_rmenu").each(function(){
        $(this).find(".btn-wo").mouseenter(function(){
            $(this).find(".pic").fadeIn("fast");
        });
        $(this).find(".btn-wo").mouseleave(function(){
            $(this).find(".pic").fadeOut("fast");
        });
        $(this).find(".btn-wx").mouseenter(function(){
            $(this).find(".pic").fadeIn("fast");
        });
        $(this).find(".btn-wx").mouseleave(function(){
            $(this).find(".pic").fadeOut("fast");
        });
        $(this).find(".btn-phone").mouseenter(function(){
            $(this).find(".phone").fadeIn("fast");
        });
        $(this).find(".btn-phone").mouseleave(function(){
            $(this).find(".phone").fadeOut("fast");
        });
    });
    
    $(window).scroll(scrollCtr);
  //初始化
    scroll_init();
    
    function scroll_init(){
    	$('#izl_rmenu').css({top:fixedTop+'px'});
    	$('div.floatCtro').css({top:fixedTop+'px'});
    	scrollCtr();
    }
    
    function scrollCtr(){
    	var right = $('#izl_rmenu');
    	var left  = $('div.floatCtro');
    	leftFloat();
    	_scrolls(right);
    	_scrolls(left);
    }
    
    function leftFloat(){
    	var f1,f2,f3,f4,f5,f6,bck;
        var fixRight = $('div.floatCtro p');
        var blackTop = $('div.floatCtro a')
        var sTop = $(window).scrollTop();
        fl = $('#float01').offset().top;
        f2 = $('#float02').offset().top;
        f3 = $('#float03').offset().top;
        f4 = $('#float04').offset().top;
        f5 = $('#float05').offset().top;
        f6 = $('#float06').offset().top;

        if(sTop>=fl){
            fixRight.eq(0).addClass('cur').siblings().removeClass('cur');
        }
        if(sTop>=f2-100){
            fixRight.eq(1).addClass('cur').siblings().removeClass('cur');
        }
        if(sTop>=f3-100){
            fixRight.eq(2).addClass('cur').siblings().removeClass('cur');
        }
        if(sTop>=f4-100){
            fixRight.eq(3).addClass('cur').siblings().removeClass('cur');
        }
        if(sTop>=f5-100){
            fixRight.eq(4).addClass('cur').siblings().removeClass('cur');
        }
        if(sTop>=f6-100){
            fixRight.eq(5).addClass('cur').siblings().removeClass('cur');
        }
    }
    
    function _scrolls(obj){
    	var sTop = $(window).scrollTop();
    	var topPx = sTop+fixedTop;
    	obj.stop().animate({ "top": topPx< 800 ? 800 :topPx}, 600);
    }
})

