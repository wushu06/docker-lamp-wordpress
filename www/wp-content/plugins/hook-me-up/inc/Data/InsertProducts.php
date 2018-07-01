<?php


namespace Inc\Data;


class InsertProducts
{
    public $data_check = false;

    function handle_csv($file)
    {
    	global $wpdb;

        //$csv_file                        = 'http://localhost/wp_treehouse/wp-content/plugins/hook-me-up-csv/users.csv';
        // $csv_file = $this->plugin_url.'users.csv';
        $csv_file = $file;

        //for checking headers
        $requiredHeaders = array('Internal ID','Product Code','Suggested Name','Agent','Website Category','Sub-Category','Sub-brand','Description','Short Description','Fire Rating','Certification','Toolkit');


        $fptr = fopen($csv_file, 'r');
        $firstLine = fgets($fptr); //get first line of csv file
        fclose($fptr);
        $foundHeaders = str_getcsv(trim($firstLine), ',', '"'); //parse to array


//check the headers of file

	    if ($foundHeaders !== $requiredHeaders) {
             echo 'File Header not the same';
             die();
         }

        $getfile = fopen($csv_file, 'r');
//$users     = array();
        if (false !== ($getfile = fopen($csv_file, 'r'))) {

            $data = fgetcsv($getfile, 1000, ',');
            //display table headers
            //var_dump($data  );

            $update_cnt = 0;
            $insert_cnt = 0;
            $count = 0;
            while (false !== ($data = fgetcsv($getfile, 1000, ','))) {
                if ($data[0] != NULL) {  // ignore blank lines
                    $count++;
                    $result = $data; // two sperate arrays
                    // $str = implode(',', $result); // join the two sperate arrays
                    //$slice = explode(',', $str); // remove ,
                    $slice = $result;

                    //variables
                    $netsuite_id = $slice[0];
                    $product_code = $slice[1];
                    $post_title = $slice[2];
                    $agent = $slice[3];
                    $cat = $slice[4];
                    $sub_cat = $slice[5];
                    $sub_brand = $slice[6];
                    $description = $slice[7];
                    $shortdesc = $slice[8];
                    $rating = $slice[9];
	                $cert = $slice[10];
	                $tech = '• '.$rating .'<br>'. $cert;
                    //$filename = $slice[12];


                    $reault_array [] = $this->insert_update_products($netsuite_id, $post_title,$agent, $cat,$sub_cat, $sub_brand, $description,$shortdesc,$tech);




                }//if empty lines


            }//end of while




        }

        return $reault_array;


    }
	public function seoUrl($string) {
		//Lower case everything
		$string = strtolower($string);
		//Make alphanumeric (removes all other characters)
		$string = preg_replace("/[^a-z0-9_\s-]/", "", $string);
		//Clean up multiple dashes or whitespaces
		$string = preg_replace("/[\s-]+/", " ", $string);
		//Convert whitespaces and underscore to dash
		$string = preg_replace("/[\s_]/", "-", $string);
		return $string;
	}

    function insert_update_products($netsuite_id, $post_title,$agent, $cat,$sub_cat, $sub_brand, $description,$shortdesc,$tech)
    {
        //wp_suspend_cache_addition(true);


         global $wpdb;


          $seo = $this->seoUrl($post_title);


          $count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $wpdb->posts WHERE custom_id = %d AND post_type = 'product'", $netsuite_id ));

          if($count == 1){

                  $wpdb->update(
                      $wpdb->posts,
                          array(
                              'post_title' => $post_title,
                              'post_content' => $description,
                              'post_name' => $seo,
                              'post_status' => 'publish',
                              'post_type'=>'product',
                              'post_excerpt'=>$shortdesc,
                              'custom_id' => $netsuite_id,
                          ),
                          array(
                              'custom_id' => $netsuite_id
                          ),

                          array(
                              '%s',
                              '%s',
                              '%s',
                              '%s',
	                          '%s',
                              '%s',
                              '%d'
                          )
                  );

                  $count = $wpdb->get_results($wpdb->prepare("SELECT * FROM $wpdb->posts WHERE custom_id = %d AND post_type = 'product'", $netsuite_id ));
                  $stdInstance =json_decode(json_encode($count),true);
                  foreach ($stdInstance as $c ){
                      $product_wp_id =  $c['ID'];
                  }

            /*  if($filename !=''){
                  $this->hmu_move_siteload_image($product_wp_id, $filename);

              }*/


                  $msg = $post_title. ' Product has been updated <br/ >';

              }else {
                  $wpdb->insert(
                      $wpdb->posts,
                      array(
                          'post_title' => $post_title,
                          'post_content' => $description,
                          'post_name' => $seo,
                          'post_status' => 'publish',
                          'post_type'=>'product',
	                      'post_excerpt'=>$shortdesc,
                          'custom_id' => $netsuite_id,
                      ),
                      array(
                          '%s',
                          '%s',
                          '%s',
                          '%s',
                          '%s',
	                      '%s',
                          '%d'
                      )
                  );

              $product_wp_id = $wpdb->insert_id;
             // if($filename !=''){
                 // $this->hmu_move_siteload_image($product_wp_id, $filename);

             // }



                  $msg = $post_title. '  Product has been insert <br/ >';

              }





         // wp_set_object_terms( $product_id, 'simple', 'product_type' );
          $cat_slug = $this->seoUrl($cat);
          //wp_set_post_terms( $product_id, $cat_slug, 'product_cat', true );
         // wp_set_post_terms($product_id, $cat_slug, 'product_cat');
	/*    $searchString = ',';

	    if( strpos($cat, $searchString) !== false ) {
		    $arr = explode(",", $cat, 2);
		    $first = $arr[0];
		    $last = $arr[1];
		    wp_set_object_terms( $product_wp_id , $first, 'product_cat', true);
		    wp_set_object_terms( $product_wp_id ,$last, 'product_cat', true);

	    }else {
		    wp_set_object_terms( $product_wp_id , $cat, 'product_cat', true);
	    }*/
           wp_set_object_terms( $product_wp_id , $cat, 'product_cat', true);

	       wp_set_object_terms( $product_wp_id, $sub_brand, 'brands', true);


	    if($sub_cat) {
		    // insert sub categories
		    $sub_cat_exist =  term_exists( $sub_cat, 'product_cat' );

		    if( $sub_cat_exist ) {
			    wp_set_post_terms( $product_wp_id,$sub_cat_exist['term_id'],  'product_cat' );

		    }else {
			    $parent_term = term_exists( $cat, 'product_cat' ); // array is returned if taxonomy is given
			    $parent_term_id = $parent_term['term_id'];         // get numeric term id

			    $child_term = wp_insert_term(
				    $sub_cat,   // the term
				    'product_cat', // the taxonomy
				    array(
					    'parent'      => $parent_term_id,
				    )
			    );

			    $sub_id = $child_term['term_id'];
			    wp_set_post_terms( $product_wp_id,$sub_id,  'product_cat' );
		    }

	    }






	    // insert child cat (agent)
        if($agent) {
            $child_exist =  term_exists( $agent, 'product_cat' );

            if($child_exist) {
                wp_set_post_terms( $product_wp_id,$child_exist['term_id'],  'product_cat' );

            }else {
                $parent_term = term_exists( 'extinguishers', 'product_cat' ); // array is returned if taxonomy is given
                $parent_term_id = $parent_term['term_id'];         // get numeric term id

                $child_term = wp_insert_term(
                    $agent,   // the term
                    'product_cat', // the taxonomy
                    array(
                        'parent'      => $parent_term_id,
                    )
                );

                $agent_id = $child_term['term_id'];
                wp_set_post_terms( $product_wp_id,$agent_id,  'product_cat' );
            }

        }





	      update_post_meta( $product_wp_id , '_visibility', 'visible' );
          update_post_meta( $product_wp_id , '_stock_status', 'instock');
          update_post_meta( $product_wp_id , 'total_sales', '0' );
          update_post_meta( $product_wp_id , '_downloadable', 'no' );
          update_post_meta( $product_wp_id , '_virtual', 'yes' );
          update_post_meta( $product_wp_id , '_price',  '0' );
          update_post_meta( $product_wp_id , '_regular_price',  '0' );
          update_post_meta( $product_wp_id , '_sale_price', '' );
          update_post_meta( $product_wp_id , '_purchase_note', '' );
          update_post_meta( $product_wp_id , '_featured', 'no' );
          update_post_meta( $product_wp_id , '_weight', '' );
          update_post_meta( $product_wp_id , '_length', '' );
          update_post_meta( $product_wp_id , '_width', '' );
          update_post_meta( $product_wp_id , '_height', '' );
          update_post_meta( $product_wp_id , '_sku', '' );
          update_post_meta( $product_wp_id , '_product_attributes', array() );
          update_post_meta( $product_wp_id , '_sale_price_dates_from', '' );
          update_post_meta( $product_wp_id , '_sale_price_dates_to', '' );
          update_post_meta( $product_wp_id , '_sold_individually', '' );
          update_post_meta( $product_wp_id , '_manage_stock', 'no' );
          update_post_meta( $product_wp_id , '_backorders', 'no' );
          update_post_meta( $product_wp_id , '_stock', '' );
          update_post_meta( $product_wp_id , 'tech_spec', $tech );





          $wpdb->show_errors();
          return $msg;



    }


    public function hmu_move_siteload_image($product_wp_id, $filename) {

        require_once(ABSPATH . "wp-admin" . '/includes/image.php');
        require_once(ABSPATH . "wp-admin" . '/includes/file.php');
        require_once(ABSPATH . "wp-admin" . '/includes/media.php');
        $post_id = $product_wp_id;
        $desc = "The WordPress Logo";
        $bname = basename($filename);

        $uploaddir = wp_upload_dir();
        $uploadfile = $uploaddir['path'] . '/'. $bname ;

        if(file_exists($uploadfile)){
            $id = attachment_url_to_postid($uploadfile);
            echo 'file exist';
            return $id;
        } else {
            $contents= file_get_contents($filename);
            $savefile = fopen($uploadfile, 'w');
            fwrite($savefile, $contents);
            fclose($savefile);
            $wp_filetype = wp_check_filetype(basename($filename), null );

            $attachment = array(
                'post_mime_type' => $wp_filetype['type'],
                'post_title' => $filename,
                'post_content' => '',
                'post_status' => 'inherit'
            );

            $attach_id = wp_insert_attachment( $attachment, $uploadfile );
            update_post_meta($post_id, '_thumbnail_id', $attach_id);
            $imagenew = get_post( $attach_id );
            $fullsizepath = get_attached_file( $imagenew->ID );
            $attach_data = wp_generate_attachment_metadata( $attach_id, $fullsizepath );
            wp_update_attachment_metadata( $attach_id, $attach_data );

        }



    }




}