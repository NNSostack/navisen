<?php
	/*
		Template Name: Authors
	*/
get_header(); ?>

	<div class="row archive">
		<main class="col-xs-12 col-md-8 col-sm-8 post-wrapper" role="main" itemprop="mainContentOfPage" itemscope="itemscope" itemtype="http://schema.org/Blog">
			<div class="article-wrapper archive-default">
				<div class="row">
					<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
						<p class="gabfire_breadcrumb"><?php gabfire_breadcrumb(); ?></p>
					</div>
				</div>
				
			   <?php
				$authors=get_users();
				$i=0;
				//get all users list
				foreach($authors as $author){
					$authorList[$i]['id']=$author->data->ID;
					$authorList[$i]['name']=$author->data->display_name;
					$i++;
				}
				?>
				<style>
					.authors_style1 {
						float:left;
						width:48%;
						margin: 0 0 20px;
						padding:0 0 20px;
						border-bottom:1px dotted #ddd;
						overflow:hidden;			
					}
					
					.authors_style1 .authors_top {
						
						margin:0 0 20px;
						overflow:hidden;
					}
					.authors_style1:nth-child(odd) {margin-right:2%;}
					.authors_style1:nth-child(even) {margin-left:2%}
					.authors_style1 .authors_top .avatar {float:left;margin-right:15px;
					-webkit-border-radius: 5px;
					-moz-border-radius: 5px;
					border-radius: 5px;					
					}
					.authors_style1 .authors_name {float:left;max-width:200px;font-size:16px;line-height:22px;text-transform:uppercase;margin-top:5px;}
					.authors_style1 .authors_prev {float:right;max-width:70px;text-align:center;font-size:13px;line-height:17px;background:#f5f7f9;padding:5px;color:#222}
					.authors_style1 .authors_prev:hover {background:#f1f3f5}
					.authors_style1 .entry-title {font-size:19px;display;block;margin:0 0 10px;}
				</style>
				
				<ul>
					<?php 
					foreach($authorList as $author)
					{
						$args=array(
								'showposts'=>1,
								'author'=>$author['id'],
								'caller_get_posts'=>1
							   );
						$query = new WP_Query($args);
						if($query->have_posts() )
						{
							while ($query->have_posts())
							{
								$query->the_post();
								?>
								
								<div class="authors_style1">
									<div class="authors_top">
										<?php echo get_avatar(get_the_author_meta('ID'), 50); ?>
										<h2 class="authors_name"><?php echo $author['name']; ?></h2>
										<p><?php echo get_the_date(); ?></p>
										<a class="authors_prev" href="<?php echo get_author_posts_url($author['id']); ?>"><?php _e('Prev. Entries','gabfire'); ?></a>
									</div>
									
									<h2 class="entry-title"><a href="<?php echo get_permalink(); ?>"> <?php echo get_the_title(); ?> </a></h2>
									<?php echo wp_trim_words( get_the_content(), 15, '...' ); ?>
								</div>
								
							

								<?php
							}
							wp_reset_postdata();
						}
					}
					?>
				</ul>
			</div>
		</main><!-- col-md-8 -->
		
		<?php get_sidebar(); ?>
	</div>	
	
<?php get_footer(); ?>