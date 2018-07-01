<?php 


    //add_action('init', 'hookMeUp_insert_new_product');


    //sync csv data 
 function hookMeUp_insert_new_product_csv(){
        $file = content_url().'/plugins/hookMeUp/newp.csv';
        
        $csv = array_map("str_getcsv", file($file,FILE_SKIP_EMPTY_LINES));
        $keys = array_shift($csv);
        //var_dump($keys);
       foreach ($csv as $i=>$row) {
          $csv[$i] = array_combine($keys, $row);
         
          $title = $csv[$i]["post_title"];
          $name = $csv[$i]["post_name"];
          $price = $csv[$i]["post_price"];
          
       
                        
                 
            
                $post_id = wp_insert_post( array(
                    'post_title' =>  $name ,
                    'post_content' => $title,
                    'post_status' => 'publish',
                    'post_type' => "product",
                ) );
            
                wp_set_object_terms( $post_id, 'simple', 'product_type' );
            
                update_post_meta( $post_id, '_visibility', 'visible' );
                update_post_meta( $post_id, '_stock_status', 'instock');
                update_post_meta( $post_id, 'total_sales', '0' );
                update_post_meta( $post_id, '_downloadable', 'no' );
                update_post_meta( $post_id, '_virtual', 'yes' );
                update_post_meta( $post_id, '_price',  $price );
                update_post_meta( $post_id, '_regular_price',  $price );
                update_post_meta( $post_id, '_sale_price', '' );
                update_post_meta( $post_id, '_purchase_note', '' );
                update_post_meta( $post_id, '_featured', 'no' );
                update_post_meta( $post_id, '_weight', '' );
                update_post_meta( $post_id, '_length', '' );
                update_post_meta( $post_id, '_width', '' );
                update_post_meta( $post_id, '_height', '' );
                update_post_meta( $post_id, '_sku', '' );
                update_post_meta( $post_id, '_product_attributes', array() );
                update_post_meta( $post_id, '_sale_price_dates_from', '' );
                update_post_meta( $post_id, '_sale_price_dates_to', '' );
                update_post_meta( $post_id, '_price', '' );
                update_post_meta( $post_id, '_sold_individually', '' );
                update_post_meta( $post_id, '_manage_stock', 'no' );
                update_post_meta( $post_id, '_backorders', 'no' );
                update_post_meta( $post_id, '_stock', '' );
            }
        }


        
    