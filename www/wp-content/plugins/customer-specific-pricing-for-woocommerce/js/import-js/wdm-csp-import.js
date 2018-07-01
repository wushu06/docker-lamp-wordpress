// @author WisdmLabs

var csp_rows_read = 0;
var csp_insert_cnt = 0;
var csp_update_cnt = 0;
var csp_skip_cnt = 0;

function senddata(filename, type, batchNumber){
    jQuery('.update-nag').hide();
    jQuery('.wdm_import_form').hide();
    if ( csp_rows_read == 0 ) {
        jQuery( '.import-header' ).text(wdm_csp_import.loading_text);
        jQuery( '.import-header' ).append( '<img src="' + wdm_csp_import.loading_image_path + '" id="loading"/>' );
        jQuery( "#wdm_import_data" ).show();
    }
    var file = filename;
    var fileType = type;
    var action;

    if ( fileType == 'role' ) {
        action = 'import_role_specific_file'
    } else if ( fileType == 'user' ) {
        action = 'import_customer_specific_file';
    } else if ( fileType == 'group' ) {
        action = 'import_group_specific_file';
    }

    jQuery.ajax({
        type: "POST",
        url: wdm_csp_import.admin_ajax_path,
        data: {
            'action' : action,
            'file_name' : file,
            'file_type' : fileType,
            'batch_number' : batchNumber,
            '_wp_import_nonce' : wdm_csp_import.import_nonce
        },
        dataType: "json",
        async: true,
        success: function(response){
            var rows = "";
            var records = response.records;
            for ( var item in records ) {
                rows += "<tr><td>" + records[item].product_id + "</td><td>" + records[item].applicable_entity + "</td><td>" + records[item].min_qty + "</td><td>" + records[item].active_price + "</td><td>" + records[item].discount_type + "</td><td>" + records[item].record_status + "</td></tr>";
            }

            // Increment the counters for Footer
            csp_rows_read += response.rows_read;
            csp_insert_cnt += response.insert_cnt;
            csp_update_cnt += response.update_cnt;
            csp_skip_cnt += response.skip_cnt;

            jQuery("#import_table").find('tbody').append(rows);
            if ( csp_rows_read == parseInt(jQuery('input[name="counters"]').attr('data-no_of_rows'), 10) ) {
                jQuery( '#loading' ).remove();
                jQuery( '.import-header' ).text(wdm_csp_import.header_text);
                //Success Message
                jQuery('#wdm_import_data').after('<div class="wdm_summary"><p>' + wdm_csp_import.total_no_of_rows + csp_rows_read + wdm_csp_import.total_insertion + csp_insert_cnt + wdm_csp_import.total_updated + csp_update_cnt + wdm_csp_import.total_skkiped + csp_skip_cnt + '</p></div><div><a href="'+wdm_csp_import.import_page_url+'">Go Back To Import</a></div>');
                jQuery.ajax({
                    type: "POST",
                    url: wdm_csp_import.admin_ajax_path,
                    data: {
                        'action' : 'drop_batch_numbers',
                        'file_type' : fileType,
                    },
                    success: function(response){

                    } 
                });

                jQuery( '.wdm_message_p' ).text(wdm_csp_import.import_successfull);
                jQuery( '#wdm_message' ).show();
            }
        }
    });
}

jQuery( 'document' ).ready( function ( jQuery ) {
    jQuery( '#wdm_message' ).hide();
    jQuery( '#dd_show_import_options' ).on( "change", function () {
        jQuery( "#wdm_import_data" ).hide();
        jQuery( '#wdm_message' ).hide();
        setCSVTemplateUrl(jQuery(this).val());
    } );

    jQuery( ".wdm_import_form" ).submit( function (event) {
        if ( jQuery('#wdm_message').hasClass('error') ) {
            jQuery( '.import-header' ).hide();
            jQuery( "#wdm_import_data" ).hide();
            event.preventDefault();
        }
    } );

    function setCSVTemplateUrl(importType) {
        switch(importType) {

            case 'Wdm_Group_Specific_Pricing_Import':
                jQuery('.sample-csv-import-template-link').attr('href', wdm_csp_import.templates_url + 'group_specific_pricing_sample.csv');
                jQuery('span.import-type').text(wdm_csp_import.group_specific_sample);
                break;

            case 'Wdm_Role_Specific_Pricing_Import':
                jQuery('.sample-csv-import-template-link').attr('href', wdm_csp_import.templates_url + 'role_specific_pricing_sample.csv');
                jQuery('span.import-type').text(wdm_csp_import.role_specific_sample);
                break;

            default:
                jQuery('.sample-csv-import-template-link').attr('href', wdm_csp_import.templates_url + 'user_specific_pricing_sample.csv');
                jQuery('span.import-type').text(wdm_csp_import.user_specific_sample);
        }
    }

    jQuery(document).on( 'click', '.wusp-import-notice .notice-dismiss', function() {

        jQuery.ajax({
            url: wdm_csp_import.admin_ajax_path,
            data: {
                action: 'wusp_dismiss_import_notice'
            }
        })

    });
} );
