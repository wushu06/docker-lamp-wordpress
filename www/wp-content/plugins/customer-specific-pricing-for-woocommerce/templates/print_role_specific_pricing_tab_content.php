<?php
/**
 * Print the role specific content in tab
 */

global $post;
$discountOptions = array('1'=>__('Flat', CSP_TD), '2'=>'%');
$more_than_one_row       = false;
$wdm_first_role_price    = '';
$wdm_first_role_qty      = '';
$wdm_first_role_price_type = 1;
$remove_image_path       = plugins_url('/images/minus-icon.png', dirname(__FILE__));
$add_image_path          = plugins_url('/images/plus-icon.png', dirname(__FILE__));
$array_of_role_price_pair    = \WuspSimpleProduct\WrspSimpleProduct\WdmWuspSimpleProductsRsp::getAllRolePricesForSingleProduct($post->ID);

if (count($array_of_role_price_pair) > 0 && false != $array_of_role_price_pair) {
    echo "<script type='text/javascript'>var role_scntDiv= jQuery( '#wdm_role_specific_pricing_tbody' ); var wdm_temp_select_holder = null; var wdm_temp_html_holder = null; </script>";
    for ($j = 1; $j < count($array_of_role_price_pair); $j ++) {
        $more_than_one_row   = true;
        ob_start();
        $wdm_dropdown_content    = wp_dropdown_roles($array_of_role_price_pair[ $j ]->role);
        $wdm_roles_dropdown  = ob_get_contents();
        ob_end_clean();
        echo "
					<script type=\"text/javascript\">
					    jQuery( function () {
					        //Print all combinations saved in the database except first combination.
					        wdm_role_temp_select_holder = \"" . str_replace("\n", '', $wdm_roles_dropdown) . "\";
					        //Start new row
					        start_row = '<tr>';
					        //Show role dropdown
					        select_holder = \"<td class='wdm_left_td' ><select name='wdm_woo_rolename[]' class='chosen-select'>\" + wdm_role_temp_select_holder + \"</select></td>\";

                            //Show price type dropdown
                            type_holder = \"<td class='wdm_left_td' ><select name='wdm_role_price_type[]' class='chosen-select csp_wdm_action'>\";";

        for ($i = 1; $i<= count($discountOptions); $i++) {
            if ($array_of_role_price_pair[ $j ]->price_type == $i) {
                echo "type_holder+= \"<option value ='".$i."' selected>".$discountOptions[$i]."</option>\";";
            } else {
                echo "type_holder+= \"<option value ='".$i."'>".$discountOptions[$i]."</option>\";";
            }
        }
        echo "
                    type_holder += \"</select></td>\";";

        //Show Quantity Textbox
        echo "qty_textbox = \"<td class='wdm_left_td'><input type='number' min = '1' name='wdm_woo_role_qty[]' class='wdm_qty' value='" . $array_of_role_price_pair[ $j ]->min_qty . "' />\";";

        //Show Price's Textbox
        if ($array_of_role_price_pair[ $j ]->price_type == 2) {
            echo "price_textbox = \"<td colspan=3 class='wdm_left_td'><input type='text' name='wdm_woo_role_price[]' class='wdm_price csp-percent-discount' value='" . wc_format_localized_price($array_of_role_price_pair[ $j ]->price) . "' />\";";
        } else {
            echo "price_textbox = \"<td colspan=3 class='wdm_left_td'><input type='text' name='wdm_woo_role_price[]' class='wdm_price' value='" . wc_format_localized_price($array_of_role_price_pair[ $j ]->price) . "' />\";";
        }
                    //Show Remove row button
                    echo "remove_row_button = \"<td><a class='wdm_remove_pair_link' href='#' id='role_remScnt' ><img class='remove_user_price_pair_row_image' alt='Remove Row' title='Remove Row' src='" . $remove_image_path . "'/></a>\";
					//Add new pair button
					        add_new_row = \"<a class='wdm_add_role_pair_link' href='#' id='wdm_add_new_role_price_pair'><img class='add_new_row_image' src='" . $add_image_path . "' /></a></td>\";
					//Lets remove wdm_add_role_pair_link associated with earlier row
					       jQuery( '.wdm_add_role_pair_link' ).remove();
					//end row
					        end_row = '</td></tr>';
					        role_scntDiv.append(
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
					        if ( typeof chosen === \"function\" ) {
					            jQuery( '.chosen-select' ).chosen( { 'width': '200px' } )
					        }
					    } );
					</script>";
    }

    ob_start();
    $role            = $array_of_role_price_pair[0]->role;
    $wdm_dropdown_content    = wp_dropdown_roles($role);
    $wdm_roles_dropdown  = ob_get_contents();
    ob_end_clean();
    $price           = wc_format_localized_price($array_of_role_price_pair[0]->price);
    $min_qty           = $array_of_role_price_pair[0]->min_qty;
    $price_type      = $array_of_role_price_pair[0]->price_type;
    // $wdm_first_price_of_user = $price;
    $wdm_first_role_price = $price;
    $wdm_first_role_qty = $min_qty;
    $wdm_first_role_price_type = $price_type;
} else {
    ob_start();
    $wdm_dropdown_content    = wp_dropdown_roles();
    $wdm_roles_dropdown  = ob_get_contents();
    ob_end_clean();
}
$array_of_values_to_be_passed_to_js = array(
    'wdm_roles_dropdown_html'    => str_replace("\n", "", $wdm_roles_dropdown),
    'discountOptions'        => $discountOptions,
    'wdm_first_role_price'        => $wdm_first_role_price,
    'wdm_first_role_price_type' => $wdm_first_role_price_type,
    'wdm_first_role_qty'     => $wdm_first_role_qty,
    'remove_image_path'      => $remove_image_path,
    'add_image_path'         => $add_image_path,
    'more_than_one_row'      => $more_than_one_row,
    'add_new_role_text'  => __('Add New Role-Price Pair', CSP_TD),
);

wp_enqueue_script('wdm_role_pricing_tab_js', plugins_url('/js/simple-products/customer-specific-pricing-tab/wdm-role-specific-pricing.js', dirname(__FILE__)), array( 'jquery' ), CSP_VERSION);
wp_localize_script('wdm_role_pricing_tab_js', 'wdm_role_pricing_object', $array_of_values_to_be_passed_to_js);
