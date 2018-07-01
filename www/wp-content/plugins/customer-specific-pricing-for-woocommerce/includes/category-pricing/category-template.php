<?php
if (! defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
?>
<div class="wrapper category-pricing">
	<div class="update-nag wdm-tab-info">
	<?php _e('Set pricing for multiple categories for specific customer, role or group.', CSP_TD) ?>
	</div>
<?php

global $wp_version, $wpdb;

// $discountOptions = array('1'=>__('Flat', CSP_TD), '2'=>'%');

$catArgs = array(
    'order'      => 'ASC',
    'hide_empty' => 0,
    'posts_per_page' =>'-1'
);

$product_categories = get_terms( 'product_cat', $catArgs );
$discountOptions = array('1'=>__('Flat', CSP_TD), '2'=>'%');
?>
	<form action = "#" method = "post">
	<?php wp_nonce_field( 'csp_save_category_pricing', '_save_category' ); ?>
		<input type="hidden" name="save_records">
		<div>
		    <p><strong><?php _e('Notes', CSP_TD) ?>:</strong></p>
		    <ol>
		        <strong>
		            <li><?php _e('If a customer is added more than once, the customer-price combination first in the list will be saved, and other combinations will be removed.', CSP_TD) ?></li>
		            <li><?php _e('When a price field is left blank, the regular price will be displayed for that product.', CSP_TD) ?></li>
		            <li><?php _e('Make sure the minimum quantity is set before saving the prices; adding the minimum quantity will ensure the prices are saved and displayed accordingly.', CSP_TD) ?></li>
		            <li><?php _e('The least price will be applicable to a customer belonging to multiple groups/roles with specific prices.', CSP_TD) ?></li>
		            <li><?php _e('The least price will be applicable for a product belonging to multiple categories with specific prices.', CSP_TD) ?></li>
		            <li><?php _e('The priorities for prices applied to products are as follows', CSP_TD) ?> - 
		            <ol>
		                <li><?php _e('Customer Specific Product Pricing.', CSP_TD) ?></li>
		                <li><?php _e('Role Specific Product Pricing.', CSP_TD) ?></li>
		                <li><?php _e('Group Specific Product Pricing.', CSP_TD) ?></li>
		                <li><?php _e('Customer Specific Category Pricing.', CSP_TD) ?></li>
		                <li><?php _e('Role Specific Category Pricing.', CSP_TD) ?></li>
		                <li><?php _e('Group Specific Category Pricing.', CSP_TD) ?></li>
		                <li><?php _e('Sale price (if any)', CSP_TD) ?></li>
		                <li><?php _e('Regular Price', CSP_TD) ?></li>
		            </ol>
		            </li>
		        </strong>
		    </ol>
		</div>

		<div id="accordion">
			<h3>User based category pricing</h3>
			<div class = "user_data">
			<?php do_action('csp_show_user_data'); ?>
			</div>
			<h3>Role based category pricing</h3>
			<div class = "role_data">
			<?php do_action('csp_show_role_data'); ?>
			</div>
			<h3>Group based category pricing</h3>
			<div class = "group_data">
			<?php do_action('csp_show_group_data'); ?>
			</div>
		</div>
	    <div class="wdm-input-group">
	        <input type="submit" id="cat_pricing" name="save_cat_price" class="button button-primary" value="<?php _e('Save Pricing', CSP_TD) ?>">
	    </div>
	</form>
</div>
