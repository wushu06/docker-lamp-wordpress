<?php
/*
Plugin Name: Hook Me Up - global
Plugin URI:  http://ukcoding.com
Description: Track submit of reviews
Version:     1.0.0
Author:      Noureddine Latreche
Text Domain: Hook Me Up Global
Domain Path: /languages
License:     GPL3

*/


if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
define ('HMU_ROOT', realpath(dirname(__FILE__)));
define ('PLUGIN_PATH', plugin_dir_path(dirname(__FILE__)).'/hmu-global/');

require_once dirname(__FILE__) . '/inc/Base/Activate.php';
require_once dirname(__FILE__) . '/inc/Base/Deactivate.php';
require_once dirname(__FILE__) . '/inc/Pages/Admin.php';
require_once dirname(__FILE__) . '/inc/Shortcode/Loop.php';

use Inc\Base\Activate;
use Inc\Base\Deactivate;
use Inc\Pages\Admin;
use Inc\Shortcode\Loop;

function hmu_g_activate () {
	Activate::activate();
}
function hmu_g_deactivate () {
	Deactivate::deactivate();
}
register_activation_hook( __FILE__, 'hmu_g_activate' );
register_deactivation_hook( __FILE__, 'hmu_g_deactivate' );

$admin = new Admin();
$loop = new Loop();











