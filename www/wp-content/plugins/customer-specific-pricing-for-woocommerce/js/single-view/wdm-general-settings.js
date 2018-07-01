( function ( $ ) {
    'use strict';

    /**
     * All of the code for your admin-specific JavaScript source
     * should reside in this file.
     *
     * Note that this assume you're going to use $, so it prepares
     * the $ function reference to be used within the scope of this
     * function.
     *
     * From here, you're able to define handlers for when the DOM is
     * ready:
     *
     * $(function() {
     *
     * });
     *
     * Or when the window is loaded:
     *
     * $( window ).load(function() {
     *
     * });
     *
     * ...and so on.
     *
     * Remember that ideally, we should not attach any more than a single DOM-ready or window-load handler
     * for any particular page. Though other scripts in WordPress core, other plugins, and other themes may
     * be doing this, we should try to minimize doing that in our own work.
     */

    $( function () {

	var progress_timer;
	var enableAllFields = true;
	var retrieveAlreadySetProducts = new Object;
	var retrieveAlreadySetProductsActions = new Object;
	var retrieveAlreadySetProductsQty = new Object;

	var retrieveExistingTitle = new Object;

	var customerProductsSelectionFlag = new Object;

	customerProductsSelectionFlag.flag = 1;

	var getParamters = getSearchParameters();

	if ( $.isPlainObject( single_view_obj.product_result ) ) {
	    enableDualListBox();
	    var response = single_view_obj.product_result;
	    appendProductPriceTable( response );
	    disableEntries();
	    $( '.wdm-csp-product-details-list' ).css( 'opacity', '1' );
	}

	//Hide set price button if 'Edit Rule' button is visible
	if ( $( '#wdm_edit_entries' ).is( ':visible' ) ) {
	    $( '#wdm_csp_set_price' ).css( 'visibility', 'hidden' );
	}

	$( '#wdm_setting_option_type' ).change( function () {
	    var option_type = $( this ).val();
	    if ( $( '#wdm_csp_query_title' ).length ) {
		retrieveExistingTitle.text = $( '#wdm_csp_query_title' ).val();
	    }
	    $( '.wdm-csp-single-view-result-wrapper' ).empty();
	    $( '#loading' ).remove();

	    if ( option_type != -1 ) {
		$( this ).after( '<img src="' + single_view_obj.loading_image_path + '" id="loading"/>' );
		//Send AJAX request

		$.ajax( {
		    type: 'POST',
		    url: single_view_obj.admin_ajax_path,
		    data: {
			action: 'get_type_selection_result',
			option_type: option_type
		    },
		    success: function ( response ) { //response is value returned from php
			$( '#loading' ).remove();
			$( '.wdm-csp-single-view-result-wrapper' ).append( response );
			enableDualListBox();
		    }
		} );
	    }
	} ); //option type selection end

	function getSearchParameters() {
	    var prmstr = window.location.search.substr( 1 );
	    return prmstr != null && prmstr != "" ? transformToAssocArray( prmstr ) : { };
	}

	function transformToAssocArray( prmstr ) {
	    var params = { };
	    var prmarr = prmstr.split( "&" );
	    for ( var i = 0; i < prmarr.length; i++ ) {
		var tmparr = prmarr[i].split( "=" );
		params[tmparr[0]] = tmparr[1];
	    }
	    return params;
	}

	function enableDualListBox() {
	    if ($('#selected-list_wdm_selections').length) {
	       $('#selected-list_wdm_selections').bootstrapDualListbox({
	           moveOnSelect: false,
		    filterTextClear: single_view_obj.show_all,
		      filterPlaceHolder: single_view_obj.filter,
		      moveSelectedLabel: single_view_obj.move_selected,
		      moveAllLabel: single_view_obj.move_all,
		      removeSelectedLabel: single_view_obj.remove_selected,
		      removeAllLabel: single_view_obj.remove_all, // true/false (forced true on androids, see the comment later)
		      helperSelectNamePostfix: '_helper', // 'string_of_postfix' / false
		      nonSelectedFilter: '', // string, filter the non selected options
		      selectedFilter: '', // string, filter the selected options
		      infoText: single_view_obj.showing_all, // text when all options are visible / false for no info text
		      infoTextFiltered: '<span class="label label-warning">Filtered</span> {0} from {1}', // when not all of the options are visible due to the filter
		      infoTextEmpty: single_view_obj.empty_list,
	       });

	       //Change "Move" button
	       var move_button = $('#bootstrap-duallistbox-nonselected-list_wdm_selections').parents('.box1').find('.move');
	       $('#bootstrap-duallistbox-nonselected-list_wdm_selections').parents('.box1').find('.moveall').before($(move_button));
	    }

	    if ( $( '#wdm_product_lists' ).length ) {
		$( '#wdm_product_lists' ).bootstrapDualListbox( {
		    moveOnSelect: false,
		    filterTextClear: single_view_obj.show_all,
		      filterPlaceHolder: single_view_obj.filter,
		      moveSelectedLabel: single_view_obj.move_selected,
		      moveAllLabel: single_view_obj.move_all,
		      removeSelectedLabel: single_view_obj.remove_selected,
		      removeAllLabel: single_view_obj.remove_all, // true/false (forced true on androids, see the comment later)
		      helperSelectNamePostfix: '_helper', // 'string_of_postfix' / false
		      nonSelectedFilter: '', // string, filter the non selected options
		      selectedFilter: '', // string, filter the selected options
		      infoText: single_view_obj.showing_all, // text when all options are visible / false for no info text
		      infoTextFiltered: '<span class="label label-warning">Filtered</span> {0} from {1}', // when not all of the options are visible due to the filter
		      infoTextEmpty: single_view_obj.empty_list,
		} ); //selectorMinimalHeight: 150

		//Change "Move" button
		var move_button = $( '#bootstrap-duallistbox-nonselected-list_wdm_product_lists' ).parents( '.box1' ).find( '.move' );
		$( '#bootstrap-duallistbox-nonselected-list_wdm_product_lists' ).parents( '.box1' ).find( '.moveall' ).before( $( move_button ) );
	    }
	}

	function disableDualListAndOptionType() {

		// select user
		$('#selected-list_wdm_selections').attr('disabled', 'disabled');
		$( '.csp-selection-list-wrapper .form-group' ).css( 'opacity', '0.5' );

	    //option type

	    $( '#wdm_setting_option_type' ).attr( 'disabled', 'disabled' );
	    $( '.csp-product-list.csp-selection-wrapper-sections .form-group' ).css( 'opacity', '0.5' );

	    //Dual Listbox controls

	    $( '.moveall' ).attr( 'disabled', 'disabled' );

	    $( '.removeall' ).attr( 'disabled', 'disabled' );

	    $( '.move' ).attr( 'disabled', 'disabled' );

	    $( '.remove' ).attr( 'disabled', 'disabled' );

	    $('.filter.form-control').attr( 'disabled', 'disabled' );
	    $( '.wdm-csp-single-view-from-group').css('opacity', '0.5');
	    $('#bootstrap-duallistbox-nonselected-list_wdm_product_lists').attr( 'disabled', 'disabled' );
	    $('#bootstrap-duallistbox-selected-list_wdm_product_lists').attr( 'disabled', 'disabled' );
	}

	function disableRuleTable() {
	    //Buttons

	    $( '#wdm_csp_save_changes' ).attr( 'disabled', 'disabled' );
	    $( '#wdm_csp_set_price' ).attr( 'disabled', 'disabled' );

	    //Query Title
	    $( '#wdm_csp_query_title' ).attr( 'disabled', 'disabled' );

	    //Datatable
	    var table = $( '#example' ).DataTable();
	    table.$( 'input' ).attr( 'disabled', 'disabled' );
	    table.$( 'select' ).attr( 'disabled', 'disabled' );

	    $( '.wdm-csp-product-details-list' ).css( 'opacity', '0.5' );
	}

	function disableEntries() {
	    disableDualListAndOptionType();

	    disableRuleTable()
	}



	function enableDualListAndOptionType() {
	    //option type

	    $( '#wdm_setting_option_type' ).removeAttr( 'disabled' );
		$( '.wdm-csp-single-view-from-group').css('opacity', '1');
	    //Dual Listbox

	    $( '.moveall' ).removeAttr( 'disabled' );

	    $( '.removeall' ).removeAttr( 'disabled' );

	    $( '.move' ).removeAttr( 'disabled' );

	    $( '.remove' ).removeAttr( 'disabled' );
		$('#bootstrap-duallistbox-nonselected-list_wdm_product_lists').removeAttr( 'disabled' );
		$('#bootstrap-duallistbox-selected-list_wdm_product_lists').removeAttr( 'disabled' );
		$('.filter.form-control').removeAttr( 'disabled' );

	    $( '.csp-selection-list-wrapper .form-group' ).css( 'opacity', '1' );
	    $('#selected-list_wdm_selections').removeAttr( 'disabled' );

	    $( '.csp-product-list.csp-selection-wrapper-sections .form-group' ).css( 'opacity', '1' );
	}

	function enableRuleTable() {

	    //Buttons

	    $( '#wdm_csp_save_changes' ).removeAttr( 'disabled' );
	    $( '#wdm_csp_set_price' ).removeAttr( 'disabled' );

	    //Query Title
	    $( '#wdm_csp_query_title' ).removeAttr( 'disabled' );


	    //Datatable
	    var table = $( '#example' ).DataTable();
	    if(enableAllFields) {
	    	table.$( 'input' ).removeAttr( 'disabled' );
	    }
	    table.$( 'select' ).removeAttr( 'disabled' );
	    
	    $( '.wdm-csp-product-details-list' ).css( 'opacity', '1' );
	    $( '.wdm-csp-product-details-list .row.form-group' ).css( 'opacity', '1' );
	}

	function enableEntries() {
	    enableDualListAndOptionType();
	    enableRuleTable();
	}

	function getOptionSelected()
	{
		var selectionType = $( "#wdm_setting_option_type" ).val() + 's';
		if(selectionType == 'customers') {
			return single_view_obj.customer_text;
        }
        if(selectionType == 'roles') {
            return single_view_obj.role_text;
        }
        if(selectionType == 'groups') {
            return single_view_obj.group_text;
        }		
	}

	function resetEntries() {
	    //Option Type

	    //Select box
	    $( '#selected-list_wdm_selections option' ).each( function () {
			$( this ).removeAttr( 'selected' )
			$('#bootstrap-duallistbox-nonselected-list_wdm_selections').append($(this));
	    } );

	    $( '#bootstrap-duallistbox-selected-list_wdm_product_lists option' ).each( function () {
			$( this ).removeAttr( 'selected' )
			$( '#bootstrap-duallistbox-nonselected-list_wdm_product_lists' ).append( $( this ) );
	    } );

	    $( '.wdm-csp-product-details-list' ).empty();

	    //Query Title
	    $( '#wdm_csp_query_title' ).val( '' );
	}

	$( 'body' ).delegate( '#csp_value', 'change', function () {

	});

	$( 'body' ).delegate( '.csp_single_view_action', 'change', function () {
		if ( parseInt($(this).val(), 10) == 1) {
			$(this).closest('tr').find('.csp_single_view_value').removeClass('csp-percent-discount');
		} else if ( parseInt($(this).val(), 10) == 2 ) {
			$(this).closest('tr').find('.csp_single_view_value').addClass('csp-percent-discount');
		}
	});

	$( 'body' ).delegate( '#wdm_edit_entries', 'click', function () {
	    disableDualListAndOptionType();
	    var selectionType = getOptionSelected();
	    $( "#wdm_csp_set_price" ).val(single_view_obj.change_text + selectionType + single_view_obj.change_product_selection);
	    
        enableRuleTable();
	    customerProductsSelectionFlag.flag = 0;
	    $( this ).hide();
	    $( '#wdm_back' ).hide();
	    $( '#wdm_csp_set_price' ).css( 'visibility', 'visible' );
	    $('.csp_single_view_qty').attr('disabled', 'disabled');
	} );

	$( 'body' ).delegate( '#wdm_clear_entries', 'click', function () {
	    enableEntries();
	    resetEntries();
	    $( this ).parent().remove();
	} );

	$( 'body' ).delegate( '#wdm_back', 'click', function () {
		window.history.back();
	} );

	function displaySearchSelection(option_type, selection_name)
	{
		console.log("here in display");
		jQuery.ajax({

            type: 'POST',
            url: single_view_obj.admin_ajax_path,
            dataType : 'json',
            data: {
                action: 'display_product_prices_selection',
                option_type : option_type,
                selection_name : selection_name
            },
            success: function (response) {//response is value returned from php
               $('#loading').remove();
               $('<div class="wdm-selection-price-list-wrapper"></div>').appendTo($('.wdm-csp-single-view-result-wrapper'));

               	$('.wdm-selection-price-list-wrapper').append( '<table cellpadding="0" cellspacing="0" border="0" class="display" id="example"></table>');

	        	var table = jQuery( '#example' ).dataTable( {
					"data": response,
					"columns": single_view_obj.title_names,
					//dom: 'Bfrtip',
					"columnDefs": [ { "targets": 3,
					"render": function ( data, type, full, meta ) {
						if(data === '--')
							return data;
						else
					      return '<a href="'+single_view_obj.query_log_link+data+'">'+data+'</a>';
					    }
					}],
					'language':{
					'lengthMenu': single_view_obj.length_menu,
					'info': single_view_obj.showing_info,
					'infoEmpty': single_view_obj.info_empty,
					'emptyTable': single_view_obj.empty_table,
					'infoFiltered': single_view_obj.info_filtered,
					'zeroRecords': single_view_obj.zero_records,
					'loadingRecords': single_view_obj.loading_records,
					'processing': single_view_obj.processing,
					'search': single_view_obj.search,
					'paginate': {
				        "first":        single_view_obj.first,
				        "previous":     single_view_obj.prev,
				        "next":         single_view_obj.next,
				        "last":         single_view_obj.last
				    },
				},
				});
            }
        });
	}

	$( 'body' ).delegate( '#wdm_csp_set_price', 'click', function () {

	    if ( customerProductsSelectionFlag.flag == 0 ) {
		enableDualListAndOptionType();
		disableRuleTable();

		customerProductsSelectionFlag.flag = 1;
		$( "#wdm_csp_set_price" ).val( 'Set Price' );
		$( '#wdm_csp_set_price' ).removeAttr( 'disabled' );
		$( 'html, body' ).animate( {
		    scrollTop: $( ".wdm-tab-info" ).offset().top
		}, 500 );
	    } else {

		$( 'div.error' ).remove();

		if ( $( '#bootstrap-duallistbox-selected-list_wdm_selections option' ).length > 0 && $( '#bootstrap-duallistbox-selected-list_wdm_product_lists option' ).length > 0 )
		{
			var TableObj = $('#example').DataTable();
			var data = TableObj.rows().data();
			// console.log(data);
		    // var currentProductCustomerId = 0;
		    // var currentProductCustomerId = 0;
			for ( var key in data ) {
				for (var i =0; i < data[key].length; i++) {
					if (jQuery(data[key][i]).hasClass('csp_single_view_action')) {
						retrieveAlreadySetProductsActions[jQuery(data[key][i]).attr('name')] = data[key][i];
			        }
					if (jQuery(data[key][i]).hasClass('csp_single_view_qty')) {
						retrieveAlreadySetProductsQty[jQuery(data[key][i]).attr('name')] = data[key][i];
			        }
					if (jQuery(data[key][i]).hasClass('csp_single_view_value')) {
						retrieveAlreadySetProducts[jQuery(data[key][i]).attr('name')] = data[key][i];
			        }
				}
			}
			        	// console.log(retrieveAlreadySetProductsActions.length);
			        	// console.log(retrieveAlreadySetProductsQty.length);
			        	// console.log(retrieveAlreadySetProducts.length);

		    customerProductsSelectionFlag.flag = 0;
		    disableDualListAndOptionType();
		    //Fetch Product Price List
			var selection_list = { };

		    $('#bootstrap-duallistbox-selected-list_wdm_selections option').each(function() {
		        selection_list[$(this).val()] = $(this).text();
		    });

		    var product_list = { };

		    $( '#bootstrap-duallistbox-selected-list_wdm_product_lists option' ).each( function () {
			product_list[$( this ).val()] = $( this ).text();

		    } );

		    //Backup current prices;
		 //    var count = 0;
		 //    var currentProductCustomerId = 0;
		 //    $( '.csp_single_view_value' ).each( function () {
			// currentProductCustomerId = $( this ).attr('name');
			// retrieveAlreadySetProducts[currentProductCustomerId] = $( this ).val();
			// count++;
		 //    } );

		 //    // console.log(retrieveAlreadySetProducts);
		 //    //Backup current actions
		 //    var count = 0;
		 //    var currentProductCustomerId = 0;
		 //    $( '.csp_single_view_action' ).each( function () {

			// currentProductCustomerId = $( this ).attr('name');
			// retrieveAlreadySetProductsActions[currentProductCustomerId] = $( this ).val();
			// count++;
		 //    } );

		 //    //Backup current quantity
		 //    var count = 0;
		 //    var currentProductCustomerId = 0;
		 //    $( '.csp_single_view_qty' ).each( function () {

			// currentProductCustomerId = $( this ).attr('name');
			// retrieveAlreadySetProductsQty[currentProductCustomerId] = $( this ).val();
			// count++;
		 //    } );

		    //Backup current title
		    if ( $( '#wdm_csp_query_title' ).length ) {
			retrieveExistingTitle.text = $( '#wdm_csp_query_title' ).val();
		    }

		    $( '.wdm-csp-product-details-list' ).empty();
		    $( '#loading' ).remove();
		    $( this ).after( '<img src="' + single_view_obj.loading_image_path + '" id="loading"/>' );

		    $.ajax( {
			type: 'POST',
			url: single_view_obj.admin_ajax_path,
			dataType: 'json',
			data: {
			    action: 'get_product_price_list',
			    selection_list: selection_list,
			    // selected_customer_names: selectedCustomerNames,
			    product_list: product_list,
			    option_type: $( '#wdm_setting_option_type' ).val()
			},
			success: function ( response ) { //response is value returned from php

			    $( '.wdm-csp-product-details-list' ).empty();
			    $( '#loading' ).remove();
			    console.log(response.value);

			    response = appendAlreadyExistingValues(response);

			    appendProductPriceTable( response );
			    var selectionType = getOptionSelected();
			    $( "#wdm_csp_set_price" ).val(single_view_obj.change_text + selectionType + single_view_obj.change_product_selection);
			    enableRuleTable();
			    if ( typeof getParamters.query_log !== 'undefined' ) {
					jQuery( "#wdm_csp_save_changes" ).val(single_view_obj.update_rule);
			    }

			}

		    } );

		} else if ( !$( '#bootstrap-duallistbox-selected-list_wdm_selections option' ).length ) {
			var selectionType = getOptionSelected();
		    $( '#wdm_csp_set_price' ).after( '<div class="error"><p>' + selectionType + single_view_obj.error_selection_empty + '</p></div>' );
		} else if ( !$( '#bootstrap-duallistbox-selected-list_wdm_product_lists option' ).length ) {
		    $( '#wdm_csp_set_price' ).after( '<div class="error"><p>' + single_view_obj.error_product_list_empty + '</p></div>' );
		}
	    }
	} ); //Set Price button click end

	function appendAlreadyExistingValues( response ) {
		var tempResponse = response;

		for (var i = 0; i < response.value.length; i++) {
			for (var j = 0; j < response.value[i].length; j++ ) {
				var tempAction,tempQty, tempProduct;
				try{
					tempAction = retrieveAlreadySetProductsActions[jQuery(response.value[i][j]).attr('name')];
					tempQty = retrieveAlreadySetProductsQty[jQuery(response.value[i][j]).attr('name')];
					tempProduct = retrieveAlreadySetProducts[jQuery(response.value[i][j]).attr('name')];
				} catch(err){
					tempProduct = tempAction = tempQty = undefined;
				}
				
				//loop through already stored actions and set those actions automatically
				if (jQuery(response.value[i][j]).hasClass('csp_single_view_action') && tempAction != undefined) {
					tempResponse.value[i][j] = retrieveAlreadySetProductsActions[jQuery(response.value[i][j]).attr('name')];
		        }

		        //loop through already stored quantities and set those values automatically
				if (jQuery(response.value[i][j]).hasClass('csp_single_view_qty') && tempQty != undefined) {
					tempResponse.value[i][j] = retrieveAlreadySetProductsQty[jQuery(response.value[i][j]).attr('name')];
		        }
		        
				//loop through already stored values and set those values automatically
				if (jQuery(response.value[i][j]).hasClass('csp_single_view_value') && tempProduct != undefined) {
					tempResponse.value[i][j] = retrieveAlreadySetProducts[jQuery(response.value[i][j]).attr('name')];
		        }
			}
		}

		return tempResponse;
	}

	function appendProductPriceTable( response ) {

	    $( '.wdm-csp-product-details-list' ).append( '<table cellpadding="0" cellspacing="0" border="0" class="display" id="example"></table>' );

	    var table = $( '#example' ).dataTable( {
		"data": response['value'],
		"columns": response['title_name'],
		"lengthMenu": [ 10, 50, 100, 150, 200, 250, 300 ],
		dom: 'Blfrtip',
		//stateSave: true,
		buttons: [ {
			extend: 'colvis',
			columns: ':not(:gt(7))'
		    } ],
		"columnDefs": [ {
			"targets": 5,
			"orderable": false
		    }, ],
		language: {
			'lengthMenu': single_view_obj.length_menu,
			'info': single_view_obj.showing_info,
			'infoEmpty': single_view_obj.info_empty,
			'emptyTable': single_view_obj.empty_table,
			'infoFiltered': single_view_obj.info_filtered,
			'zeroRecords': single_view_obj.zero_records,
			'loadingRecords': single_view_obj.loading_records,
			'processing': single_view_obj.processing,
			'search': single_view_obj.search,
			'paginate': {
		        "first":        single_view_obj.first,
		        "previous":     single_view_obj.prev,
		        "next":         single_view_obj.next,
		        "last":         single_view_obj.last
    		},
		    buttons: {
			colvis: single_view_obj.hide_column_msg
		    }
		}
	    } );


	    
	    $( '.wdm-csp-product-details-list' ).hide();
	    $( '.wdm-csp-product-details-list' ).append( response['query_input'] );

	    if ( retrieveExistingTitle.hasOwnProperty( 'text' ) ) {
			$( '#wdm_csp_query_title' ).val( retrieveExistingTitle.text );
	    }

	    $( '.wdm-csp-product-details-list' ).show();

	    $( '.progress' ).hide();
	    $( '.wdm-csp-product-details-list' ).css( 'opacity', '1' );
	    $( 'html, body' ).animate( {
			scrollTop: $( ".wdm-csp-product-details-list" ).offset().top
	    }, 500 );


/*var table = jQuery('#example').DataTable();

var data = table
    .rows()
    .data();

alert( 'The table has ' + data.length + ' records' );*/
	    // var TableObj = $('#example').DataTable();
	    // console.log(TableObj.rows().data());
	}

	$( 'body' ).delegate( '#wdm_csp_save_changes', 'click', function () {

	    $( this ).parent().find( '.error' ).remove();
	    $('.csp_single_view_qty').removeAttr('disabled');
	    if ( $( '#wdm_csp_query_title' ).val().length === 0 ) {
		$( this ).after( '<div class="error"><p>' + single_view_obj.error_query_title_empty + '</p></div>' );
		return false;
	    }

	    var rows = $( "#example" ).dataTable().fnGetNodes();
	    var cells = [ ];
	    var nanVal = [ ];
	    var negativeVal = [ ];
	    var invalidQty = [ ];
	    var maxVal = false;
	    var some_fields_empty = false; //used to check if some fields are empty or not
		var all_fields_valid = false; //used to check if All Fields consist valid value

	    for ( var i = 0; i < rows.length; i++ ) {
			var value = $( rows[i] ).find( "td:eq(7)" ).find( '#csp_value' ).val();
			var discType = $( rows[i] ).find( "td:eq(5)" ).find( '.csp_single_view_action' ).val();
			var minQty = $( rows[i] ).find( "td:eq(6)" ).find( '.csp_single_view_qty' ).val();

			var max = '';

			if ( parseInt(discType, 10) == 2 ) { // used to check if discount type is % then max value can be 100
				if ( parseInt(value, 10) > 100 ) { // if discount type is % and value greater than 100 
					maxVal = true;
					break;
				}
			}

			var convertToDBStorageVal = reverse_number_format(value, wdm_csp_function_object.decimals, wdm_csp_function_object.decimal_separator, wdm_csp_function_object.thousand_separator);
			// if (minQty)
			var numericCheck = isNaN(convertToDBStorageVal);

			// var numericCheck = isNaN( value );
			var negativeCheck = ( convertToDBStorageVal < 0 ) ? true : false;

			if ( value == '' ) {
			    cells.push( value );
			} else if( numericCheck === true ) {
				nanVal.push( value );
			} else if( negativeCheck ) {
				negativeVal.push( value );
			} else if ( cells.length > 0 || nanVal.length > 0 || negativeVal.length > 0 ) {
			    break;
			}

			minQty = minQty.trim();
	
			if( (minQty != '' && !isPositiveInt(minQty)) || (minQty == '') ) {
				invalidQty.push( minQty );
				break;
			}
	    }

	    if ( cells.length > 0 ) {
			if ( rows.length === cells.length ) {
			    $( this ).after( '<div class="error"><p>' + single_view_obj.error_all_fields_empty + '</p></div>' );
			    return false;
			} else {
			    some_fields_empty = true;
			}
	    }

	    if(invalidQty.length > 0) {
	    	$( this ).after( '<div class="error"><p>' + single_view_obj.invalid_quantity_value + '</p></div>' );
	    	return false;
	    }

	    if ( nanVal.length > 0 ) {
	    	$( this ).after( '<div class="error"><p>' + single_view_obj.error_field_not_numeric + '</p></div>' );
	    	return false;
		}

	    if ( negativeVal.length > 0 ) {
	    	$( this ).after( '<div class="error"><p>' + single_view_obj.error_field_negative_number + '</p></div>' );
	    	return false;
		}

		if ( maxVal ) {
	    	$( this ).after( '<div class="error"><p>' + single_view_obj.error_field_max_val + '</p></div>' );
	    	return false;
		}

	    if ( $( '#example' ).find( '.wdm_error' ).length ) {
		//Errors present
		var confirmCheck = confirm( single_view_obj.confirm_msg_if_error );
	    } else if ( some_fields_empty ) {
		var confirmCheck = confirm( single_view_obj.confirm_msg_if_empty );
	    } else {
		var confirmCheck = confirm( single_view_obj.confirm_msg );
	    }

	    if ( confirmCheck == true ) {
		var table = $( '#example' ).DataTable();
		var product_values = table.$( 'input.csp_single_view_value' ).serialize();
		var product_actions = table.$( 'select' ).serialize();
		var product_quantities = table.$( 'input.csp_single_view_qty' ).serialize();

		var selection_list = '';
	    $('#bootstrap-duallistbox-selected-list_wdm_selections option').each(function() {
	        selection_list += $(this).val() + ',';
	    });
		
		var product_list = '';
		$( "#example tbody tr" ).each( function () {
			product_list += $( this ).find( "td" ).eq( 0 ).text() + ',';
	    } );

		//Send AJAX request

		$( '#loading' ).remove();
		$( this ).after( '<img src="' + single_view_obj.loading_image_path + '" id="loading"/>' );

		//Add/Reset Progress bar

		var pb = $( '.progress .progress-bar' );
		$( pb ).attr( 'data-transitiongoal', 0 ).progressbar( {
		    display_text: 'center',
		    transition_delay: 10
		} );
		$( '.csp-log-progress' ).html( single_view_obj.progress_loading_text );

		$( '.progress' ).show();
		$( '.csp-log-progress' ).show();

		progress_timer = setTimeout( getProgress, 1000 );

		$( '.wdm_result' ).remove();

		$.ajax( {
		    type: 'POST',
		    url: single_view_obj.admin_ajax_path,
		    data: {
			action: 'save_query_log',
			option_type: $( '#wdm_setting_option_type' ).val(),
			selection_list: selection_list,
			product_list: product_list,
			product_values: product_values,
			product_actions: product_actions,
			product_quantities: product_quantities,
			current_query_id: getParamters.query_log,
			query_title: $( '#wdm_csp_query_title' ).val(),
			option_name: $( '#wdm_csp_query_time' ).val()
		    },
		    success: function ( response ) { //response is value returned from php
			clearTimeout( progress_timer );
			$( '#loading' ).remove();
			$( '.progress .progress-bar' ).attr( 'data-transitiongoal', 100 ).progressbar( {
			    display_text: 'center'
			} );
			$( '.csp-log-progress' ).html( single_view_obj.progress_complete_text );
			$( '.progress' ).hide();
			$( '.csp-log-progress' ).hide();
			$( '#wdm_csp_save_changes' ).after( response );
			$('.csp_single_view_qty').attr('disabled', 'disabled');
			if ( typeof getParamters.query_log === 'undefined' ) {
			    getParamters.query_log = $( '.wdm_result' ).attr( 'rule_id' );
			    jQuery( "#wdm_csp_save_changes" ).val( single_view_obj.update_rule );
			}

		    }
		} );

	    }

	} ); //Save changes button clicked

	function getProgress() {
	    var val = $( '.progress .progress-bar' ).attr( 'data-transitiongoal' );
	    if ( val < 99 ) {
		progress_timer = setTimeout( getProgress, 1000 );
	    }
	    if ( val == 0 ) {
		$( '.csp-log-progress' ).html(single_view_obj.progress_loading_text);
	    }

	    $.ajax( {
		type: 'POST',
		url: single_view_obj.admin_ajax_path,
		dataType: 'json',
		data: {
		    action: 'get_progress_status',
		    option_name: $( '#wdm_csp_query_time' ).val()
		},
		success: function ( response ) { //response is value returned from php
		    $( '.progress .progress-bar' ).attr( 'data-transitiongoal', parseInt( response['value'] ) ).progressbar( {
			display_text: 'center'
		    } );
		    $( '.csp-log-progress' ).html( response['status'] );
		}
	    } );
	}

    } );

} )( jQuery );