function redirect(url){
	location.href=url;
}

function layer_alert(msg,url){
	layer.open({
	    content: msg,
	    btn: ['OK'],
	    shadeClose: false,
	    yes: function(index){
	    	if(url){
	    		location.href=url
	    	}else{
		    	layer.close(index);
	    	}
	    }
	});
}