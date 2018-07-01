<?php

/**
 * Fired during plugin deactivation
 *
 * @since      0.0.1
 */

namespace BullseyeLocations\utils;

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      0.0.1
 */
class Deactivator {

  /**
   * Short Description.
   *
   * Long Description.
   *
   * @since    0.0.1
   */
  public static function deactivate() {
    $main = new \BullseyeLocations\Main();
    $plugin_admin = new \BullseyeLocations\admin\Controller( $main->get_plugin_name(), $main->get_version() );

    //delete WP integration in Bullseye account
    $plugin_admin->deleteBullseyeIntegration();

    //delete WP option from database
    $accountOption = new \BullseyeLocations\options\Account();
    $accountOption->remove();
  }

}
