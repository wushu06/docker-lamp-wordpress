<?php
/**
 * Print the content of the tab
 * Saving of these custom fields is handled by function process_product_meta_userSpecificPricingTab of WdmWuspSimpleProductsUsp class.
 */
global $post, $product, $wp_version;
$remove_image_path   = plugins_url('/images/minus-icon.png', dirname(__FILE__));
$add_image_path      = plugins_url('/images/plus-icon.png', dirname(__FILE__));

// Fall back for wordpress version below 4.5.0
$show_user = 'user_login';
if ($wp_version >= '4.5.0') {
    $show_user = 'display_name_with_login';
}
/**
 * Fetch WordPress users. Using wdm_usp_user_dropdown_params filter developers
 * can decide what to be shown in the Users dropdown
 */
$args = apply_filters('wdm_usp_user_dropdown_params', array(
    'show_option_all'        => null, // string
    'show_option_none'       => null, // string
    'hide_if_only_one_author'    => null, // string
    'orderby'            => 'display_name',
    'order'              => 'ASC',
    'include'            => null, // string
    'exclude'            => null, // string
    'multi'              => false,
    'show'               => $show_user,
    'echo'               => false,
    'selected'           => false,
    'include_selected'       => false,
    'name'               => 'wdm_woo_username[]', // string
    'id'                 => null, // integer
    'class'              => 'chosen-select', // string
    'blog_id'            => $GLOBALS['blog_id'],
    'who'                => null, // string
    ));

$wdm_users_dropdown = wp_dropdown_users($args);

$discountOptions = array('1'=>__('Flat', CSP_TD), '2'=>'%');
$array_of_username_price_pair = array();

// Retrieve pricing from db and arrange it in associative array where key is user_id
// and value is price.
$array_of_username_price_pair = \WdmCSP\WdmWuspGetData::getAllPricesForSingleProduct($post->ID);

if (empty($array_of_username_price_pair)) {
    $wdm_first_username  = '';
    $wdm_first_price_of_user = '';
    $wdm_first_qty_user = '';
    $wdm_first_user_price_type = 1;
} else {
    //Retrieve value of first user saved in db for corresponding product
    // $list_of_all_users = array_keys($array_of_username_price_pair);

    // Push value of first user-price to variable. This variable would be
    // passed to JS file.
    $wdm_first_username  = $array_of_username_price_pair[0]->user_id;
    $wdm_first_price_of_user = wc_format_localized_price($array_of_username_price_pair[0]->price);
    $wdm_first_qty_user = $array_of_username_price_pair[0]->min_qty;
    $wdm_first_user_price_type = $array_of_username_price_pair[0]->price_type;

    do_action('wdm_add_before_simple_csp');
}

update_post_meta(get_the_ID(), 'CSP', 'Registered');
?>
<div id="userSpecificPricingTab_data" class="panel show_if_simple  woocommerce_options_panel">
    <p><strong><?php _e('Notes', CSP_TD) ?>:</strong></p>
    <ol>
        <strong>
            <li><?php _e('If a customer is added more than once, the customer-price combination first in the list will be saved, and other combinations will be removed.', CSP_TD) ?></li>
            <li><?php _e('If the price field is left blank, then default product price will be shown.', CSP_TD) ?></li>
            <li><?php _e('Please set the min qty before saving. Only then, the discounted amount will be saved and will reflect to the logged in user, role or group.', CSP_TD) ?></li>
            <li><?php _e('If a customer belongs to multiple groups (or roles), the least price set for the group (or role) will be applied', CSP_TD) ?></li>
            <li><?php _e('The priorities are as follows', CSP_TD) ?> - 
            <ol>
                <li><?php _e('Customer Specific Price', CSP_TD) ?></li>
                <li><?php _e('Role Specific Price', CSP_TD) ?></li>
                <li><?php _e('Group Specific Price', CSP_TD) ?></li>
                <li><?php _e('Regular Price', CSP_TD) ?></li>
            </ol>
            </li>
        </strong>
    </ol>
    <div class="accordion csp-accordion">
    <?php do_action('wdm_add_before_csp'); ?>
    <h3 class="wdm-heading"><?php _e('Customer Based Pricing', CSP_TD) ?></h3>
    <div>
        <!-- <button type="button" class="button" id="wdm_add_new_user_price_pair"><?php //_e('Add New Customer-Price Pair', CSP_TD) ?></button> -->
        <div class="options_group wdm_user_pricing_tab_options">
        <table cellpadding="0" cellspacing="0" class="wc-metabox-content  wdm_simple_product_usp_table" style="display: table;">
            <thead class="username_price_thead">
            <tr>
                <th>
                    <?php _e('Customer Name', CSP_TD) ?>
                </th>
                <th>
                    <?php _e('Discount Type', CSP_TD) ?>
                </th>
                <th>
                    <?php _e('Min Qty', CSP_TD) ?>
                </th>
                <th colspan=3>
                    <?php _e('Value', CSP_TD) ?>
                </th>
            </tr>
            </thead>
            <tbody id="wdm_user_specific_pricing_tbody"></tbody>
        </table>
        </div>
    </div>
    <?php do_action('wdm_add_after_simple_csp'); ?>
    </div>
    <p>
    <strong><?php _e('Remember to Publish/Update the product to save any changes made.', CSP_TD) ?></strong>
    </p>
</div>
<?php
//Flag to track if more than one rows are avaialble
$more_than_one_row = false;

if (count($array_of_username_price_pair) > 0 && false != $array_of_username_price_pair) {
    echo "<script type='text/javascript'>var scntDiv = jQuery('#wdm_user_specific_pricing_tbody'); var wdm_temp_select_holder = null; var wdm_temp_html_holder = null;</script>";

    // Javascript is added here because it is going to show all combinations
    // and hence it needs looping. To solve the purpose of looping, javascript
    // is being added. This javascript shows only combinations saved in db.

    for ($j = 1; $j < count($array_of_username_price_pair); $j ++) {
        $more_than_one_row = true;
    ?>
    <script type="text/javascript">
        jQuery( function () {
            //Print all combinations saved in the database except first combination.
            wdm_temp_select_holder = jQuery( "<?php echo str_replace("\n", "", $wdm_users_dropdown); ?>" );
            wdm_temp_select_holder.find( 'option[value="<?php echo $array_of_username_price_pair[$j]->user_id ; ?>"]' ).attr( 'selected', true );

            //Start new row
            start_row = "<tr>";

            //Show User dropdown
            select_holder = "<td class='wdm_left_td' ><select name='wdm_woo_username[]' class='chosen-select'>" + wdm_temp_select_holder.html() + "</select></td>";

            //show price type dropdown
            type_holder = "<td class = 'wdm_left_td discount_options'><select name='wdm_price_type[]' class='chosen-select csp_wdm_action'>";
            <?php
            for ($i = 1; $i <= count($discountOptions); $i++) {
                ?>
                var i = "<?php echo $i; ?>";
                <?php
                if ($array_of_username_price_pair[$j]->price_type == $i) {
            ?>
                    type_holder += "<option value ='"+i+"' selected>"+wdm_user_pricing_object.discountOptions[i]+"</option>";
            <?php
                } else {
            ?>
                    type_holder += "<option value ='"+i+"'>"+wdm_user_pricing_object.discountOptions[i]+"</option>";
            <?php
                }
            }
            ?>
            type_holder += "</select></td>";

            //Show Quantity Textbox
            qty_textbox = "<td class='wdm_left_td'><input type='number' min = '1' name='wdm_woo_qty[]'' class='wdm_qty' value='<?php echo $array_of_username_price_pair[$j]->min_qty ; ?>' ></td>";
<?php
            //Show Price's Textbox
if ($array_of_username_price_pair[$j]->price_type == 2) {
?>
price_textbox = "<td class='wdm_left_td'><input type='text' name='wdm_woo_price[]' class='wdm_price csp-percent-discount' value='<?php echo wc_format_localized_price($array_of_username_price_pair[$j]->price) ; ?>' /></td><td><a class='wdm_remove_pair_link' href='#' id='remScnt'  >";
<?php
} else {
?>
price_textbox = "<td class='wdm_left_td'><input type='text' name='wdm_woo_price[]' class='wdm_price' value='<?php echo wc_format_localized_price($array_of_username_price_pair[$j]->price) ; ?>' /></td><td><a class='wdm_remove_pair_link' href='#' id='remScnt'  >";
<?php
}
?>
            //Show Remove row button
            remove_row_button = "<img alt='Remove Pair' title='Remove Pair' class='remove_user_price_pair_row_image' src='<?php echo $remove_image_path; ?>'/></a>";

            add_new_row = '';
            <?php if ($j == count($array_of_username_price_pair) - 1) { ?>
                        //Add new pair button
                        add_new_row = "<a class='wdm_add_pair_link' href='#' id='wdm_add_new_user_price_pair'><img class='add_new_row_image' src='<?php echo $add_image_path ?>' /></a>";
            <?php
} ?>
            //end row
            end_row = "</td></tr>";

            scntDiv.append(
                start_row +
                select_holder +
                type_holder +
                qty_textbox +
                price_textbox +
                remove_row_button +
                add_new_row +
                end_row
                );
            wdm_temp_select_holder = null;
            if ( typeof chosen === "function" ) {
                jQuery( ".chosen-select" ).chosen( { 'width': '200px' } );
            }
        } );
    </script>
    <?php
    }
}

$array_of_values_to_be_passed_to_js = array(
    'wdm_users_dropdown_html'    => str_replace("\n", '', $wdm_users_dropdown),
    'wdm_first_username'         => $wdm_first_username,
    'wdm_first_price_of_user'    => $wdm_first_price_of_user,
    'wdm_first_user_price_type'    => $wdm_first_user_price_type,
    'wdm_first_qty_user'         => $wdm_first_qty_user,
    'remove_image_path'      => $remove_image_path,
    'add_image_path'         => $add_image_path,
    'more_than_one_row'      => $more_than_one_row,
    'discountOptions'        => $discountOptions,
    'add_new_pair_text'      => __('Add New Pair', CSP_TD),
    'please_verify_prices'   => __('It seems that some values mapped to users, roles or groups are not valid. Please verify it again.', CSP_TD),
    'add_new_customer_text' => __('Add New Customer-Price Pair', CSP_TD),
);

wp_enqueue_script('jquery-ui-accordion');
wp_enqueue_script('wdm_user_pricing_tab_js', plugins_url('/js/simple-products/customer-specific-pricing-tab/wdm-user-specific-pricing.js', dirname(__FILE__)), array( 'jquery-ui-accordion' ), CSP_VERSION, true);
wp_localize_script('wdm_user_pricing_tab_js', 'wdm_user_pricing_object', $array_of_values_to_be_passed_to_js);
wp_enqueue_style('wdm_user_pricing_tab_css', plugins_url('/css/wdm-user-pricing-tab.css', dirname(__FILE__)), array(), CSP_VERSION);
