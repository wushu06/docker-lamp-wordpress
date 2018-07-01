<?php

namespace Inc\Data;

use Inc\Data\Read;
use Inc\Data\InsertPriceByUser;
use Inc\Data\InsertPriceByRole;
use Inc\Data\InsertUser;
use Inc\Data\InsertProducts;
use Inc\Data\InsertImage;

class Submit
{



    public function submit_data( $POST, $file)
    {
        $output ='';
        '';
        $read = new Read();
        $insert_by_user = new InsertPriceByUser();
        $insert_by_role = new InsertPriceByRole();
        $insert_users = new InsertUser();
        $insert_products = new InsertProducts();
	    $insert_locations = new InsertLocations();
	    $insert_images = new InsertImage();


        //$file = $read->upload_csv_file($FILE_POST);

        if( $POST == 'submit_prices_users'):
            $output   = $insert_by_user->insert_update_by_user($file);
           // $output .= $read->read_prices_file($file);
         elseif( $POST == 'submit_prices_role') :
             $output   = $insert_by_role->insert_update_by_role($file);
           //  $output  .= $read->read_prices_file($file);
         elseif( $POST == 'submit_users') :
             $output =  $insert_users->handle_csv($file);
              // $read->read_users_file($file);
        elseif( $POST == 'submit_products') :
             $output = $insert_products->handle_csv($file);
           // $output  = $read->read_products_file($file);
        elseif( $POST == 'submit_locations') :
	         $insert_locations->handle_csv($file);
        elseif( $POST == 'submit_images') :
	        // $insert_images->handle_csv($file);
	        $output = $insert_images->insert_image_folder();


	       // $output  = $read->read_locations_file($file);
         endif;


         $this->send_email ($file);

        return $output;
        // return sprintf("<h3> %s </h3> <hr><h3>Result:</h3> %s.",$output_array['msg'],$output);


    }

    public function send_email ($file)
    {
        $insert_users = new InsertUser();
        $insert_products = new InsertProducts();
        $option = get_option ('hmu_dashboard');
        @$send_email = $option["activate_email"];


        if($send_email == true) {
            if ($insert_users->dataCheck  == true ){
                $to = get_bloginfo('admin_email');
                $subject = get_bloginfo('name').' Users Update';
                $message = 'new users have been added / updated';
                $headers[] = 'From: '.get_bloginfo('name').' <'.$to.'>'; // 'From: Alex <me@alecaddd.com>'
                // $headers[] = 'Content-Type: text/html: charset=UTF-8';
                $attachments = $file;
                wp_mail($to, $subject, $message, $headers, $attachments);

                echo '<h3>Email has been sent</h3>';


            }elseif ($insert_products->data_check  == true) {
                $to = get_bloginfo('admin_email');
                $subject = get_bloginfo('name').' Products Update';
                $message = 'new products have been added / updated';
                $headers[] = 'From: '.get_bloginfo('name').' <'.$to.'>'; // 'From: Alex <me@alecaddd.com>'
                // $headers[] = 'Content-Type: text/html: charset=UTF-8';
                $attachments = $file;
                wp_mail($to, $subject, $message, $headers, $attachments);

                echo '<h3>Email has been sent</h3>';
            }
        }else {
            echo '<h3>Email deactivated</h3>';
        }


    }


}