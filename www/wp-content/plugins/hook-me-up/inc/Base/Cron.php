<?php

namespace Inc\Base;
use Inc\Data\InsertLocations;
use Inc\Data\Submit;

class Cron
{
    public $option ;
    public $cron;



    public function register()
    {
        $option  = get_option ('hmu_cron');
        if($option )

        foreach ($option  as $key => $value) {

            @$cron_time  =  $value['cron_time'] ;
            $cron_name_row = $value['cron_name'];
            $cron_name_nospace = preg_replace("/[\s_]/", "-",  $cron_name_row);
            $cron_name = 'hmu-'.$cron_name_nospace;
            if(isset($cron_time) && !empty($cron_time)) {

                add_filter('cron_schedules', array($this, 'hmu_cron_recurrence_interval') );

                if (!wp_next_scheduled($cron_name )) {
                    wp_schedule_event(time(), $cron_time, $cron_name );
                }

                add_action($cron_name , array($this,'hmu_upload_file_fun') );

            }else {

                wp_clear_scheduled_hook( $cron_name  );
            }
        }



    }

    function hmu_cron_recurrence_interval($schedules) {

        $schedules['every_one_minute'] = array(
            'interval' => 60,
            'display' => __('Every 1 Minutes', 'textdomain')
        );
        $schedules['every_three_minutes'] = array(
            'interval' => 180,
            'display' => __('Every 3 Minutes', 'textdomain')
        );
        $schedules['every_fifteen_minutes'] = array(
            'interval' => 900,
            'display' => __('Every 15 Minutes', 'textdomain')
        );
        return $schedules;
    }


    function hmu_upload_file_fun()
    {
        @$option  = get_option ('hmu_cron');
       // $cron_file = 'http://localhost/wp_treehouse/wp-content/plugins/hook-me-up/locations.csv';

      /*  $location = new InsertLocations();

        $location->handle_csv($cron_file);*/

        foreach (@$option  as $key => $value) {
           //
            @$cron_file  =   (($value['cron_upload'] !='')? $value['cron_upload'] : $value['cron_url'])  ;
            @$cron_function  =   $value['cron_function'] ;
            if($cron_function == 'insert-location'){
                //$cron_file = 'http://localhost/wp_treehouse/wp-content/uploads/2018/02/locations.csv';
                $submit = new Submit();
                $submit->submit_data( 'submit_locations', $cron_file);

            }
            if($cron_function == 'insert-users'){
                //$cron_file = 'http://localhost/wp_treehouse/wp-content/uploads/2018/02/locations.csv';
                $submit = new Submit();
                $submit->submit_data( 'submit_users', $cron_file);

            }
            if($cron_function == 'insert-products'){
                //$cron_file = 'http://localhost/wp_treehouse/wp-content/uploads/2018/02/locations.csv';
                $submit = new Submit();
                $submit->submit_data( 'submit_products', $cron_file);

            }

        }




/*
       wp_insert_post(array(
            'post_title' => 'test',
            'post_content' => 'content',
            'post_status' => 'publish',
            'post_type' => "wpsl_stores",
        ));*/

    }






} // end class