<?php 

namespace Inc\Data;

use \Inc\Base\BaseController;

class Upload {

    public $html;

    public function register() {

        add_action( 'wp_ajax_hook_me_up_upload_csv_file', array($this, 'hook_me_up_upload_csv_file') );
       // add_action('wp_ajax_nopriv_hook_me_up_upload_csv_file', array($this,'hook_me_up_upload_csv_file') );
        
    }

    function hook_me_up_upload_csv_file () {


        // $_FILES['file']['tmp_name'];
        $FILE_POST =  $_FILES['file'] ;
        // Check if file was uploaded without errors
        // $allowed = array("jpg" => "image/jpg");
        $filename = $FILE_POST["name"];
        $filetype = $FILE_POST["type"];
        $filesize = $FILE_POST["size"];
        $newFilename =  $FILE_POST["name"];
       echo $location = 'C:\xampp\htdocs\wp_bootstrap\wp-content\plugins\hook-me-up/upload/'. $newFilename;
        //$filetype = wp_check_filetype( basename( $FILE_POST["tmp_name"] ), array('csv' => 'text/csv') );
        $mimes = array('application/vnd.ms-excel','text/plain','text/csv','text/tsv');

                if(!is_writable($location))
                {
                    echo 'Can not write to file ';

                }
                else {
                echo 'can wrte';
            }

                echo var_dump(is_writable($location));



        move_uploaded_file($FILE_POST["tmp_name"], $location);

        if( ($handle = fopen( $location, 'w' )) !== false )
        {
            $output = '<table class="widefat fixed" cellspacing="0">';
            while( ($data = fgetcsv( $handle )) !== false )
            {
                $output .= '<tr >';
                foreach( $data as $value )
                {
                    $output .= sprintf( '<td>%s</td>', $value );

                }
                $output .= sprintf( '<td>%s</td>', '+' );
                $output .= sprintf( '<td>%s</td>', 'x' );
                $output .= '</tr>';

            }
            fclose( $handle );
            $output .= '</table>';
        }
        echo $output;





    }
}