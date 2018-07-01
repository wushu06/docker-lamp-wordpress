<?php
/**
 * Single Product Price
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/single-product/price.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @author  WooThemes
 * @package WooCommerce/Templates
 * @version 3.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

global $product;
$price_html = $product->get_price_html();
?>
<?php if(is_user_logged_in()) : ?>
	<?php if ( $price_html = $product->get_price_html() ) : ?>
        <span class="price button  text-center single-price">YOUR PRICE <br><?php echo $price_html; ?>EX VAT</span>
	<?php endif; ?>
<?php else:
	?>
    <span><h2 class="single-login"><a href="<?php echo site_url() ?>/login">Login for Price</a></h2></span>
	<?php
endif;?>
