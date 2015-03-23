$(function(){
	var tophtml="<div id=\"izl_rmenu\" class=\"izl-rmenu\" style=\"top: 800px\"><a href=\"tencent://Message/\" class=\"btn btn-qq\"></a><div class=\"btn btn-wo\"><img class=\"pic\" src=\"img/weibo.jpg\" onclick=\"window.location.href=\'http://%77%77%77%2e%73%75%63%61%69%6a%69%61%79%75%61%6e%2e%63%6f%6d\'\"/></div><div class=\"btn btn-wx\"><img class=\"pic\" src=\"img/weixin.jpg\" onclick=\"window.location.href=\'http://%77%77%77%2e%73%75%63%61%69%6a%69%61%79%75%61%6e%2e%63%6f%6d\'\"/></div><div class=\"btn btn-phone\"><div class=\"phone\">4000-999-136</div></div><div class=\"btn btn-top\"></div></div>";
	$("#left").html(tophtml);
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
		//$(this).find(".btn-top").click(function(){
		//	$("html, body").animate({
		//		"scroll-top":0
		//	},"fast");
		//});

	});
	var AllHet = $(window).height();
	var mainHet= $('#izl_rmenu').height();
	var fixedTop = (AllHet - mainHet)/2
	$('#izl_rmenu').css({top:fixedTop+'px'});
	$(window).scroll(function(){
		var sTop = $(window).scrollTop();
		fl = $('#float01').offset().top;
		f2 = $('#float02').offset().top;
		f3 = $('#float03').offset().top;
		f4 = $('#float04').offset().top;
		f5 = $('#float05').offset().top;
		f6 = $('#float06').offset().top;
		var topPx = sTop+fixedTop
		$('#izl_rmenu').stop().animate({ "top": ($(window).height() + $(window).scrollTop() - ($(window).height() + $(".float").height())/2) < 800 ? 800 :
		$(window).height() + $(window).scrollTop() - ($(window).height() + $(".float").height())/1.5
		}, 600);


	});

});