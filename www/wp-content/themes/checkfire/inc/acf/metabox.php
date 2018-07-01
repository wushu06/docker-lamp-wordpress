<?php

/*
* add boxes meta
*/

add_action('admin_init', 'gpm_add_meta_boxes', 2);

function gpm_add_meta_boxes()
{
    add_meta_box('gpminvoice-group', 'Custom Repeatable', 'Repeatable_meta_box_display', 'page', 'normal', 'default');
}

function Repeatable_meta_box_display()
{
    global $post;
    $gpminvoice_group = get_post_meta($post->ID, 'customdata_group', true);
    wp_nonce_field('gpm_repeatable_meta_box_nonce', 'gpm_repeatable_meta_box_nonce');


    ?>
    <script type="text/javascript">
        jQuery(document).ready(function ($) {

            var heroSlider = false,
                twoCol = false,
                fullW = false;

            $('#mbSelect').on('change', function() {
                $('#add-row').removeClass('disabled');
                if($(this).val() === 'hero_slider' ){

                    heroSlider = true;
                    twoCol = false;
                     fullW = false;

                }
                if($(this).val() === 'two_columns' ){

                    twoCol = true;



                }
                if($(this).val() === 'full_width' ){

                    fullW = true;



                }


            })

            var i = 1;



                $('#add-row').on('click', function () {

                    $('#mbSelect').val($("#mbSelect option:first").val());

                   /* var row = $('.empty-row.screen-reader-text').clone(true);
                    row.removeClass('empty-row screen-reader-text');
                    row.addClass('block-' + i);
                    row.insertBefore('#repeatable-fieldset-one .row-wrapper:last');*/
                    var block = $('.block-' + i);



                    if( heroSlider === true ) {
                        heroSlider  = false;
                        var row = $('.mb_text_wrapper').clone(true);
                        row.removeClass('mb_text_wrapper');
                        row.attr('style', '');
                        $('.new-blocks').append(row);
                        $('.new-blocks').append('<input type="hidden" name="hero_slider[]" value="hero-slider" />');

                    }
                    if( twoCol === true ) {
                       /* var row = $('.mb_text_wrapper').clone(true);
                        row.removeClass('mb_text_wrapper');
                        row.attr('style', '');
                        $('.new-blocks').append(row);
                        var row2col = $('.mb_textarea_wrapper').clone(true);
                        row2col.removeClass('mb_textarea_wrapper');
                        row2col.attr('style', '');*/
                       var col_el = '<input type="text" placeholder="Title" name="mb_2col_text[]"/>' +
                                     '<textarea placeholder="Description" cols="55" rows="5" name="mb_2col_textarea[]"></textarea>'
                        $('.new-blocks').append( col_el );
                        //$('.new-blocks').append( row2col );
                        $('.new-blocks').append('<input type="hidden" name="hero_slider[]" value="hero-slider" />');

                        // ajax(i);
                        twoCol = false;
                    }
                    if( fullW === true ) {

                        ajax(i);
                        fullW = false;



                    }



                    i++;

                    return false;
                });


            $('.remove-row').on('click', function () {
                $(this).parents('.row-wrapper').remove();
                return false;
            });

            function ajax (i) {
                var target = '<?php echo admin_url('admin-ajax.php'); ?>' // Passed from wp_localize_script
                var editor_id = "editorid" + i; // Generate this dynamically
                var textarea_name = "mb_editor[]" // Generate this as you need
                var data = {
                    'action': 'ID_get_text_editor',
                    'text_editor_id': editor_id,
                    'textarea_name': textarea_name
                }

                $.ajax({

                    url: target,
                    type: 'post',
                    data: data,
                    error: function (response) {
                        console.log(response);
                        $('.mb_gif').hide();
                    },
                    beforeSend: function (xhr) {
                        $('.mb_gif').show();
                    },
                    success: function (response) {
                        $('.mb_gif').hide();
                        $('.new-blocks').append(response); // Use your logic to get the container where you want to append the editor
                        tinymce.execCommand('mceAddEditor', false, editor_id);
                        quicktags({id: editor_id});

                    }

                });
            }

        });
    </script>
    <div id="repeatable-fieldset-one" width="100%">
        <?php
        if ($gpminvoice_group) : $i = 0;

            foreach ($gpminvoice_group as $field) {


                if( $gpminvoice_group['two_col']){
                    echo 'te';
                }
                ?>
                <div class="row-wrapper">
                    <?php  if( $gpminvoice_group['hero']){?>
                    <div>

                        <input type="text" placeholder="Title" name="mb_text[]"
                               value="<?php if ($gpminvoice_group['hero'] != '') echo esc_attr($field[$i]['mb_text']); ?>"/>
                    </div>
                    <?php } ?>
                    <?php  if( $gpminvoice_group['two_col']){ ?>
                        <div>

                            <input type="text" placeholder="Title" name="mb_2col_text[]"
                                   value="<?php if ($field[$i]['mb_2col_text'] != '') echo esc_attr($field[$i]['mb_2col_text']); ?>"/>
                        </div>
                        <div>


                        <textarea placeholder="Description" cols="55" rows="5"
                                  name="mb_textarea[]"> <?php if ($field[$i]['mb_2col_textarea'] != '') echo esc_attr($field[$i]['mb_2col_textarea']); ?> </textarea></td>
                        </div>
                    <?php } ?>
                    <div>
                        <?php
                        $text = '';
                        /*if ( $field['mb_editor'] != '' ) {
                            $text = htmlspecialchars_decode($field['mb_editor']);

                        }*/
                        //wp_editor($text, 'mettaabox_'.$i, $settings = array('textarea_name' => 'mb_editor[]')); ?>
                    </div>

                    <a class="button remove-row" href="#1">Remove</a></div>
                <?php
                $i++;
            }
        else :
            // show a blank one
            ?>
            <div class="blank_row">

              <!--  <div><input type="text" placeholder="Title" title="Title" name="mb_text[]"/></div>
                <div id="elemId">

                    <?php
/*                    wp_editor('','custom_blank', $settings = array('textarea_name' => 'mb_editor[]'));

                    */?>
                </div>
                <div ><textarea placeholder="Description" name="mb_textarea[]" cols="55" rows="5">  </textarea>
                </div>
                <a class="button  cmb-remove-row-button button-disabled" href="#">Remove</a>-->
            </div>
        <?php endif; ?>


        <!-- empty hidden one for jQuery -->
        <div class="new-blocks">

        </div>


        <div class="empty-row screen-reader-text row-wrapper">

            <div class="mb_text_wrapper" style="display: none;">
                <input type="text" placeholder="Title" name="mb_text[]"/>
            </div>

            <div id="wpmudev_wp_editor_hidden" style="display: none;">
                <?php
                wp_editor( '', 'wpmudev_editor' );
                ?>
            </div>
            <div class="mb_editor_wrapper"></div>

            <div class="mb_textarea_wrapper" style="display: none;">
                <textarea placeholder="Description" cols="55" rows="5" name="mb_textarea[]"></textarea>
            </div>

            <a class="button remove-row" href="#">Remove</a>
        </div>
    </div>

    <div>
        <a id="add-row" class="button disabled" href="#" >Add another</a>
        <div >
            <select id="mbSelect">
                <option value="">Select Block</option>
                <option value="hero_slider">Hero Slider</option>
                <option value="two_columns">Two Columns</option>
                <option value="full_width">Full Width</option>
            </select>

           <img class="mb_gif" style="display: none;" src="http://localhost/checkfire/wp-admin/images/spinner-2x.gif" alt="">
        </div>

    </div>


    <?php
}

/*
 * save the data
 */
add_action('save_post', 'custom_repeatable_meta_box_save');
function custom_repeatable_meta_box_save($post_id)
{
    if (!isset($_POST['gpm_repeatable_meta_box_nonce']) ||
        !wp_verify_nonce($_POST['gpm_repeatable_meta_box_nonce'], 'gpm_repeatable_meta_box_nonce')
    )
        return;

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
        return;

    if (!current_user_can('edit_post', $post_id))
        return;

    $old = get_post_meta($post_id, 'customdata_group', true);
    $new = array();
    $text = $_POST['mb_text'];
    $text_2col = $_POST['mb_2col_text'];
    $textarea = $_POST['mb_2col_textarea'];
    $count = count($text);
    $editor = $_POST['mb_editor'];
    //hidden field
    $hero_slider = $_POST['hero_slider'];
    $c_hero = count($hero_slider);
    $two_col = $_POST['two_col'];
    $c_two_col = count($_POST['two_col']);


        for ($i = 0; $i < $c_hero; $i++) {
            if ($text[$i] != '') :
                $new[$i]['hero']['mb_text'] = stripslashes(strip_tags($text[$i]));
                $new[$i]['two_col']['mb_2col_text'] = stripslashes(strip_tags($text_2col[$i]));
                $new[$i]['two_col']['mb_2col_textarea'] = stripslashes($textarea[$i]); // and however you want to sanitize
                /*  $new[$i]['mb_textarea'] = stripslashes($textarea[$i]); // and however you want to sanitize
                  $new[$i]['mb_editor'] = htmlspecialchars($editor[$i]);*/
            endif;
        }



    if (!empty($new) && $new != $old)
        update_post_meta($post_id, 'customdata_group', $new);
    elseif (empty($new) && $old)
        delete_post_meta($post_id, 'customdata_group', $old);


}

/*
 * create the wp editor -ajax
 */
function ID_get_text_editor() {
    $content = ""; // Empty because is a new editor
    $editor_id = $_POST["text_editor_id"]; // Random ID from AJAX JS call
    $textarea_name = $_POST["textarea_name"]; // Name from JS file
    $settings = array(
        'textarea_name' => $textarea_name
    );
    wp_editor($content, $editor_id, $settings);
    wp_die(); // Mandatory wp_die
}
add_action('wp_ajax_ID_get_text_editor', 'ID_get_text_editor');

