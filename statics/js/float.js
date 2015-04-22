/**
 * Created by ds on 2015/4/22.
 */
$(function(){
    var AllHet = $(window).height();
    var fixedTop = (AllHet - 450)/2;

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
        scrollCtr();
    }

    function scrollCtr(){
        var right = $('#izl_rmenu');
        _scrolls(right);

    }

    function _scrolls(obj){
        var sTop = $(window).scrollTop();
        var topPx = sTop+fixedTop;
        obj.stop().animate({ "top": topPx< 800 ? 800 :topPx}, 600);
    }
})

