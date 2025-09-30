var embeddata = 'Data in embedding page';
function embedfunction(testval){
	alert('embedding page function: '+testval);
}

function sendTest(meid, data){
	var target_iframe = document.getElementById(meid);
	var me_host = 'https://navisen.dk'; // jQuery(target_iframe).attr('data-media_server_url');
//	var target = target_iframe.contentWindow;
//	console.log('*********************************** me_host:> '+me_host);
	if(me_host != undefined) target_iframe.contentWindow.postMessage(data, me_host);
}

function sendSize(target){
//	console.log(target);
	var meid = jQuery(target).attr('id');
	sendTest(meid, {
		action: 'resize',
		meid : meid,
		max_width : jQuery(target).parent().width()
		
	});		
}

window.onmessage = function(event){
//	console.log('embedding page received message from '+event.origin);
//	console.log(event.data);
	var me_iframe;
	var me_server_url = '-';
	if(event.data.meid != undefined) {
		me_iframe = jQuery('#'+event.data.meid);
		me_server_url = 'https://navisen.dk'; // me_iframe.attr('data-media_server_url');
	}
//	console.log('***************** me_server_url: '+me_server_url);
	switch (event.origin) { 
		case me_server_url:
//			console.log(event.data);
			var me_iframe_width = me_iframe.width();
			var me_iframe_height = me_iframe.height();
//			console.log(me_iframe_width +' x '+ me_iframe_height);
			var width = Math.min(me_iframe.parent().width(),event.data.width);
			switch(event.data.msg){
				case 'resized':
					me_iframe.width(width);
					me_iframe.height(event.data.height + 10);
					break;
				case 'ready':
					me_iframe.width(width);
					me_iframe.height(event.data.height + 10);
					sendSize(jQuery('#'+event.data.meid));
					break;
			};
			me_iframe_width = me_iframe.width();
			me_iframe_height = me_iframe.height();
//			console.log(me_iframe_width +' x '+ me_iframe_height);
			break;
		default:
			break;
	}

};

window.onresize = function(e){
	jQuery('.embediframe').each(function(t){
		sendSize(jQuery(this));
	});
};

