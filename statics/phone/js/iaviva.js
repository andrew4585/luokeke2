$(document).ready(function(){
$(".daydaymenu ins").click(function(){
  $(".Calendar").slideToggle(1000,function(){
	   if($(".Calendar").is(":visible")){
  $(".daydaymenu ins").html("收起日历").addClass("hover");
  }else{
	$(".daydaymenu ins").html("查看日历选择日期").removeClass("hover"); 
	  }});
 });	



$(".foot_nav dt i").click(function(){
   var i=$(".foot_nav dt i").index($(this));
  $(".foot_nav dd").eq(i).slideToggle(500,function(){
	 if($(".foot_nav dd").eq(i).is(":visible")){
    $(".foot_nav dt a").eq(i).addClass("hover");
   $(".foot_nav dt i").eq(i).find("img").transition({ rotate:'45deg'});
  }else{
   $(".foot_nav dt a").eq(i).removeClass("hover");
   $(".foot_nav dt i").eq(i).find("img").transition({ rotate:'0deg'});
	  }
 });

 });


if($("#banner li").size()>1){
$("#rundivNav li").eq(0).addClass("active");
var elem = document.getElementById('banner');window.rundiv = Rundiv(elem, {hasNav:true, startSlide:1,auto:5000,continuous:true,disableScroll:false,stopPropagation:true,transitionEnd:function(){
	/*alert(Rundiv.getPage());
	$(".smallbox li").removeClass("hover");
	$(".smallbox li").eq(getPage()).addClass("hover");*/
	}
	});
	rundiv.linkNav("rundivNav");  
}else{
$(".smallbox").hide();
}
 
 
 
$('.SideMenu').transition({"transform-origin":"top right",rotate:-90,opacity:0.2},0);
$(".SideMenu h2 i img").transition({rotate:90},0);

$(".SideMenu_bot ins").click(function(){
$(".SideMenu_bot").transition({y:400},1000,"easeInOutBack");
$(".SideMenu_bg").fadeIn(1000);
$('.SideMenu').transition({rotate:0,opacity:1},1500,"easeInOutBack",function(){
	$(".SideMenu h2 i img").transition({rotate:0},1000);
	});
});

$(".SideMenu h2 i").click(function(){
$(".SideMenu h2 i img").transition({rotate:180},1000);
$('.SideMenu').transition({rotate:-90,opacity:0.2},1000,"easeInOutBack",function(){
	    $(".SideMenu_bot").transition({y:0},1000,"easeInOutBack",function(){});
	});
$('.SideMenu_bg').fadeOut(2000);
});

$(".SideMenu_bot tt").click(function(){
	$("body,html").animate({scrollTop:0},1000);
	});

$(window).scroll(function(){
if ($(window).scrollTop()>500){
 $(".SideMenu_bot").css({height:154});
}else{
$(".SideMenu_bot").css({height:77});
	}
});

$("a.Series").click(function(){
	$("body,html").animate({scrollTop:$(".wuhan_list_menu").offset().top},1000);
	});


 
});