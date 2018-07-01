jQuery(document).ready(function () {

    //Add new row when there are no rows
    jQuery('#variable_product_options').delegate('span.add_var_csp_v', 'click', function () {
        wdm_temp_select_holder = wdm_variable_product_role_csp_object.wdm_roles_dropdown_html;
        var table_to_be_considered = jQuery(this).next("table.wdm_variable_product_role_usp_table");

        var variation_post_id = jQuery(this).parents('div.wdm_user_price_mapping_wrapper').find('.wdm_hidden_variation_data_csp').html();

        jQuery(this).remove();
        table_to_be_considered.show();
        table_to_be_considered.append(
                '<tr class="single_variable_csp_row">' +
                "<td><select name='wdm_woo_rolename_v[" + variation_post_id + "][]' class='chosen-select'>" + wdm_temp_select_holder + "</select></td>" +
                "<td><select name='wdm_role_price_type_v[" + variation_post_id + "][]' class='chosen-select csp_wdm_action'><option value = 1>" + wdm_variable_product_role_csp_object.wdm_discount_options[1] + "</option><option value = 2>" + wdm_variable_product_role_csp_object.wdm_discount_options[2] + "</option></select></td>" +
                '<td><input type="number" min = "1" size="5" class ="wdm_qty" name="wdm_woo_variation_qty_v[' + variation_post_id + '][]" />' +
                '<td><input type="text" style="width:75px;" size="5" class ="wdm_price" name="wdm_woo_variation_price_v[' + variation_post_id + '][]" />' +
                '<td class="remove_var_csp_v" style="color:red;cursor: pointer;"><img src="' + wdm_variable_product_role_csp_object.minus_image + '"></td>' +
                '<td class="add_var_csp_v" style="cursor: pointer;"><img src="' + wdm_variable_product_role_csp_object.plus_image + '"></td>' +
                '</tr>'
                );
    });
    //Add row when button is clicked
    jQuery('#variable_product_options').delegate('td.add_var_csp_v', 'click', function () {
        wdm_temp_select_holder = wdm_variable_product_role_csp_object.wdm_roles_dropdown_html;



        var variation_post_id = jQuery(this).parents('div.wdm_user_price_mapping_wrapper').find('.wdm_hidden_variation_data_csp').html();

        var wdm_append_to_parent = jQuery(this).parents("table.wdm_variable_product_role_usp_table");
        wdm_append_to_parent.append(
                '<tr class="single_variable_csp_row">' +
                "<td><select name='wdm_woo_rolename_v[" + variation_post_id + "][]' class='chosen-select'>" + wdm_temp_select_holder + "</select></td>" +
                "<td><select name='wdm_role_price_type_v[" + variation_post_id + "][]' class='chosen-select csp_wdm_action'><option value = 1>" + wdm_variable_product_role_csp_object.wdm_discount_options[1] + "</option><option value = 2>" + wdm_variable_product_role_csp_object.wdm_discount_options[2] + "</option></select></td>" +
                '<td><input type="number" min = "1" size="5" class ="wdm_qty" name="wdm_woo_variation_qty_v[' + variation_post_id + '][]" />' +
                '<td><input type="text" style="width:75px;" size="5" class ="wdm_price" name="wdm_woo_variation_price_v[' + variation_post_id + '][]" />' +
                '<td class="remove_var_csp_v" style="color:red;cursor: pointer;"><img src="' + wdm_variable_product_role_csp_object.minus_image + '"></td>' +
                '<td class="add_var_csp_v" style="cursor: pointer;"><img src="' + wdm_variable_product_role_csp_object.plus_image + '"></td>' +
                '</tr>'
                );

        jQuery(this).remove(); //Remove Add Button

    });

//Remove row when remove button is clicked
    jQuery('#variable_product_options').delegate('td.remove_var_csp_v', 'click', function () {
        var variation_post_id = jQuery(this).parents('div.wdm_user_price_mapping_wrapper').find('.wdm_hidden_variation_data_csp').html();

        var parent_table_element = jQuery(this).parents("table.wdm_variable_product_role_usp_table");

        //enabled save changes button on edit variable product page whenever a role pricing pair is removed.
        jQuery(this).closest('.woocommerce_variation').addClass('variation-needs-update');
        jQuery('button.cancel-variation-changes, button.save-variation-changes').removeAttr('disabled');
        jQuery('#variable_product_options').trigger('woocommerce_variations_input_changed');

        jQuery(this).parent('tr').remove();
        var last_tr_element = parent_table_element.find("tr:last");


        if (last_tr_element.find("th:last").length) {
            parent_table_element.hide();
            parent_table_element.before('<span class="add_var_csp_v" style="cursor: pointer; margin: 1em;display:block;"><button type="button" class="button">Add New Role-Price Pair </button></span>');
        }
        else if (!last_tr_element.find("td:last").hasClass('add_var_csp_v')) {
            last_tr_element.append('<td class="add_var_csp_v" style="cursor: pointer;"><img src="' + wdm_variable_product_role_csp_object.plus_image + '"></td>');
        }
    });

    //On clicking Save Changes, check if all values are valid. If there is any invalid field, then show alert.
    jQuery("#variable_product_options").on('woocommerce_variations_save_variations_button', function(){
        variation_ids = [];
        var wrapper     = jQuery( '#variable_product_options' ).find( '.woocommerce_variations' ),
        need_update = jQuery( '.variation-needs-update', wrapper );
        if ( 0 < need_update.length ) {
            if(wdm_error_function('before_variations') === false) {
                need_update.removeClass( 'variation-needs-update' );
            }
        }
    });


    // //If Price is not valid, then highlight the Price box
    // jQuery('#variable_product_options').delegate('.wdm_price', 'focusout', function () {
    //     highlightPriceError(jQuery(this));
        
    // }); //end live

    // jQuery('#variable_product_options').delegate('.wdm_price', 'change',function(){
    //     highlightPriceError(jQuery(this));
    // })

    // jQuery( '#variable_product_options' ).delegate( '.csp_wdm_action', 'change', function () {
    //     var price_selector = jQuery(this).closest('tr').find('.wdm_price');

    //     if(jQuery(this).val() == 1) {
    //         if(price_selector.hasClass('csp-percent-discount')) {
    //             price_selector.removeClass('csp-percent-discount');
    //         }
    //     } else {
    //         price_selector.addClass('csp-percent-discount');
    //     }
    //     highlightPriceError(price_selector);
    // });

    // //If qty is not valid, then highlight the qty box
    // jQuery( '#variable_product_options' ).delegate( ".wdm_qty", 'focusout', function () {

    //     var current_quantity = jQuery( this ).val();
    
    //     if(!isPositiveInt(current_quantity)) {
    //         jQuery( this ).addClass( 'wdm_error' );
    //     } else {
    //         if(jQuery( this ).hasClass( 'wdm_error' )) {
    //             jQuery( this ).removeClass( 'wdm_error' );
    //         }
    //     }

    // } ); //end focusout

    // //When User edits the invalid field, clear the background of that field
    // jQuery( '#variable_product_options' ).delegate( ".wdm_qty", 'focusin', function () {
    //     jQuery( this ).removeClass( 'wdm_error' );
    // } ); //end focusin


    // //When User edits the invalid field, clear the background of that field
    // jQuery('#variable_product_options').delegate('.wdm_price', 'focusin', function () {
    //     jQuery(this).removeClass('wdm_error');
    // }); //end live

    // function regularPriceExist(price) {
    //     if (jQuery( price ).val() != "" && jQuery( price ).parents('.woocommerce_variable_attributes').find("input[name^='variable_regular_price']").val() == "") {
    //         return true;
    //     }
    //     return false;
    // }

    function wdm_error_function(error_location = 'top_of_page') {
        jQuery('#wdm_message').remove();
        var wdm_error_flag = 1;
        var wdm_rprice_error = 1;
        var variation_error_id = 0;
        jQuery(".wdm_price").filter(function () {
            var variation_string = jQuery(this).attr('name'); // name of the price input field

            if (jQuery(this).hasClass('wdm_error')) {
                variation_error_id = parseInt(variation_string.replace( /^\D+/g, ''));
                return wdm_error_flag = 0;
            }

            if (regularPriceExist(this)) {
                variation_error_id = parseInt(variation_string.replace( /^\D+/g, ''));
                pushInErrorVariationIds(variation_error_id);
                return wdm_rprice_error = 0;
            }
        });

        jQuery( ".wdm_qty" ).filter( function () {
            var variation_string = jQuery(this).attr('name'); // name of the price input field
            if ( jQuery( this ).hasClass( 'wdm_error' ) ) {
                variation_error_id = parseInt(variation_string.replace( /^\D+/g, ''));
                return wdm_error_flag = 0;
            }
        } );

        if (wdm_rprice_error === 0) {
            var error_variation_ids = "<span class = 'wdm_error_ids'>";
            var array_size = variation_ids.length;
            // var seperator = "";
            for( var i in variation_ids) {
                var seperator = "";
                if(array_size-1 != i){
                    seperator = ", ";
                }
                error_variation_ids += "#"+variation_ids[i]+seperator;
            }
            error_variation_ids += "</span>";
            var messageText = wdm_csp_function_object.please_verify_regular_prices+error_variation_ids+wdm_csp_function_object.please_set_regular_prices;
            return preventSubmission(messageText, error_location);
        } else if (wdm_error_flag === 0) {
                var messageText = wdm_csp_function_object.please_verify_prices + variation_error_id;
                return preventSubmission(messageText, error_location);
        }
    }

    function pushInErrorVariationIds(variation_id)
    {
        if(jQuery.inArray(variation_id, variation_ids) == -1) {
            variation_ids.push(variation_id);
        }
    }

});
