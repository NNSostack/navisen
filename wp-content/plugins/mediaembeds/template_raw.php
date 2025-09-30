<?php 
$basedir = dirname(__FILE__);

if(!defined('ABSPATH')) die('.');
if(isset($_GET['view']) || isset($_SERVER['HTTP_X_FORWARDED_HOST'])) { 
	switch(@$_GET['view']){
		case 'iframe.css':
			if(file_exists($basedir.'/iframe.css')){
				header('Content-type: text/css');
				readfile($basedir.'/iframe.css');
			}
			exit;
		case 'iframe.js':
			if(file_exists($basedir.'/iframe.js')){
				header('Content-type: application/javascript');
				readfile($basedir.'/iframe.js');
			}
			exit;
		case 'media.json':
				if(file_exists($basedir.'/media.json')){
				header('Content-type: application/json');
				readfile($basedir.'/media.json');
			}
			exit;
		case 'iframe.html':
			break;
		default:
			die();
	}
	
?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8" />
		<title>IFrame test</title>
<!--[if lt IE 9]>
		<script src="<?=plugins_url('/jquery1.min.js',__FILE__);?>"></script>
<![endif]-->
<!--[if gte IE 9]><!-->
		<script src="<?=plugins_url('/jquery.min.js',__FILE__);?>"></script>
<!--<![endif]-->
		<script src="<?=plugins_url('/default.js',__FILE__)?>"></script>
		<script src="<?=plugins_url('/iframe.js',__FILE__)?>"></script>
		<link rel="stylesheet" href="<?=plugins_url('/iframe.css',__FILE__)?>" type="text/css" />
	</head>
	<body>	
<?php
//	echo 'The Content! '; 
	if ( have_posts() ) {
		while ( have_posts() ) {
			the_post();
			$embed_code_ar = get_post_meta($post->ID, 'ruc_embed_code_ar',true);
			$aspect = empty($embed_code_ar) ? '' : " data-aspect_ratio='$embed_code_ar'";
			echo '<div id="ruc_me_content" data-embedding_host="'.$_SERVER['HTTP_X_FORWARDED_HOST'].'"'.$aspect.'>';
			echo html_entity_decode(get_post_meta($post->ID,'ruc_embed_code',true));
/*
			echo '<pre>';
			print_r($post);
			echo '</pre>';
*/
			echo '</div>';
			break;
		}
	}
} else {
?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8" />
		<title>Embedded IFrame test</title>
<!--[if lt IE 9]>
		<script src="<?=plugins_url('/jquery1.min.js',__FILE__);?>"></script>
<![endif]-->
<!--[if gte IE 9]><!-->
		<script src="<?=plugins_url('/jquery.min.js',__FILE__);?>"></script>
<!--<![endif]-->
		<script src="<?=plugins_url('/embed.js',__FILE__)?>"></script>
		<link rel="stylesheet" href="<?=plugins_url('/embed.css',__FILE__)?>" type="text/css" />
	</head>
	<body>
<?php 

	if ( have_posts() ) {
		while ( have_posts() ) {
			the_post();
			echo '<h1>';
			the_title();
			echo '</h1>';
		}
	}
	$me_server_url = get_option('ruc_me_server_url');
	echo "<iframe src='$me_server_url/mediaembeds/$post->post_name/iframe.html' id='meid_$post->ID' name='meid_$post->ID' class='embediframe' data-media_server_url='$me_server_url' seamless style='width:100%;height:300px;border:0;overflow: hidden;' scrolling='no'></iframe>";  // outside value not accessible
	
	if(isset($_SERVER['HTTP_REFERER'])) echo "<div style='margin-top:8px'><a href='{$_SERVER['HTTP_REFERER']}'>Tilbage til artikel</a></div>";
	
}

?>
	</body>
</html>
