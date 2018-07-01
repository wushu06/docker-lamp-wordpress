(function( $ ) {
	'use strict';

	/**
	 * All of the code for your admin-specific JavaScript source
	 * should reside in this file.
	 *
	 * Note that this assume you're going to use jQuery, so it prepares
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

	 $(function() {

	 	$('#wdm_setting_option_type').change(function(){

			var option_type = jQuery(this).val();
			$('.wdm-csp-single-view-result-wrapper').empty();
			$('#loading').remove();

			if(option_type != -1)
			{
				$(this).after('<img src="' + single_view_obj.loading_image_path + '" id="loading"/>');

				//Send AJAX request

				jQuery.ajax({

		            type: 'POST',
		            url: single_view_obj.admin_ajax_path,
		            data: {
		                action: 'get_search_selection_result',
		                option_type : option_type,
		                single_view_action : 'search'
		            },
		            success: function (response) {//response is value returned from php
		               $('#loading').remove();
		               $('.wdm-csp-single-view-result-wrapper').append(response);
		            }
		        });
			}

		});//option type selection end

		$('body').delegate('#selected-list_wdm_selections','click',function(){

			var option_type = $('#wdm_setting_option_type').val();
			var selection_name = $(this).val();

			$('.wdm-selection-price-list-wrapper').remove();
			$('#loading').remove();

			if(selection_name != -1)
			{
				$(this).after('<img src="' + single_view_obj.loading_image_path + '" id="loading"/>');

				//Send AJAX request
				// displaySearchSelection(option_type, selection_name);
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
							"columnDefs": [ { "targets": 4,
							"render": function ( data, type, full, meta ) {
								if(data === '--')
									return data;
								else
							      return '<a href="'+single_view_obj.query_log_link+data+'&selection_name='+selection_name+'">'+data+'</a>';
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

		});//Selection type selection end

		// function displaySearchSelection(option_type, selection_name)
		// {
		// 	jQuery.ajax({

	 //            type: 'POST',
	 //            url: single_view_obj.admin_ajax_path,
	 //            dataType : 'json',
	 //            data: {
	 //                action: 'display_product_prices_selection',
	 //                option_type : option_type,
	 //                selection_name : selection_name
	 //            },
	 //            success: function (response) {//response is value returned from php
	 //               $('#loading').remove();
	 //               $('<div class="wdm-selection-price-list-wrapper"></div>').appendTo($('.wdm-csp-single-view-result-wrapper'));

	 //               	$('.wdm-selection-price-list-wrapper').append( '<table cellpadding="0" cellspacing="0" border="0" class="display" id="example"></table>');

		//         	var table = jQuery( '#example' ).dataTable( {
		// 				"data": response,
		// 				"columns": single_view_obj.title_names,
		// 				//dom: 'Bfrtip',
		// 				"columnDefs": [ { "targets": 3,
		// 				"render": function ( data, type, full, meta ) {
		// 					if(data === '--')
		// 						return data;
		// 					else
		// 				      return '<a href="'+single_view_obj.query_log_link+data+'">'+data+'</a>';
		// 				    }
		// 				}],
		// 				'language':{
		// 				'lengthMenu': single_view_obj.length_menu,
		// 				'info': single_view_obj.showing_info,
		// 				'infoEmpty': single_view_obj.info_empty,
		// 				'emptyTable': single_view_obj.empty_table,
		// 				'infoFiltered': single_view_obj.info_filtered,
		// 				'zeroRecords': single_view_obj.zero_records,
		// 				'loadingRecords': single_view_obj.loading_records,
		// 				'processing': single_view_obj.processing,
		// 				'search': single_view_obj.search,
		// 				'paginate': {
		// 			        "first":        single_view_obj.first,
		// 			        "previous":     single_view_obj.prev,
		// 			        "next":         single_view_obj.next,
		// 			        "last":         single_view_obj.last
		// 			    },
		// 			},
		// 			});
	 //            }
	 //        });
		// }

	 });

})( jQuery );