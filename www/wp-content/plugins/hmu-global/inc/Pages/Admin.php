<?php

namespace Inc\Pages;

class Admin
{


    public function __construct()
    {

            add_action('admin_menu', array($this, 'add_menu_pages'));


    }

    function add_menu_pages() {
        add_menu_page(
            'Hmu Global',
            'Hmu Global',
            'manage_options',
            'hmu_g_plugin',
            array($this, 'add_menu_pages_callback'),
            ''
        );
    }
    function add_menu_pages_callback() {
        require_once PLUGIN_PATH.'inc/Template/dashboard.php';
    }

}