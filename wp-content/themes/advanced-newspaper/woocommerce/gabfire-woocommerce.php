<?php
	add_theme_support( 'woocommerce' );

	/* Call gabfire custom css */
	if ( ! function_exists( 'gabfire_woocommerce_css' ) ) {
		function gabfire_woocommerce_css() {
			wp_enqueue_style('gabfire-woocommerce', get_template_directory_uri() .'/css/woocommerce.css',array( 'gabfire-style' ));
		}
		add_action( 'wp_enqueue_scripts', 'gabfire_woocommerce_css' );
	}
	
	/**
	* WooCommerce Extra Feature
	* --------------------------
	* Change number of related products on product page
	* Set your own value for 'posts_per_page'
	*
	*/
	add_filter( 'woocommerce_output_related_products_args', 'jk_related_products_args' );
	function jk_related_products_args( $args ) {
		$args['posts_per_page'] = 4; // 4 related products
		$args['columns'] = 4; // arranged in 2 columns
		return $args;
	} 
	
	/* Customize WooCommerce Breadcrumb */
	add_filter( 'woocommerce_breadcrumb_defaults', 'jk_woocommerce_breadcrumbs' );
	function jk_woocommerce_breadcrumbs() {
		return array(
			'delimiter' => ' <span class="gabfire_bc_separator">&raquo;</span> ',
			'wrap_before' => '<nav class="gabfire_breadcrumb" itemprop="breadcrumb">',
			'wrap_after' => '</nav>',
			'before' => '',
			'after' => '',
			'home' => _x( 'Home', 'breadcrumb', 'gabfire' ),
		);
	} 	

	/*Edit number of products per page and show 8 only */
	add_filter( 'loop_shop_per_page', create_function( '$cols', 'return 9;' ), 20 );	
	
	/* Hooks */
	remove_action( 'woocommerce_sidebar', 'woocommerce_get_sidebar', 10);
	remove_action( 'woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10);
	remove_action( 'woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10);	
	remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_upsell_display', 15 );
	
	add_action('woocommerce_after_add_to_cart_button', 'gabfire_product_social_links', 10);
	add_action('woocommerce_before_shop_loop', 'gabfire_archive_navigation', 10 );

	function gabfire_archive_navigation() { ?>
		<nav class="products-navigation" itemscope="itemscope" itemtype="http://schema.org/SiteNavigationElement" role="navigation">
			<ul class="productsnav responsive_menu">
				<?php
				if ( has_nav_menu( 'shop' ) ) {
					wp_nav_menu( array('theme_location' => 'shop', 'container' => false, 'items_wrap' => '%3$s'));
				} else {
					wp_list_categories('taxonomy=product_cat&title_li=');
				}
				?>
			</ul>
		</nav>	
	<?php }
	
	function gabfire_product_social_links() { ?>
		
		<div class="woocommerce-sharebtn-wrapper">
			<p><strong>Share this product</strong></p>
			<a href="https://twitter.com/home?status=<?php echo get_the_title() . ' => ' . get_the_permalink(); ?>" class="btn btn-sm btn-social btn-twitter">
			  <i class="fa fa-twitter"></i>
			  <?php _e('Twitter', 'gabfire'); ?>
			</a>
			
			<a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo get_the_permalink(); ?>" class="btn btn-sm btn-social btn-facebook">
			  <i class="fa fa-facebook"></i>
			  <?php _e('Facebook', 'gabfire'); ?>
			</a>

			<a href="https://plus.google.com/share?url=<?php echo get_the_permalink(); ?>" class="btn btn-sm btn-social btn-google-plus">
			  <i class="fa fa-google-plus"></i>
			  <?php _e('Google+', 'gabfire'); ?>
			</a>
			
			<a href="https://pinterest.com/pin/create/button/?url=&media=<?php echo get_the_permalink(); ?>&description=<?php echo get_the_excerpt(); ?>" class="btn btn-sm btn-social btn-pinterest">
			  <i class="fa fa-pinterest"></i>
			  <?php _e('Pinterest', 'gabfire'); ?>
			</a>			

		</div>
	<?php }
	
	if (!function_exists('gabfire_before_loop')) {
		add_action('woocommerce_before_shop_loop','gabfire_before_loop', 50);
		function gabfire_before_loop() {
			echo '<div class="row">';
			echo '<main id="content" class="col-xs-12 col-md-9 col-sm-12 woo-font" role="main">';
		}
	}
	
	if (!function_exists('gabfire_after_loop')) {
		add_action('woocommerce_after_shop_loop','gabfire_after_loop', 50);
		function gabfire_after_loop() {
			echo "</main>\n
					<div class='col-md-3 col-sm-12 col-xs-12 sidebar-wrapper'>\n
						<div class='sidebar' role='complementary'>\n";
							gabfire_dynamic_sidebar('shop'); echo "
						</div><!-- .sidebar inner -->\n
					</div><!-- .sidebar wrapper -->\n
				</div><!-- .row -->\n";
		}	
	}
	
	
	if (!function_exists('loop_columns')) {
		add_filter('loop_shop_columns', 'loop_columns');
		
		function loop_columns() {
			return 3; // 3 products per row
		}
	}
	
	/* Add out of stock text to shop archive */
	if (!function_exists('gabfire_outofstok_text')) {
		add_action('woocommerce_before_shop_loop_item','gabfire_outofstok_text', 1);
		function gabfire_outofstok_text() {
			global $product;
			if (!$product->is_in_stock()) {
				echo "<p class='horizontally-center'><span class='notinstock'>" . __('Out of Stock', 'gabfire') . "</span></p>";
			}
		}
	} 	
	
	/* Custom Add To Cart Messages */
	add_filter( 'wc_add_to_cart_message', 'custom_add_to_cart_message' );
	function custom_add_to_cart_message() {
		global $woocommerce;

			$return_to  = get_permalink(woocommerce_get_page_id('shop'));
			$message = sprintf('<a href="%s" class="button wc-forwards">%s</a>', $return_to, __('Continue Shopping', 'gabfire'));
			$message .= sprintf('<a href="%s" class="button spacetoright">%s</a> %s', get_permalink(woocommerce_get_page_id('cart')), __('View Cart', 'gabfire'), __('Product successfully added to your cart.', 'gabfire') );
			
		return $message;
	}
	
	/* Add a clearfix after sorting on category page to display 1 product nicely */
	if (!function_exists('gabfire_clearright_beforeproducts')) {
		
		function gabfire_clearright_beforeproducts() {
			echo '<div class="clearfix"></div>';
		}	
		add_action('woocommerce_before_shop_loop', 'gabfire_clearright_beforeproducts', 30 );
	}