jQuery(function($){
    var quantityList		= JSON.parse(wdm_csp_qty_price_object.qtyList),
    	cspPrices	= JSON.parse(wdm_csp_qty_price_object.csp_prices),
    	minQuantity	= JSON.parse(wdm_csp_qty_price_object.minimum),
    	regularPrices	= wdm_csp_qty_price_object.regular_price,
        current_cart_total = wdm_csp_qty_price_object.cart_contents_total,
        currency = wdm_csp_qty_price_object.currency_symbol,
        price, qtyList, csp_prices, regular_price;


    $('[name=quantity]').change(function(){

    	if (!$('#product_total_price').is(":visible")) {
    		$('#product_total_price').show();
    	}
    	var qty = parseInt(this.value, 10);
        if (!(qty < 1) && !isNaN(qty)) {
            if (typeof csp_prices != 'undefined' && typeof regular_price != 'undefined') {
                price = regular_price;
                if(typeof qtyList != 'undefined') {
                    price = getApplicablePrice(qtyList, qty, csp_prices, regular_price);
                }
                showPrices(price, qty, currency, current_cart_total);
            }        	
        } else {
        	$('#product_total_price').hide();
        }
    });

    $( ".single_variation_wrap" ).on( "hide_variation", function (event) {
        $('#product_total_price').hide();
    });

    $( ".single_variation_wrap" ).on( "show_variation", function (event, variation, purchasable) {
    // Fired when the user selects all the required dropdowns / attributes
    // and a final variation is selected / shown
        
	    $('#product_total_price').show();
	    qtyList 		= quantityList[variation.variation_id];
	    csp_prices 		= cspPrices[variation.variation_id];
	    min 			= minQuantity[variation.variation_id];
	    regular_price 	= regularPrices[variation.variation_id];
        var csp_keys = Object.keys(csp_prices);
        var csp_length = csp_keys.length;

        if (!empty(csp_prices)) {
            if(csp_length == 1 && csp_keys[0] == 1) {
                var current_price = parseFloat(csp_prices[csp_keys[0]]);
                jQuery('.woocommerce-variation-price').html("<p class='price'>"+ cspFormatPrice(current_price) + wdm_csp_qty_price_object.price_suffix + "</p>");
                jQuery('#product_total_price .price').html( currency + current_price.toFixed(2));
            } else {
                displayTable(min, qtyList, csp_prices, regular_price);  
            } 
        } 
        $('[name=quantity]').change();
	} );

    function displayTable(min, qtyList, csp_prices, regular_price)
    {
        var table = showQtyPriceTable(min, qtyList, csp_prices, regular_price);
        $('.woocommerce-variation-price').html(table);
    }

    function showQtyPriceTable(min, qtyList, csp_prices, regular_price)
    {
        if (csp_prices !== 'undefined') {          
        	var current_qty = parseInt($('[name=quantity]').val(), 10);
			if (current_qty == 1) {
				var current_price = getApplicablePrice(qtyList, current_qty, csp_prices, regular_price);
				jQuery('#product_total_price .price').html( currency + current_price.toFixed(2));		
			}
        	// purchasable = true;
            var table = '<div class = "qty-fieldset"><h1 class = "qty-legend"><span>' + wdm_csp_qty_price_object.quantity_discount_text + '</span></h1><table class = "qty_table">';

            if (min && min != 1) {
                var price = regular_price;
                table += "<tr>";
                table += "<td class = 'qty-num'>1  "+wdm_csp_qty_price_object.more_text+" </td><td class = 'qty-price'>"+ cspFormatPrice(price) + wdm_csp_qty_price_object.price_suffix + "</td>";
                table += "</tr>";
            }

            for (var qty in csp_prices) {
            	qty = parseInt(qty, 10);
            	if(!isNaN(qty)) {
	                var price = csp_prices[qty];
	                table += "<tr>";
	                table += "<td class = 'qty-num'>"+qty+" "+wdm_csp_qty_price_object.more_text+" </td><td class = 'qty-price'>"+ cspFormatPrice(price) + wdm_csp_qty_price_object.price_suffix + "</td>";
	                table += "</tr>";
            	}
            }
            table += "</table></div>";
            return table;
        }
        return regular_price;
    }
});