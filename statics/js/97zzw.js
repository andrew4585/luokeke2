jQuery(document).ready(function($){
 $('#roll_top').click(function(){$('html,body').animate({scrollTop: '0px'}, 800);}); 
 $('#ct').click(function(){$('html,body').animate({scrollTop:$('.ct').offset().top}, 800);});
 $('#fall').click(function(){$('html,body').animate({scrollTop:$('.footer,.footer_a').offset().top}, 800);});
});
/*代码整理：www.97zzw.com - 97站长网*/