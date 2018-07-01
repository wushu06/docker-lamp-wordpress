<?php

/**
 * Plugin Name:       Bullseye Store Locator
 * Plugin URI:        https://wordpress.org/plugins/bullseye-store-locator/
 * Description:       Bullseye is the original cloud-based store locator solution. Install and customize in minutes—no plugin updates required or hidden add-on costs.
 * Version:           1.0.1
 * Author:            Bullseye Locations
 * Author URI:        http://www.bullseyelocations.com/
 * License:           GPL-2.0
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 */

namespace BullseyeLocations;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
  die;
}

// We load Composer's autoload file
require_once plugin_dir_path( __FILE__ ) . 'vendor/autoload.php';

register_activation_hook(
  __FILE__,
  array('\BullseyeLocations\utils\Activator', 'activate')
);

register_deactivation_hook(
  __FILE__,
  array('\BullseyeLocations\utils\Deactivator', 'deactivate')
);

/**
 * Use Dotenv to set required environment variables and load .env file in root
 */
$rootDir = __DIR__;
if (file_exists($rootDir . '/.env')) {
  $dotenv = new \Dotenv\Dotenv($rootDir);
  $dotenv->load();
}

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since 0.0.1
 */
function run_bullseye_locations() {
  $plugin = new Main();
  $plugin->run();
}
run_bullseye_locations();
