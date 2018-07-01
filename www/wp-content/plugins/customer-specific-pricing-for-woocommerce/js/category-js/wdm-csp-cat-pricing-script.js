jQuery(document).ready(function () {
    jQuery( "#accordion" ).accordion({
    	clearStyle: true,
      	heightStyle: "content",
      	collapsible: true,
    });

    var user_row = jQuery( 'div.user-row' ).size() + 1;
    var role_row = jQuery( 'div.role-row' ).size() + 1;
    var group_row = jQuery( 'div.group-row' ).size() + 1;

    // jQuery( document ).delegate('.add_new_user_row_image','click', function () {
        // if ( user_row === 1 ) {
        // }
    //     jQuery(this).closest('div.category-row').clone().appendTo('.user_data');
    //     jQuery(this).remove();
    //     user_row++;
    // });

    jQuery( document ).delegate('.add_new_user_row_image','click', function () {
        if ( user_row === 1 ) {
        }

        var clonedElement = jQuery(this).parents('div.category-row').clone();
        jQuery(clonedElement).find('select[id^=wdm_woo_username] option[value="-1"]').attr('selected',true);
        jQuery(clonedElement).find('select[name^=wdm_woo_user_category] option[value="-1"]').attr('selected',true);
        jQuery(clonedElement).find('select[name^=wdm_user_price_type] option[value="-1"]').attr('selected',true);
        jQuery(clonedElement).find('input[name^=wdm_user_qty]').removeClass('wdm_error').val('');
        jQuery(clonedElement).find('input[name^=wdm_user_value]').removeClass('wdm_error').val('');
        jQuery(clonedElement).appendTo('.user_data');
        jQuery(this).remove();

        user_row++;
    });

    jQuery( document ).delegate('.add_new_role_row_image','click', function () {
        if ( role_row === 1 ) {
        }

        var clonedElement = jQuery(this).parents('div.category-row').clone();
        jQuery(clonedElement).find('select[id^=wdm_woo_roles] option[value="-1"]').attr('selected',true);
        jQuery(clonedElement).find('select[name^=wdm_woo_role_category] option[value="-1"]').attr('selected',true);
        jQuery(clonedElement).find('select[name^=wdm_role_price_type] option[value="-1"]').attr('selected',true);
        jQuery(clonedElement).find('input[name^=wdm_role_qty]').removeClass('wdm_error').val('');
        jQuery(clonedElement).find('input[name^=wdm_role_value]').removeClass('wdm_error').val('');        
        jQuery(clonedElement).appendTo('.role_data');
        jQuery(this).remove();
        role_row++;
    });

    jQuery( document ).delegate('.add_new_group_row_image','click', function () {
        var clonedElement = jQuery(this).parents('div.category-row').clone();
        jQuery(clonedElement).find('select[id^=wdm_woo_groups] option[value="-1"]').attr('selected',true);
        jQuery(clonedElement).find('select[name^=wdm_woo_group_category] option[value="-1"]').attr('selected',true);
        jQuery(clonedElement).find('select[name^=wdm_group_price_type] option[value="-1"]').attr('selected',true);
        jQuery(clonedElement).find('input[name^=wdm_group_qty]').removeClass('wdm_error').val('');
        jQuery(clonedElement).find('input[name^=wdm_group_value]').removeClass('wdm_error').val('');        
        jQuery(clonedElement).appendTo('.group_data');
        jQuery(this).remove();
        group_row++;
    });


    // jQuery(document).delegate('.remove_user_row_image', 'click', function() {
    //     jQuery(this).closest('div.category-row').remove();
    //     user_row--;
    // });

    jQuery(document).delegate('.remove_user_row_image', 'click', function() {
        if ( user_row === 2 ) {
            var clonedElement = jQuery(this).parents('div.category-row').clone();
            jQuery(this).closest('div.category-row').remove();
            jQuery(clonedElement).find('select[id^=wdm_woo_username] option[value="-1"]').attr('selected',true);
            jQuery(clonedElement).find('select[name^=wdm_woo_user_category] option[value="-1"]').attr('selected',true);
            jQuery(clonedElement).find('select[name^=wdm_user_price_type] option[value="-1"]').attr('selected',true);
            jQuery(clonedElement).find('input[name^=wdm_user_qty]').removeClass('wdm_error').val('');
            jQuery(clonedElement).find('input[name^=wdm_user_value]').removeClass('wdm_error').val('');
            jQuery(clonedElement).appendTo('.user_data');
        } else {
            jQuery(this).closest('div.category-row').remove();
            addUsersLastRowButton();
            user_row--;
        }
    });


    jQuery(document).delegate('.remove_role_row_image', 'click', function() {
        if ( role_row === 2 ) {
            var clonedElement = jQuery(this).parents('div.category-row').clone();
            jQuery(this).closest('div.category-row').remove();
            jQuery(clonedElement).find('select[id^=wdm_woo_roles] option[value="-1"]').attr('selected',true);
            jQuery(clonedElement).find('select[name^=wdm_woo_role_category] option[value="-1"]').attr('selected',true);
            jQuery(clonedElement).find('select[name^=wdm_role_price_type] option[value="-1"]').attr('selected',true);
            jQuery(clonedElement).find('input[name^=wdm_role_qty]').removeClass('wdm_error').val('');
            jQuery(clonedElement).find('input[name^=wdm_role_value]').removeClass('wdm_error').val('');        
            jQuery(clonedElement).appendTo('.role_data');
        } else {
            jQuery(this).closest('div.category-row').remove();
            addRolesLastRowButton();
            role_row--;
        }
    });

    jQuery(document).delegate('.remove_group_row_image', 'click', function() {
        if ( group_row === 2 ) {
            var clonedElement = jQuery(this).parents('div.category-row').clone();
            jQuery(this).closest('div.category-row').remove();
            jQuery(clonedElement).find('select[id^=wdm_woo_groups] option[value="-1"]').attr('selected',true);
            jQuery(clonedElement).find('select[name^=wdm_woo_group_category] option[value="-1"]').attr('selected',true);
            jQuery(clonedElement).find('select[name^=wdm_group_price_type] option[value="-1"]').attr('selected',true);
            jQuery(clonedElement).find('input[name^=wdm_group_qty]').removeClass('wdm_error').val('');
            jQuery(clonedElement).find('input[name^=wdm_group_value]').removeClass('wdm_error').val('');        
            jQuery(clonedElement).appendTo('.group_data');
        } else {
            jQuery(this).closest('div.category-row').remove();
            var curr = jQuery('.group-row:last').find('.add_remove_button');
            addGroupsLastRowButton();
            group_row--;
        }
    });

    function addUsersLastRowButton()
    {
        var curr = jQuery('.user-row:last').find('.add_remove_button');
        
        var isPresent = jQuery('.user-row:last').find('.add_remove_button img').hasClass('add_new_user_row_image');
        if (!isPresent) {
            jQuery('<img class="add_new_user_row_image" title="Add Row" src="'+cat_pricing_object.add_image_path+'">').appendTo(curr);
        } else {
        }
    }

    function addRolesLastRowButton()
    {
        var curr = jQuery('.role-row:last').find('.add_remove_button');
        
        var isPresent = jQuery('.role-row:last').find('.add_remove_button img').hasClass('add_new_role_row_image');
        if (!isPresent) {
            jQuery('<img class="add_new_role_row_image" title="Add Row" src="'+cat_pricing_object.add_image_path+'">').appendTo(curr);
        } else {
        }
    }

    function addGroupsLastRowButton()
    {
        var curr = jQuery('.group-row:last').find('.add_remove_button');

        var isPresent = jQuery('.group-row:last').find('.add_remove_button img').hasClass('add_new_group_row_image');
        if (!isPresent) {
            jQuery('<img class="add_new_group_row_image" title="Add Row" src="'+cat_pricing_object.add_image_path+'">').appendTo(curr);
        } else {
        }
    }

    jQuery( document ).delegate( '.csp_wdm_action', 'change', function () {
        var price_selector = jQuery(this).closest('div').find('.wdm_price');

        if(jQuery(this).val() == 1) {
            if(price_selector.hasClass('csp-percent-discount')) {
                price_selector.removeClass('csp-percent-discount');
            }
        } else {
            price_selector.addClass('csp-percent-discount');
        }
        highlightPriceError(price_selector);
    });

    //If qty is not valid, then highlight the qty box
    jQuery( document ).delegate( ".wdm_qty", 'focusout', function () {
        var current_quantity = jQuery( this ).val();
        if(!isPositiveInt(current_quantity) && cspGetClosestElement(this, '.wdm_price').val()!="") {
            jQuery( this ).addClass( 'wdm_error' );
        } else {
            if(jQuery( this ).hasClass( 'wdm_error' )) {
                jQuery( this ).removeClass( 'wdm_error' );
            }
        }

    } ); //end focusout

    //If Price is not valid, then highlight the Price box
    jQuery( document ).delegate('.wdm_price', 'focusout', function () {
        highlightPriceError(jQuery(this));
        var qtyField = cspGetClosestElement(this, '.wdm_qty');
        if (jQuery(this).val() != '' && jQuery(qtyField).val() == '') {
            jQuery(qtyField).addClass( 'wdm_error' );
        }
    }); //end live

    function userSelectionEmpty()
    {
        jQuery("select[id^=wdm_woo_username]").filter(function () {
            if (jQuery(this).val() === '-1') {
                removeEmptyUserRows(this);
            }
        });

        jQuery("select[name^=wdm_woo_user_category]").filter(function () {
            if (jQuery(this).val() === '-1') {
                removeEmptyUserRows(this);
            }
        });

        jQuery("select[name^=wdm_user_price_type]").filter(function () {
            if (jQuery(this).val() == '-1') {
                removeEmptyUserRows(this);
            }
        });

        jQuery("input[name^=wdm_user_qty]").filter(function () {
            if (!jQuery(this).val()) {
                removeEmptyUserRows(this);
            }
        });

        jQuery("input[name^=wdm_user_value]").filter(function () {
            if (!jQuery(this).val()) {
                removeEmptyUserRows(this);
            }
        });
    }

    function groupSelectionEmpty()
    {        
        jQuery("select[id^=wdm_woo_groupname]").filter(function () {
            if (jQuery(this).val() === '-1') {
                removeEmptyGroupRows(this);
            }
        });

        jQuery("select[name^=wdm_woo_group_category]").filter(function () {
            if (jQuery(this).val() === '-1') {
                removeEmptyGroupRows(this);
            }
        });

        jQuery("select[name^=wdm_group_price_type]").filter(function () {
            if (jQuery(this).val() === '-1') {
                removeEmptyGroupRows(this);
            }
        });

        jQuery("input[name^=wdm_group_qty]").filter(function () {
            if (!jQuery(this).val()) {
                removeEmptyGroupRows(this);
            }
        });

        jQuery("input[name^=wdm_group_value]").filter(function () {
            if (!jQuery(this).val()) {
                removeEmptyGroupRows(this);
            }
        });
    }

    function roleSelectionEmpty()
    {   
        jQuery("select[id^=wdm_woo_roles]").filter(function () {
            if (jQuery(this).val() === '-1') {
                removeEmptyRoleRows(this);
            }
        });

        jQuery("select[name^=wdm_woo_role_category]").filter(function () {
            if (jQuery(this).val() === '-1') {
                removeEmptyRoleRows(this);
            }
        });

        jQuery("select[name^=wdm_role_price_type]").filter(function () {
            if (jQuery(this).val() === '-1') {
                removeEmptyRoleRows(this);
            }
        });

        jQuery("input[name^=wdm_role_qty]").filter(function () {
            if (!jQuery(this).val()) {
                removeEmptyRoleRows(this);
            }
        });

        jQuery("input[name^=wdm_role_value]").filter(function () {
            if (!jQuery(this).val()) {
                removeEmptyRoleRows(this);
            }
        });
    }

    function removeEmptyUserRows($this)
    {
        jQuery($this).closest('.user-row').remove();
        addUsersLastRowButton();
    }

    function removeEmptyRoleRows($this)
    {
        jQuery($this).closest('.role-row').remove();
        addRolesLastRowButton();
    }

    function removeEmptyGroupRows($this)
    {
        jQuery($this).closest('.group-row').remove();
        addGroupsLastRowButton();
    }

    function wdm_error_function(error_location = 'top_of_page') {
        jQuery('#wdm_message').remove();
        var wdm_error_flag = 1;

        userSelectionEmpty();
        roleSelectionEmpty();
        groupSelectionEmpty();

        jQuery(".wdm_price").filter(function () {
            if (jQuery(this).hasClass('wdm_error')) {
                return wdm_error_flag = 0;
            }
        });

        jQuery( ".wdm_qty" ).filter( function () {
            if ( jQuery( this ).hasClass( 'wdm_error' ) ) {
                return wdm_error_flag = 0;
            }
        } );
        
        if (wdm_error_flag === 0) {
            var messageText = wdm_csp_function_object.please_verify_prices;
            return preventSubmission(messageText, error_location);
        }
    }

    //When User edits the invalid field, clear the background of that field
    jQuery( document ).delegate( ".wdm_qty", 'focusin', function () {
        jQuery( this ).removeClass( 'wdm_error' );
    } ); //end focusin

    //When User edits the invalid field, clear the background of that field
    jQuery( document ).delegate('.wdm_price', 'focusin', function () {
        jQuery(this).removeClass('wdm_error');
    }); //end live

    jQuery( document ).delegate('.wdm_price', 'change',function(){
        highlightPriceError(jQuery(this));
    });

    // jQuery( document ).delegate('#cat_pricing', 'click', function(){
    //     alert("Hi");
    //     jQuery(this).after('<img src="' + cat_pricing_object.loading_image_path + '" id="loading"/>');
    //     return wdm_error_function();      
    // });

    jQuery("form").submit(function (e) {
       // confirm("Submit?");
        return wdm_error_function();      
    }); //end submit
    
    // For adding new row
    // jQuery('#ui-id-2 > div:nth-child(2)').clone().appendTo('.user_data');

    function clearUser()
    {

    }
});