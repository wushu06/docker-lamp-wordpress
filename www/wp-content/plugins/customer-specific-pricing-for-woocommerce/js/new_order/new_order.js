jQuery(document).ready(function () {
    var quantityBasedPricing = {};

    jQuery('[name=customer_user]').change(function () {

        var customer_id = jQuery('[name=customer_user]').val();
        
        if(customer_id == ''){
            customer_id=0;
        }
        var url = wdm_new_order_ajax.ajaxurl;
        jQuery.ajax({
            type: 'POST',
            url: url,
            data: {
                action: 'get_customer_id',
                customer_id: customer_id,
                order_id:wdm_new_order_ajax.order_id
            },
            success: function (response) {//response is value returned from php

            }
        });

    });

    jQuery( document.body ).on('wc_backbone_modal_response', function(event, target, order_item){
        var user_id = jQuery( '#customer_user' ).val();
        var product_id = order_item.add_order_items;
        var url = wdm_new_order_ajax.ajaxurl;
        jQuery.ajax({
            type: 'POST',
            url: url,
            data: {
                action: 'get_quantity_price_pairs',
                customer_id: user_id,
                product_id: product_id,
                order_id:wdm_new_order_ajax.order_id
            },
            success: function (response) {//response is value returned from php
                response = jQuery.parseJSON(response);

                if( !(user_id in quantityBasedPricing) ) {
                    quantityBasedPricing[user_id] = {};
                }

                // If there is no error, then process the array
                if(!("error" in response)) {
                    for (var single_product_id in response) {
                      if (response.hasOwnProperty(single_product_id)) {
                        quantityBasedPricing[user_id][single_product_id] = {
                            csp_prices : response[single_product_id].csp_prices,
                            qtyList : response[single_product_id].qtyList,
                            regular_price: response[single_product_id].regular_price,
                        }
                    }
                }
            }

        }
    });
    })

    jQuery('#woocommerce-order-items' ).on('quantity_changed', 'input.quantity', function(data){

        var user_id = jQuery( '#customer_user' ).val();
        var product_id = jQuery(this).closest('tr.item').find('.csp_order_item_product_id').val();
        //If we do not have csp data of a user, then return.
        if( !(user_id in quantityBasedPricing) ) {
            return;
        }

        var qty = parseInt(this.value, 10);
        if (!(qty < 1) && !isNaN(qty)) {
            price = getApplicablePrice(
                quantityBasedPricing[user_id][product_id]['qtyList'], 
                qty, 
                quantityBasedPricing[user_id][product_id]['csp_prices'], 
                quantityBasedPricing[user_id][product_id]['regular_price']
                );

            if(!isNaN(price)) {
                var price_total = price * qty;
                var formattedPrice = parseFloat( accounting.formatNumber( price_total , woocommerce_admin_meta_boxes.rounding_precision, '' ) )
                .toString()
                .replace( '.', woocommerce_admin.mon_decimal_point );
                // jQuery(this).closest('tr.item').find( 'input.line_subtotal').data('subtotal', price_total);
                // jQuery(this).closest('tr.item').find( 'input.line_subtotal').val(price_total);
                // jQuery(this).closest('tr.item').find('input.line_total').data('total', price_total);
                // jQuery(this).closest('tr.item').find( 'input.line_total').val(price_total);
                jQuery(this).closest('tr.item').find('input.line_subtotal').val(formattedPrice);
                jQuery(this).closest('tr.item').find('input.line_total').val(formattedPrice);

            }
        }
    });
});