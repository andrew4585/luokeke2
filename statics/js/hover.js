/**
 * Created by Administrator on 2015/3/13.
 */
    //首页页面的淡入淡出效果
    $(document).ready(function() {
        $(".container2 ul li .rsp").hide();
        $(".container2 ul li .text").hide();
        $(".container2	 ul li").hover(function() {
            $(this).find(".rsp").stop().fadeTo(500, 1.5)
            $(this).find(".text").stop().fadeTo(500,1.5)
            // $(this).find(".text").stop().animate({left:'0'}, {duration: 500})
        }, function() {
            $(this).find(".rsp").stop().fadeTo(500, 0)
            $(this).find(".text").stop().fadeTo(500,0)
            // $(this).find(".text").stop().animate({left:'318'}, {duration: "fast"})
            // $(this).find(".text").animate({left:'-318'}, {duration: 0})
        });
            });
   //首页页面的九宫格淡入淡出效果
    $(function() {
    $(".xl_001 ul li .rsp").hide();
    $(".xl_001	 ul li").hover(function() {
        $(this).find(".rsp").stop().fadeTo(500, 0.5)

    }, function() {
        $(this).find(".rsp").stop().fadeTo(500, 0)

    });
 });
    //婚纱礼服页面的淡入淡出效果
    $(function() {
        $(".xl_001_lf ul li .rsp").hide();
        $(".xl_001_lf ul li .text").hide();
        $(".xl_001_lf ul li").hover(function() {
            var width   = $(this).find(".Imghover").width();
            var top     = $(this).find(".Imghover").height()/2 -40;
            var left    = width/2 -86;
            $(this).find(".text").find("h3").css({"width":width,"margin-top":top});
            $(this).find(".text").find("img").css("padding-left",left);
            $(this).find(".rsp").stop().fadeTo(500, 0.5);
            $(this).find(".text").stop().fadeTo(500, 1.0);
        }, function() {
            $(this).find(".rsp").stop().fadeTo(500, 0);
            $(this).find(".text").stop().fadeTo(500, 0);

        });

});
  //婚纱礼服页面的淡入淡出效果
    $(function() {
        $(".dress_photo ul li .rsp").hide();
        $(".dress_photo ul li .text").hide();
        $(".dress_photo	 ul li").hover(function() {
            $(this).find(".rsp").stop().fadeTo(500, 1.0)
            $(this).find(".text").stop().fadeTo(500, 1.0)

        }, function() {
            $(this).find(".rsp").stop().fadeTo(500, 0)
            $(this).find(".text").stop().fadeTo(500, 0)

        });
});


