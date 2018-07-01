<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @since      0.0.1
 */

namespace BullseyeLocations;

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      0.0.1
 */
class Main {

  /**
   * The loader that's responsible for maintaining and registering all hooks that power
   * the plugin.
   *
   * @since    0.0.1
   * @access   protected
   * @var      Loader    $loader    Maintains and registers all hooks for the plugin.
   */
  protected $loader;


  /**
   * The unique identifier of this plugin.
   *
   * @since    0.0.1
   * @access   protected
   * @var      string    $plugin_name    The string used to uniquely identify this plugin.
   */
  protected $plugin_name;


  /**
   * The current version of the plugin.
   *
   * @since    0.0.1
   * @access   protected
   * @var      string    $version    The current version of the plugin.
   */
  protected $version;


  /**
   * Define the core functionality of the plugin.
   *
   * Set the plugin name and the plugin version that can be used throughout the plugin.
   * Load the dependencies, define the locale, and set the hooks for the admin area and
   * the public-facing side of the site.
   *
   * @since    0.0.1
   */
  public function __construct() {

    $this->plugin_name = 'bullseye-locations';
    $this->version = '0.0.1';
    $this->loader = new utils\Loader();

    $this->set_locale();
    $this->define_admin_hooks();
    $this->define_public_hooks();

  }


  /**
   * Define the locale for this plugin for internationalization.
   *
   * Uses the Internationalization class in order to set the domain and to register the hook
   * with WordPress.
   *
   * @since    0.0.1
   * @access   private
   */
  private function set_locale() {

    $plugin_i18n = new utils\Internationalization();
    $plugin_i18n->set_domain( $this->get_plugin_name() );

    $this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

  }


  /**
   * Register all of the hooks related to the admin area functionality
   * of the plugin.
   *
   * @since    0.0.1
   * @access   private
   */
  private function define_admin_hooks() {

    $plugin_admin = new admin\Controller( $this->get_plugin_name(), $this->get_version() );

    $this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
    $this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
    $this->loader->add_action( 'admin_menu', $plugin_admin, 'menu' );

    // Ajax actions
    $this->loader->add_action('wp_ajax_save_account', $plugin_admin, 'ajaxSaveAccount');
    $this->loader->add_action('wp_ajax_user_logout', $plugin_admin, 'ajaxUserLogout');
    $this->loader->add_action('wp_ajax_interface_get_model', $plugin_admin, 'ajaxGetInterfaces');
    $this->loader->add_action('wp_ajax_delete_api_interface', $plugin_admin, 'ajaxDeleteInterface');
    $this->loader->add_action('wp_ajax_add_api_interface', $plugin_admin, 'ajaxAddInterface');
    $this->loader->add_action('wp_ajax_add_api_page', $plugin_admin, 'ajaxAddPage');
  }


  /**
   * Register all of the hooks related to the public-facing functionality
   * of the plugin.
   *
   * @since    0.0.1
   * @access   private
   */
  private function define_public_hooks() {

    $plugin_public = new front\Controller($this->get_plugin_name(), $this->get_version());
    // AJAX actions
    $this->loader->add_action('wp_ajax_nopriv_update_bullseye_interface', $plugin_public, 'ajaxUpdateInterface');
    $this->loader->add_action('wp_ajax_nopriv_delete_bullseye_interface', $plugin_public, 'ajaxDeleteInterface');


    // Shortcodes
    add_shortcode('BE', array($plugin_public, 'beShortcode'));
  }


  /**
   * Run the loader to execute all of the hooks with WordPress.
   *
   * @since    0.0.1
   */
  public function run() {
    $this->loader->run();
  }


  /**
   * The name of the plugin used to uniquely identify it within the context of
   * WordPress and to define internationalization functionality.
   *
   * @since     0.0.1
   * @return    string    The name of the plugin.
   */
  public function get_plugin_name() {
    return $this->plugin_name;
  }


  /**
   * The reference to the class that orchestrates the hooks with the plugin.
   *
   * @since     0.0.1
   * @return    Loader    Orchestrates the hooks of the plugin.
   */
  public function get_loader() {
    return $this->loader;
  }


  /**
   * Retrieve the version number of the plugin.
   *
   * @since     0.0.1
   * @return    string    The version number of the plugin.
   */
  public function get_version() {
    return $this->version;
  }

}
