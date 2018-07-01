<?php
/**
 * The Template for displaying product archives, including the main shop page which is a post type archive
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/archive-product.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see 	    https://docs.woocommerce.com/document/template-structure/
 * @author 		WooThemes
 * @package 	WooCommerce/Templates
 * @version     2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

get_header( 'shop' );



?>
<?php
$t = $theme::theme_terms('', 'product_cat' );
var_dump($t);

?>

	<?php
		/**
		 * woocommerce_before_main_content hook.
		 *
		 * @hooked woocommerce_output_content_wrapper - 10 (outputs opening divs for the content)
		 * @hooked woocommerce_breadcrumb - 20
		 * @hooked WC_Structured_Data::generate_website_data() - 30
		 */
		do_action( 'woocommerce_before_main_content' );
	?>

    <header class="woocommerce-products-header">

		<?php if ( apply_filters( 'woocommerce_show_page_title', true ) ) :   ?>
            <div class="row">
                <div class="col-sm-7 col-xs-12">
                    <h1 class="woocommerce-products-header__title page-title"><?php woocommerce_page_title(); ?></h1>
                    <div class="title-separator"></div>
                </div>
                <div class="col-sm-5 col-xs-12">
	                <?php // dynamic_sidebar( 'Home right sidebar' ); ?>

                    <div class="woo_product_filter">
                        <form action="<?php echo site_url() ?>/wp-admin/admin-ajax.php" method="POST" id="catFilter">
			                <?php

			                $parent = get_term_by('name', 'extinguishers', 'product_cat');

			                if( $terms = get_term_children($parent->term_id, 'product_cat' )  ) :
                           
				                echo '<select id="selectProductCat" name="product_cat_filter" ><option>Filter by <strong>Agent</strong></option>';
				                foreach ( $terms as $term ) :
					                $termn = get_term_by( 'id', $term , 'product_cat'  );
				                $disbaled = ($termn->count == 0)? 'disabled' : '';
					                echo '<option value="' . $termn->slug . '"'.$disbaled .' >' .$termn->name.' ('.$termn->count. ')</option>';
				                endforeach;
				                echo '</select>';
			                endif;
			                ?>
                            <input type="hidden" name="action" value="productcatfilter">


                            <input type="hidden" name="tax_filter" value="product_cat">

                        </form>
                        <i class="fa fa-chevron-down" aria-hidden="true"></i>

                    </div>

                    <div class="woo_product_filter">
                        <form action="<?php echo site_url() ?>/wp-admin/admin-ajax.php" method="POST" id="brandFilter">
			                <?php
			                if( $terms =  get_terms( 'brands', 'orderby=name' ) ) :
				                echo '<select id="selectBrands" name="product_cat_filter" ><option>Filter by <strong>Brand</strong></option>';
				                foreach ( $terms as $term ) :
                                    $disbaled = ($term->count == 0)? 'disabled' : '';

                                    echo '<option value="' . $term->slug . '" '.$disbaled .'>' . $term->name .' ('.$term->count. ')</option>';
				                endforeach;
				                echo '</select>';
			                endif;
			                ?>
                            <input type="hidden" name="tax_filter" value="brands">


                            <input type="hidden" name="action" value="productcatfilter">

                        </form>
                        <i class="fa fa-chevron-down" aria-hidden="true"></i>

                    </div>

                </div>


                </div>
            </div>




		<?php endif; ?>

		<?php
			/**
			 * woocommerce_archive_description hook.
			 *
			 * @hooked woocommerce_taxonomy_archive_description - 10
			 * @hooked woocommerce_product_archive_description - 10
			 */
			do_action( 'woocommerce_archive_description' );
		?>

    </header>


		<?php if ( have_posts() ) : ?>

			<?php
				/**
				 * woocommerce_before_shop_loop hook.
				 *
				 * @hooked wc_print_notices - 10
				 * @hooked woocommerce_result_count - 20
				 * @hooked woocommerce_catalog_ordering - 30
				 */
				do_action( 'woocommerce_before_shop_loop' );
			?>

			<?php woocommerce_product_loop_start(); ?>

				<?php woocommerce_product_subcategories(); ?>

            <div class="products row">
                <div id="lazyload">

				<?php $i=1; while ( have_posts() ) : the_post(); ?>

                <?php
                if ($i % 4 == 0) {
                echo '</div><div class="products row" >';
                    }
                    ?>

					<?php
						/**
						 * woocommerce_shop_loop hook.
						 *
						 * @hooked WC_Structured_Data::generate_product_data() - 10
						 */

						do_action( 'woocommerce_shop_loop' );

					?>


					<?php wc_get_template_part( 'content', 'product' ); ?>

				<?php $i++; endwhile; // end of the loop. ?>

			<?php woocommerce_product_loop_end(); ?>

			<?php
				/**
				 * woocommerce_after_shop_loop hook.
				 *
				 * @hooked woocommerce_pagination - 10
				 */
				//do_action( 'woocommerce_after_shop_loop' );
			?>

		<?php elseif ( ! woocommerce_product_subcategories( array( 'before' => woocommerce_product_loop_start( false ), 'after' => woocommerce_product_loop_end( false ) ) ) ) : ?>

			<?php
				/**
				 * woocommerce_no_products_found hook.
				 *
				 * @hooked wc_no_products_found - 10
				 */
				do_action( 'woocommerce_no_products_found' );
			?>

                <?php  endif; ?></div></div>

	<?php
		/**
		 * woocommerce_after_main_content hook.
		 *
		 * @hooked woocommerce_output_content_wrapper_end - 10 (outputs closing divs for the content)
		 */
		do_action( 'woocommerce_after_main_content' );
	?>

	<?php
		/**
		 * woocommerce_sidebar hook.
		 *
		 * @hooked woocommerce_get_sidebar - 10
		 */
		do_action( 'woocommerce_sidebar' );
	?>
<div class="text-center loader-wrapper">

                <button class="loadmore hvr-sweep-to-right red-button" id="ctf-more" data-page="1" data-url="<?php echo admin_url( 'admin-ajax.php' ); ?>">LOAD MORE PRODUCTS</button>


</div>
</div>
<?php get_footer( 'shop' ); ?>


<?php if(isset($_GET['filter'])):

$filter_value = $_GET['filter'];
$filter_tax = $_GET['tax'];

?>
<script>
    jQuery(document).ready(function ($) {

        var url      = window.location.href;
        var getRequest = window.location.search.slice(1);
        var selected = $(this).find(":selected").text().replace(/\s+/g, '-').toLowerCase();

        var filter = $('#brandFilter');
        $('#selectBrands').val('<?php  echo $filter_value; ?>' );


        var loaderSpinner = '<div class="loader-spinner">' +
            '<span class="square"></span>'+
            '<span class="square"></span>'+
            '<span class="square last"></span>'+
            '<span class="square clear"></span>'+
            '<span class="square"></span>'+
            '<span class="square last"></span>'+
            '<span class="square clear"></span>'+
            '<span class="square"></span>'+
            '<span class="square last"></span>'+
            '</div>';
        $('.wrapper').append(loaderSpinner);
        $('.wrapper').css({'opacity': '0.2'});

        $.ajax({
            url:filter.attr('action'),
            data:filter.serialize(), // form data
            type:filter.attr('method'), // POST
            beforeSend:function(xhr){
                filter.find('button').text('Applying Filters...');          },
            error:function (data) {
                console.log('ERROR');
            },
            success:function(data){
                if(data =='') {
                    console.log('empty result');
                }else {
                    $('.loader-spinner').hide();
                    $('.wrapper').css({'opacity': '1'});
                    filter.find('button').text('Apply filters');
                    $('.products').empty().html(data);
                    // console.log(data);

                }


            }
        });








    });




</script>
<?php endif; ?>
<script>
    jQuery(document).ready(function ($) {


        var url      = window.location.href;
        var getRequest = window.location.search.slice();

        var cleanUrl = url.replace(getRequest,'');


        $('#selectProductCat').change(function(){

            var loaderSpinner = '<div class="loader-spinner">' +
                '<span class="square"></span>'+
                '<span class="square"></span>'+
                '<span class="square last"></span>'+
                '<span class="square clear"></span>'+
                '<span class="square"></span>'+
                '<span class="square last"></span>'+
                '<span class="square clear"></span>'+
                '<span class="square"></span>'+
                '<span class="square last"></span>'+
                '</div>';
            $('.wrapper').append(loaderSpinner);
            $('.wrapper').css({'opacity': '0.2'});
            var filter = $('#catFilter');
            $.ajax({
                url:filter.attr('action'),
                data:filter.serialize(), // form data
                type:filter.attr('method'), // POST
                beforeSend:function(xhr){
                    filter.find('button').text('Applying Filters...');          },
                error:function (data) {
                    console.log('ERROR');
                },
                success:function(data){
                    if(data =='') {
                        console.log('empty');
                    }else {
                        $('.loader-spinner').hide();
                        $('.wrapper').css({'opacity': '1'});
                        filter.find('button').text('Apply filters');
                        $('.products').empty().html(data);

                        history.pushState({}, "", cleanUrl);
                    }


                    // $('#lazyload').empty();
                }
            });
            return false;
        });



        $('#selectBrands').change(function(){


            var selected = $(this).find(":selected").text();
            console.log(selected);


            var loaderSpinner = '<div class="loader-spinner">' +
                '<span class="square"></span>'+
                '<span class="square"></span>'+
                '<span class="square last"></span>'+
                '<span class="square clear"></span>'+
                '<span class="square"></span>'+
                '<span class="square last"></span>'+
                '<span class="square clear"></span>'+
                '<span class="square"></span>'+
                '<span class="square last"></span>'+
                '</div>';
            $('.wrapper').append(loaderSpinner);
            $('.wrapper').css({'opacity': '0.2'});
            var filter = $('#brandFilter');
            $.ajax({
                url:filter.attr('action'),
                data:filter.serialize(), // form data
                type:filter.attr('method'), // POST
                beforeSend:function(xhr){
                    filter.find('button').text('Applying Filters...');
                },
                error:function (data) {
                    console.log('ERROR');
                },
                success:function(data){
                    if(data =='') {
                        console.log('empty');
                    }else {
                        $('.loader-spinner').hide();
                        $('.wrapper').css({'opacity': '1'});
                        filter.find('button').text('Apply filters');
                        $('.products').empty().html(data);


                        //history.pushState(null, null, url+'?'+selected);
                        history.pushState({}, "", cleanUrl);
                    }


                    // $('#lazyload').empty();
                }
            });
            return false;
        });
    });

</script>