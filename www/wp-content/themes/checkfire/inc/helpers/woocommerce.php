<?php

/**
* Add WooCommerce support
*
* @package checkfire
*/
add_action( 'after_setup_theme', 'woocommerce_support' );

if ( ! function_exists( 'woocommerce_support' ) ) {

    /**
    * Declares WooCommerce theme support.
    */
    function woocommerce_support() {
    add_theme_support( 'woocommerce' );

    // Add New Woocommerce 3.0.0 Product Gallery support
    add_theme_support( 'wc-product-gallery-lightbox' );
    add_theme_support( 'wc-product-gallery-zoom' );



    }
}
// Remove each style one by one
/*
add_filter( 'woocommerce_enqueue_styles', 'jk_dequeue_styles' );
function jk_dequeue_styles( $enqueue_styles ) {
	unset( $enqueue_styles['woocommerce-general'] );	// Remove the gloss
	unset( $enqueue_styles['woocommerce-layout'] );		// Remove the layout
	unset( $enqueue_styles['woocommerce-smallscreen'] );	// Remove the smallscreen optimisation
	return $enqueue_styles;
}

// Or just remove them all in one line
add_filter( 'woocommerce_enqueue_styles', '__return_false' );
*/

/*
 * customize the archive product content page
 */

remove_action('woocommerce_shop_loop_item_title', 'woocommerce_template_loop_product_title', 10);
add_action('woocommerce_shop_loop_item_title', 'tbb_woocommerce_template_loop_product_title', 10);

function tbb_woocommerce_template_loop_product_title() {
    global $post;

   $tax = wp_get_post_terms($post->ID,'product_cat');
   foreach ($tax as $term ) {

	   echo  '<h4 class="text-center">'.$term->name.'</h4>';
   }
	//echo  '<h4 class="text-center">'.single_term_title("", false).'</h4>';
	echo '<h3 class="woocommerce-loop-product__title text-center">' . get_the_title() . '</h3>';

}
if (!function_exists('woocommerce_template_loop_add_to_cart')) {
	function woocommerce_template_loop_add_to_cart() {
		global $product;
		if ( ! $product->is_in_stock() || ! $product->is_purchasable() ) return;

		//wc_get_template('loop/add-to-cart.php');
	}
}

/**
 * WooCommerce Extra Feature
 * --------------------------
 *
 * Change number of related products on product page
 * Set your own value for 'posts_per_page'
 *
 */
function woo_related_products_limit() {
	global $product;

	$args['posts_per_page'] = 3;
	return $args;
}
add_filter( 'woocommerce_output_related_products_args', 'jk_related_products_args' );
function jk_related_products_args( $args ) {
	$args['posts_per_page'] = 3; // 4 related products
	$args['columns'] = 3; // arranged in 2 columns
	return $args;
}

/*
 * price
 */
remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_price', 10);
add_action('woocommerce_single_product_summary', 'woocommerce_template_single_price', 20);

/*
 * tabs
 */
add_filter( 'woocommerce_product_tabs', 'woo_new_product_tab' );
function woo_new_product_tab( $tabs ) {

	// Adds the new tab

	$tabs['tech_tab'] = array(
		'title' 	=> __( 'Tech Spec', 'woocommerce' ),
		'priority' 	=> 50,
		'callback' 	=> 'tech_pec_tab_content'
	    );

    $tabs['download_tab'] =	array(
        'title' 	=> __( 'Downloads', 'woocommerce' ),
        'priority' 	=> 50,
        'callback' 	=> 'download_tab_content'
    );



	return $tabs;

}
function tech_pec_tab_content() {

	// The new tab content


	echo theme('tech_spec');

}
function download_tab_content() {

	echo '<p>'.theme('downloads').'</p>';

}
add_filter( 'woocommerce_product_tabs', 'wcs_woo_remove_reviews_tab', 98 );

function wcs_woo_remove_reviews_tab($tabs) {
	unset($tabs['reviews']);
	return $tabs;
}


/*
 * add to cart text
 */

add_filter( 'woocommerce_product_single_add_to_cart_text', 'woo_custom_cart_button_text' );    // 2.1 +

function woo_custom_cart_button_text() {

	return __( 'ADD TO BASKET', 'woocommerce' );

}

/*
 *
 */
remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_meta', 40);







// ajax function for loading posts
add_action( 'wp_ajax_nopriv_tbb_load_more', 'tbb_load_more' );
add_action( 'wp_ajax_tbb_load_more', 'tbb_load_more' );
function tbb_load_more() {

	$paged = $_POST["page"]+1;

	$query = new WP_Query( array(
		'post_type' => 'product',
		'paged' => $paged
	) );
	if( $query->have_posts() ):
		?>

		<?php
        $i = 1;

		while( $query->have_posts() ): $query->the_post(); ?>
            <?php
            if ($i % 4 == 0) {
                echo '<div class="clear"></div></div><div class="products row fun" >';
            }
            ?>

				<?php  wc_get_template_part( 'content', 'product' ); ?>



			<?php
		$i++; endwhile;
		?>
		</div>
		<?php
	endif;
	 wp_reset_postdata();

	die();

}

/*
 * hide price if = 0
 */
add_filter('woocommerce_get_price_html', 'changeFreePriceNotice', 10, 2);

function changeFreePriceNotice($price, $product) {

	if ( $price == wc_price( 0.00 ) ){

		remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart');

		remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart');
		return '';
    }else {
		return $price;
    }


}


remove_filter( 'woocommerce_ajax_loader_url', 'filter_woocommerce_ajax_loader_url', 10, 1 );



function remove_add_to_cart_on_0 ( $purchasable, $product ){
	if( $product->get_price() == 0 )
		$purchasable = false;
	return $purchasable;
}
add_filter( 'woocommerce_is_purchasable', 'remove_add_to_cart_on_0', 10, 2 );

// hide add to cart
remove_action( 'woocommerce_before_shop_loop_item_title', 'woocommerce_template_loop_product_thumbnail', 10);
add_action( 'woocommerce_before_shop_loop_item_title', 'woocommerce_template_loop_product_thumbnail', 10);

if ( ! function_exists( 'woocommerce_template_loop_product_thumbnail' ) ) {
	function woocommerce_template_loop_product_thumbnail() {
		echo woocommerce_get_product_thumbnail();
	}
}
if ( ! function_exists( 'woocommerce_get_product_thumbnail' ) ) {
	function woocommerce_get_product_thumbnail( $size = 'full', $placeholder_width = 0, $placeholder_height = 0  ) {
		global $post, $woocommerce;


		if ( has_post_thumbnail() ) {
			$output = get_the_post_thumbnail( $post->ID, $size );
		} else {
			$output = wc_placeholder_img( $size );
		}

		return $output;
	}
}

// chaning orderby position
remove_action( 'woocommerce_before_shop_loop','woocommerce_catalog_ordering', 30 );
remove_action( 'woocommerce_before_shop_loop','woocommerce_result_count', 20 );
//add_action( 'woocommerce_before_main_content','woocommerce_catalog_ordering', 20 );




function wooFilters(){
	$args = array(
		'orderby' => 'date',
		'order' => $_POST['date']
	);

	if( isset( $_POST['product_cat_filter'] ) )
		$args['tax_query'] = array(
			array(
				// 'taxonomy' => 'product_cat',
				'taxonomy' => $_POST['tax_filter'],
				'field' => 'slug',
				'terms' => $_POST['product_cat_filter'],

			)
		);

	$query = new WP_Query( $args );

	if( $query->have_posts() ) :
		while( $query->have_posts() ): $query->the_post();
			?>
            <div class="woo-prduct-list col-md-4 col-sm-6 col-xs-12 " >
                <a  href="<?php the_permalink() ?>">
                    <img  class="attachment-full size-full wp-post-image" src="<?php echo get_the_post_thumbnail_url() ?>" >
                    <h3 class="woocommerce-loop-product__title text-center"><?php the_title() ?></h3></a>
                <p class="text-center woo-findout-more">
                    <a class="red-button hvr-sweep-to-right text-center" href="	<?php the_permalink() ?>">Find out more</a>
                </p>
            </div>



			<?php
		endwhile;
		wp_reset_postdata();
	else :
		echo 'No posts found';
	endif;

	die();
}


add_action('wp_ajax_productcatfilter', 'wooFilters');
add_action('wp_ajax_nopriv_productcatfilter', 'wooFilters');
