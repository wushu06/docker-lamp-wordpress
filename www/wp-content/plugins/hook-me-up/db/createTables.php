<?php

class hookeMe_tables {


  function __construct  (){
      global $wpdb;

    // first table for the oAuth 1
    $table_name = $wpdb->prefix . "hookMeUp_oauth_one";
    $date = date('Y-m-d H:i:s');
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
      id mediumint(9) NOT NULL AUTO_INCREMENT,
      username tinytext NOT NULL,
      ck tinytext NOT NULL,
      cs tinytext NOT NULL,
      text text NOT NULL,
      time DATETIME DEFAULT CURRENT_TIMESTAMP,
      url varchar(55) DEFAULT '' NOT NULL,
      PRIMARY KEY  (id)
    ) $charset_collate;";


  require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
  dbDelta( $sql );


    // second table for the basic oauth
    $table_name_basic= $wpdb->prefix . "hookMeUp_basic";
    $date = date('Y-m-d H:i:s');
    $charset_collate_basic = $wpdb->get_charset_collate();

    $sql_basic = "CREATE TABLE $table_name_basic (
      id mediumint(9) NOT NULL AUTO_INCREMENT,
      username_wp tinytext NOT NULL,
      username tinytext NOT NULL,
      password tinytext NOT NULL,
      text text NOT NULL,
      time DATETIME DEFAULT CURRENT_TIMESTAMP,
      url varchar(55) DEFAULT '' NOT NULL,
      PRIMARY KEY  (id)
    ) $charset_collate_basic;";

    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $sql_basic );


    // third table for the upload file
    $table_name_basic= $wpdb->prefix . "hookMeUp_csv";
    $date = date('Y-m-d H:i:s');
    $charset_collate_basic = $wpdb->get_charset_collate();

    $sql_basic = "CREATE TABLE $table_name_basic (
      id mediumint(9) NOT NULL AUTO_INCREMENT,
      username_wp tinytext NOT NULL,
      upload tinytext NOT NULL,
      text text NOT NULL,
      time DATETIME DEFAULT CURRENT_TIMESTAMP,
      PRIMARY KEY  (id)
    ) $charset_collate_basic;";

    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $sql_basic );

      // fourth table for magento
      $table_soap = $wpdb->prefix . "hookMeUp_soap";
      $date = date('Y-m-d H:i:s');
      $charset_collate_basic = $wpdb->get_charset_collate();
  
      $sql_basic = "CREATE TABLE $table_soap (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        username_wp tinytext NOT NULL,
        username_soap tinytext NOT NULL,
        password_soap tinytext NOT NULL,
        url_soap varchar(55) DEFAULT '' NOT NULL,
        time DATETIME DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY  (id)
      ) $charset_collate_basic;";
  
      require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
      dbDelta( $sql_basic );

    }
}

new hookeMe_tables();
