/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

jQuery('document').ready(function (jQuery) {
    jQuery('#wdm_message').hide();
    jQuery(".wdm_export_form").submit(function () {
        return false;
    });
    jQuery('#export').click(function () {
        jQuery('#wdm_message').hide();
        var val = jQuery('#dd_show_export_options').val();
        jQuery.ajax({
            type: 'POST',
            url: wdm_csp_export_ajax.ajaxurl, //'http://csp.mirealux.com/wp-admin/admin-ajax.php', //ajaxurl,
            data: {
                action: 'create_csv',
                option_val: val,
                _wpnonce : wdm_csp_export_ajax.export_nonce
            },
            success: function (response) {//response is value returned from php
                if (response.search(".csv") === -1) {
                    jQuery('#wdm_message').addClass('error');
                    if( val === 'User' || val ==='user') {
                        jQuery('.wdm_message_p').text(wdm_csp_export_ajax.please_Assign_valid_user_file_msg);
                    } else if( val === 'Role' || val ==='role') {
                        jQuery('.wdm_message_p').text(wdm_csp_export_ajax.please_Assign_valid_role_file_msg);
                    } else if( val === 'Group' || val ==='group' ) {
                        jQuery('.wdm_message_p').text(wdm_csp_export_ajax.please_Assign_valid_group_file_msg);
                    }
                    jQuery('#wdm_message').show();
                }
                else {
                    var link = document.createElement("a");
                    location.href = encodeURI(response);//'http://csp.mirealux.com/wp-content/uploads/role.csv';  
                }
            }
        });
    });
});
