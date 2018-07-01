<?php
/*
Plugin Name: Hook Me Up
Plugin URI:  http://ukcoding.com
Description: Track submit of reviews
Version:     1.0.0
Author:      Noureddine Latreche
Text Domain: Hook Me Up
Domain Path: /languages
License:     GPL3

*/


if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
define ('SITE_ROOT', realpath(dirname(__FILE__)));
/**
 * first we call the files we are using
 */
if ( file_exists( dirname( __FILE__ ) . '/vendor/autoload.php' ) ) {
	require_once dirname( __FILE__ ) . '/vendor/autoload.php';
}



require_once dirname(__FILE__) . '/testUpload.php';

use Inc\Base\Activate;
use Inc\Base\Deactivate;
function hook_me_up_activate () {
	Activate::activate();
}
function hook_me_up_deactivate () {
	Deactivate::deactivate();
}
register_activation_hook( __FILE__, 'hook_me_up_activate' );
register_deactivation_hook( __FILE__, 'hook_me_up_deactivate' );


if (class_exists ('Inc\\Init')) {
	Inc\Init::register_services();
}

add_filter('wp_mail_from', 'new_mail_from');
add_filter('wp_mail_from_name', 'new_mail_from_name');

function new_mail_from($old) {
	return 'info@checkfire.com';
}
function new_mail_from_name($old) {
	return 'Checkfire';
}







add_action( 'show_user_profile', 'hmu_user_custom_field' );
add_action( 'edit_user_profile', 'hmu_user_custom_field' );

function hmu_user_custom_field() {
   $custom_id = get_user_meta(
                     31,
                    '_user_custom_id'

            );

    ?>
    <table class="form-table">
        <tbody>
        <tr>
            <th><label>custom id</label></th>
            <td> <input type="text" name="hmu_custom_id" placeholder="user custom id" value="<?php echo @$custom_id[0]; ?>"><br>
                </td>
        </tr>
        </tbody>
    </table>



<?php
}
function hmu_update_user_meta() {
    $values = array(
        // String value. Empty in this case.
        '_user_custom_id'             => filter_input( INPUT_POST, 'hmu_custom_id', FILTER_SANITIZE_STRING ),

    );

    foreach ( $values as $key => $value ) {
        update_user_meta( '31', $key, $value );
    }
}

add_action( 'personal_options_update', 'hmu_update_user_meta' );
add_action( 'edit_user_profile_update', 'hmu_update_user_meta' );

