<?php

namespace Inc\Base;

class BaseController
{
    public $plugin_path;

    public $plugin_url;

    public $plugin;

    public $subpagesOutput = array();

    public $dahboardFields = array();
    public $fieldsOutput = array();


    public function __construct()
    {
        /*$this->plugin_path = plugin_dir_path(dirname(__FILE__, 2));
        $this->plugin_url = plugin_dir_url(dirname(__FILE__, 2));*/
        $this->plugin_path = SITE_ROOT.'/';
        $this->plugin_url = plugins_url().'/hook-me-up/';

        $this->subpagesOutput = array(
           /* 'import_users' =>
                array('Import Users', 'hmu_users_page'),*/
            'import_prices' =>
                array('Import Prices & Users', 'hmu_prices_page'),
            'cron_task' =>
                array('Cron Task', 'hmu_cron_page'),
        );

        /*
        * FIELDS
        */
        $op = array('hmu_plugin' => 'activate_cron');

        $this->dahboardFields = array(
            // ID
            //0- title 1- callback 2-page 3- section 4- option name 5-input type

            'activate_cron' =>
                array('Activate Cron ',
                    'cronActivationField',
                    'hmu_plugin',
                    'hmu_dashboard_index',
                    'hmu_dashboard',
                    'boolean'
                ),
            'activate_email' =>
                array(
                    'Activate Email ',
                    'emailActivationField',
                    'hmu_plugin',
                    'hmu_dashboard_index',
                    'hmu_dashboard',
                    'boolean'
                ),

            'cron_name' =>
                array(
                    '',
                    'cronNameField',
                    'cron_task',
                    'hmu_cron_index',
                    'hmu_cron',
                    'string'
                ),

            'cron_time' =>
                array(
                    '',
                    'cronTimeField',
                    'cron_task',
                    'hmu_cron_index',
                    'hmu_cron',
                    'string'
                ),
            'cron_function' =>
                array(
                    '',
                    'cronFunction',
                    'cron_task',
                    'hmu_cron_index',
                    'hmu_cron',
                    'string'
                ),
        /*    'cron_file' =>
                array('',
                    'cronFile',
                    'cron_task',
                    'hmu_cron_index',
                    'hmu_cron',
                    'string'
                ),*/

            'cron_upload' =>
                array('',
                    'cronUpload',
                    'cron_task',
                    'hmu_cron_index',
                    'hmu_cron',
                    'string'
                ),
            'cron_url' =>
                array('',
                    'cronURL',
                    'cron_task',
                    'hmu_cron_index',
                    'hmu_cron',
                    'string'
                )


        );


    }

    /*public function activated(   $option_name,  $key )
    {
        $option = get_option( $option_name );

        return  $option[ $key ] == 1  ? true : false;
    }

    public function activated_string (   $option_name,  $key )
    {
        $option = get_option( $option_name );

        return  $option[ $key ] !== ''  ? $option[ $key ] : '';
    }*/


}