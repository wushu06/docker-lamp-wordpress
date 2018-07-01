<?php

namespace Inc\Data;

use \Inc\Base\BaseController;

class UploadFile extends BaseController{

    public $html;

    public $getFile ='' ;

    public function register() {

        add_action( 'wp_ajax_hook_me_up_upload_csv_file', array($this, 'hook_me_up_upload_csv_file') );
        add_action('wp_ajax_nopriv_hook_me_up_upload_csv_file', array($this,'hook_me_up_upload_csv_file') );

    }

    function hook_me_up_upload_csv_file () {

        if ( ! check_ajax_referer( 'hmu-security', 'security' ) ) {
            return wp_send_json_error( 'Invalid Nonce' );
        }



        // $_FILES['file']['tmp_name'];
        $FILE_POST =  $_FILES['file'] ;
        $filename = $FILE_POST["name"];
        // $filetype = $FILE_POST["type"];
        $filesize = $FILE_POST["size"];
        $newFilename =  time().'_'.$FILE_POST["name"];
         $location = $this->plugin_path.'Upload/'. $newFilename;
         $this->getFile = $location;
        $filetype = wp_check_filetype( basename( $location ), array('csv' => 'text/csv') );

       // $mimes = array('application/vnd.ms-excel','text/plain','text/csv','text/tsv');


       if($filetype['ext'] != 'csv'):?>
            <div class="notice notice-error is-dismissible">
                <p>File must be a CSV.</p>
            </div>
            <?php

            exit();

        endif;
        if($filesize == 0):?>
            <div class="notice notice-error is-dismissible">
                <p>File is empty.</p>
            </div>

          <?php
           exit();

        endif;

        if(! move_uploaded_file($FILE_POST["tmp_name"], $location)) {?>
            <div class="notice notice-error is-dismissible">
                <p>File couldn't be moved!</p>
            </div>
            <?php
            exit();
        }
        if(!file_exists($location)){?>
            <div class="notice notice-error is-dismissible">
                <p>File doesn't exist!</p>
            </div>
            <?php
            exit();
        }

        if(!is_writable($location)){?>
            <div class="notice notice-error is-dismissible">
                <p>File not writable!</p>
            </div>
            <?php
            exit();

        }


        if( ($handle = fopen( $location, 'r' )) !== false )
        {
            $row = 1;
            $output = '<table class="widefat fixed" cellspacing="0">';
            while( ($data = fgetcsv( $handle )) !== false )
            {
                $output .= '<tr >';
                foreach( $data as $value )
                {
                    $output .= sprintf( '<td>%s</td>', $value );

                }
                if($row !==1) {
                    $output .= sprintf('<td>%s</td>', ' <i class="fa fa-plus"></i>');
                    $output .= sprintf('<td>%s</td>', ' <i class="fa fa-close"></i>');
                    $output .= '</tr>';
                }
                if($row ==1) {
                    $output .= sprintf('<td>%s</td>', ' Add');
                    $output .= sprintf('<td>%s</td>', ' Delete');
                    $output .= '</tr>';
                }
                $row ++;

            }
            fclose( $handle );
            $output .= '</table>';


        }
        $output;
        wp_send_json_success($output);





    }

}