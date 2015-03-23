
$(document).ready(function(){


	$('.huan_cen:eq(0)').css({'display':'block'});
	$(".huan_title div:eq(0)").addClass("huan_bt_hv");
	$(".huan_title div").mouseover(function()
	{

		$(".huan_title div").removeClass("huan_bt_hv");
		$(this).addClass("huan_bt_hv");
	});


	$('.huan_bt').mouseover(
		function (){


		$(this).parents().children('.huan_cen').css({'display':'none'});
		var cc = $(this).attr('id');


		var num = cc.split('_');
		$('#level_'+num[1]).css({'display':'block'});

	}
	);

});
function star_show(a)
{

}






















//$(document).ready(function(){
//
//			$("#star dl:eq(0)").find('dt').css({'color':'#aa0000','font-weight':'bold','margin-top':'19px'});
//			$("#star dl:eq(0)").find('dd').css({'background':'url(img/ksbd_bb.gif) center no-repeat','margin-top':'5px'});
//			$('.ksbd_cen:eq(0)').css({'display':'block'});
//			$("#star dl").mouseover(function()
//			{
//				$("#star dl").find('dt').css({'color':'#555','font-weight':'normal','margin-top':'24px'});
//				$("#star dl").find('dd').css({'background':'url(img/ksbd_b.gif) center no-repeat','margin-top':'0px'});
//				$(this).find('dt').css({'color':'#aa0000','font-weight':'bold','margin-top':'19px'});
//				$(this).find('dd').css({'background':'url(img/ksbd_bb.gif) center no-repeat','margin-top':'5px'});
//			});
//
//	
//	$('.px1').mouseover(
//		function (){
//
//		
//		$(this).parents().children('.ksbd_cen').css({'display':'none'});
//		var cc = $(this).attr('id');
//
//		
//		var num = cc.split('_');
//		$('#level_'+num[1]).css({'display':'block'});
//		
//	}	
//	);
//
//	
//
//
//});
//function star_show(a)
//{
//
//}