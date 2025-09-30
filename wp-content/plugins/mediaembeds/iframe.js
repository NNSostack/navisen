var iframedata = 'Data inside the IFrame!';
function iframefunction(testval){
	alert('iframe function: '+testval);
}

function sendTestIF(data){
	var target = parent;
//	console.log('-------------------------------', target);
	var embedding_host = jQuery('#ruc_me_content').attr('data-embedding_host_url');
	target.postMessage(data, embedding_host);
};

window.onmessage = function(event){
//	console.log('iframe received message from '+event.origin);
//	console.log(event.data);
	var content_element = jQuery('#ruc_me_content');
	switch (event.origin) {
		case 'https://storify.com':
		case 'http://storify.com':
			var data = jQuery.parseJSON(event.data);
			switch(data.method){
				case 'resize':
					sendTestIF({
	//				width : jQuery('body').width(),
						msg: 'resized',
						meid: window.name,
						width : content_element.width(),
						height : data.value
					});
			};
			break;

		case 'https://s-static.ak.facebook.com':
			var data = event.data.split('&');
//			console.log(data);
			var arg;
			var args= {};
			for(var i= 0;i < data.length; ++i){
//				console.log(data[i]);
				arg = data[i].split('=');
				args[arg[0]] = arg[1];
			}
			console.log(args);
			if(args.width != undefined && args.height != undefined){
				sendTestIF({
//				width : jQuery('body').width(),
					msg: 'resized',
					meid: window.name,
					width : args.width,
					height : args.height
				});
			};
			
			break;
		case 'https://e.infogram.com':
			if(typeof event.data === 'object' && ('height' in event.data) && event.data.height >= 0){
				var scroll_height = document.body.scrollHeight;
//				console.log('iframeHeight '+ scroll_height);
				var if_height = event.data.height;
//				console.log('iframeHeight '+ if_height);
				if(if_height < scroll_height) break;
				sendTestIF({
					msg: 'ready',
					meid: window.name,
					width : jQuery('body').width(),
					height :  if_height
				});				
			} else if(event.data.lastIndexOf('iframeHeight',0) >= 0){
				var scroll_height = document.body.scrollHeight;
				console.log('iframeHeight '+ scroll_height);
				var if_height = event.data.substring(event.data.lastIndexOf(':')+1);
				console.log('iframeHeight '+ if_height);
				if(if_height < scroll_height) break;
				sendTestIF({
					msg: 'ready',
					meid: window.name,
					width : jQuery('body').width(),
					height :  if_height
				});				
			}
			break;

		
		case 'https://e.infogr.am':
		case 'http://e.infogr.am':
//			console.log('---------------------------------');
			console.log(event);

			if(event.data.lastIndexOf('iframeHeight',0) >= 0){
				var scroll_height = document.body.scrollHeight;
				console.log('iframeHeight '+ scroll_height);
				var if_height = event.data.substring(event.data.lastIndexOf(':')+1);
				console.log('iframeHeight '+ if_height);
				if(if_height < scroll_height) break;
				sendTestIF({
					msg: 'ready',
					meid: window.name,
					width : jQuery('body').width(),
					height :  if_height
				});				
			}
			break;

		default:
			jQuery(window).resize();
			if(event.data.action == 'resize') {
//				content_element.width(event.data.max_width);
				var content = jQuery('#ruc_me_content');
				var ifc = content.children('iframe');
//				console.log(ifc);
				if(ifc.length > 0){
					console.log('xxxxxxxxxxxxx');
					jQuery('body').width(event.data.max_width);
					var aspect_ratio = content.attr('data-aspect_ratio');
					console.log(content);
					if(aspect_ratio != undefined){
						console.log('aspect_ration: '+aspect_ratio);
						content.height(0);
						content.css('padding-bottom',(100.0/aspect_ratio)+'%');
						jQuery(ifc[0]).height('100%');
					}

//					jQuery(ifc[0]).width(event.data.max_width);
//					jQuery(ifc[0]).height('400px');
				}
				sendTestIF({
					msg: 'resized',
					meid: event.data.meid,
					width : jQuery('body').width(),
					height :  jQuery('body').height()
				});
			};
			
			break;
	}
	
};

function sendContentInfo(){
	var content_element = jQuery('#ruc_me_content');
//	console.log('**************************************************');
//	console.log(content_element);
	var me_content_info = {
//			width : content_element.width(),
//			width : jQuery('body').width(),
			msg: 'ready',
			meid: window.name,
			height : Math.max(content_element.height(),400),
			width : content_element.width()
	}
	sendTestIF(me_content_info);
}

console.log('iframe src: ' + location.href);

jQuery(document).ready(function () {
/*
	try{
		console.log('outside value: ' + window.parent.embeddata);
		//window.parent.extestf('Hej from iframe.js');
	} catch (c) {
		console.log('outside value not accessibel: '+c.Message);
	}
*/
	var ifc = jQuery('#ruc_me_content').children('iframe');
	if(ifc.length == 0){
		jQuery('#ruc_me_content').css('display','inline-block');
	}

	var timeout = 100;
	jQuery( "#ruc_me_content" ).contents().each(function() {
        if(this.nodeType === 8){
        	console.log('------------- Comment --------------');
    		console.log(this.data);
    		switch(this.data.trim()){
    			case 'Web Poll Code':
    			case 'late resize':
    				timeout = 2000;
    				break;
    		}
    		
    		
        }
        
      });
	setTimeout("sendContentInfo()",timeout);
	console.log('iframe ready');
});

