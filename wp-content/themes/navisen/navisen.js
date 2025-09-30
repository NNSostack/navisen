/*
  jQuery(function() {
    jQuery( "#sortable" ).sortable();
    jQuery( "#sortable" ).disableSelection();
  });
*/
function showPostInfo(content){
	tb_show("","#TB_inline?width=400&height=200&inlineId=navisen_tb_list");
//	tb_init();
	jQuery('#TB_ajaxContent').html(content);
}

function showListContent(content){
	tb_show("","#TB_inline?width=600&height=500&inlineId=navisen_tb_list");
//	tb_init();
	jQuery('#TB_ajaxContent').html(content);
	jQuery('div.featuredpost p.postmeta').hide();
	jQuery( "#postlist" ).sortable();
    jQuery( "#postlist" ).disableSelection();
}

function add2list(postId, listId){
	console.log('add2list: '+postId+' '+listId);
	console.log(Navisen.ajaxurl);

	jQuery.post(
			Navisen.ajaxurl,
			{
				action : 'navisen_updatelist',
				postId : postId,
				listId : listId,
				update : 'add',
				updateListNonce : Navisen.updateListNonce
			},
			showListContent 
	);
}

function removeFromList(postId, listId){
	jQuery.post(
			Navisen.ajaxurl,
			{
				action : 'navisen_updatelist',
				postId : postId,
				listId : listId,
				update : 'remove', 
				updateListNonce : Navisen.updateListNonce
			},
			showListContent 
	);
	
}

function reorderList(postId, listId){ 
	items = '';
	jQuery('#postlist li').each(function(){
		items = items + jQuery(this).attr('postid') + ',';
	});
	jQuery.post(
			Navisen.ajaxurl,
			{
				action : 'navisen_updatelist',
				postId : postId,
				listId : listId,
				update : 'reorder',
				list   : items,
				updateListNonce : Navisen.updateListNonce
			},
			showListContent 
	);
	
}

function showList(listId){
	jQuery.post(
			Navisen.ajaxurl,
			{
				action : 'navisen_updatelist',
				postId : 0,
				listId : listId,
				updateListNonce : Navisen.updateListNonce
			},
			showListContent 
	);
	
}

function showInfo(postId){
	jQuery.post(
			Navisen.ajaxurl,
			{
				action : 'navisen_showinfo',
				postId : postId,
				updateListNonce : Navisen.updateListNonce
			},
			showPostInfo
	);
	
}

/*
jQuery(document).ready(function(){
	jQuery('#featured-slider .fea-slides').cycle({delay: 100000});
});
*/